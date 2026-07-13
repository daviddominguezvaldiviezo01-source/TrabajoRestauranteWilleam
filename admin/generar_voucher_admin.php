<?php
/**
 * ============================================================
 * ARCHIVO: admin/generar_voucher_admin.php
 * ============================================================
 * Genera y descarga el voucher PDF de cualquier pedido.
 * Solo accesible para administradores.
 * Recibe: GET ?id_pedido=X
 * ============================================================
 */

// Ruta de fuentes FPDF antes de cargar librerías
if (!defined('FPDFONTPATH')) {
    define('FPDFONTPATH', dirname(__FILE__) . '/../vendor/fpdf/font/');
}

session_start();
require_once dirname(__FILE__) . '/../conexion.php';
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../tools/mailer.php'; // contiene generar_pdf_voucher()

// ── Solo admins ──
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../cliente/login.php');
    exit();
}

// ── Validar parámetro ──
$id_pedido = isset($_GET['id_pedido']) ? intval($_GET['id_pedido']) : 0;
if ($id_pedido <= 0) {
    header('Location: pedidos.php');
    exit();
}

// ── Detectar si la tabla pedidos tiene columnas de invitado ──
$has_guest_cols = false;
$chkCol = mysqli_query($conexion, "SHOW COLUMNS FROM pedidos LIKE 'nombre_cliente'");
if ($chkCol && mysqli_num_rows($chkCol) > 0) {
    $has_guest_cols = true;
}

// ── Obtener datos del pedido (sin restricción de usuario) ──
if ($has_guest_cols) {
    $sql = "SELECT p.id_pedido, p.estado, p.total, p.fecha,
                   COALESCE(u.nombre, p.nombre_cliente, 'Invitado') AS nombre,
                   COALESCE(u.email, p.email_cliente, '') AS correo,
                   COALESCE(u.telefono, p.telefono_cliente, '') AS telefono,
                   COALESCE(d.direccion, '') AS direccion,
                   pg.metodo AS metodo_pago
            FROM pedidos p
            LEFT JOIN usuarios u    ON u.id_usuario = p.id_usuario
            LEFT JOIN direcciones d ON d.id_direccion = p.id_direccion
            LEFT JOIN pagos pg      ON pg.id_pedido = p.id_pedido
            WHERE p.id_pedido = ?
            LIMIT 1";
} else {
    $sql = "SELECT p.id_pedido, p.estado, p.total, p.fecha,
                   COALESCE(u.nombre, 'Invitado') AS nombre,
                   COALESCE(u.email, '') AS correo,
                   COALESCE(u.telefono, '') AS telefono,
                   COALESCE(d.direccion, '') AS direccion,
                   pg.metodo AS metodo_pago
            FROM pedidos p
            LEFT JOIN usuarios u    ON u.id_usuario = p.id_usuario
            LEFT JOIN direcciones d ON d.id_direccion = p.id_direccion
            LEFT JOIN pagos pg      ON pg.id_pedido = p.id_pedido
            WHERE p.id_pedido = ?
            LIMIT 1";
}

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id_pedido);
mysqli_stmt_execute($stmt);
$res    = mysqli_stmt_get_result($stmt);
$pedido = mysqli_fetch_assoc($res);

if (!$pedido) {
    header('Location: pedidos.php');
    exit();
}

// ── Obtener items del pedido ──
$sql_items = "SELECT pr.nombre, dp.cantidad, dp.subtotal,
                     ROUND(dp.subtotal / dp.cantidad, 2) AS precio
              FROM detalle_pedido dp
              JOIN productos pr ON pr.id_producto = dp.id_producto
              WHERE dp.id_pedido = ?";
$stmt2 = mysqli_prepare($conexion, $sql_items);
mysqli_stmt_bind_param($stmt2, 'i', $id_pedido);
mysqli_stmt_execute($stmt2);
$res2  = mysqli_stmt_get_result($stmt2);
$items = [];
while ($row = mysqli_fetch_assoc($res2)) {
    $items[] = $row;
}

if (empty($items)) {
    header('Location: pedidos.php');
    exit();
}

// ── Preparar datos para el generador ──
$datos_pedido = [
    'id_pedido'         => $pedido['id_pedido'],
    'nombre'            => $pedido['nombre'],
    'correo'            => $pedido['correo'],
    'telefono'          => $pedido['telefono'],
    'direccion'         => $pedido['direccion'] ?: 'Sin dirección registrada',
    'estado'            => $pedido['estado'],
    'metodo_pago'       => $pedido['metodo_pago'] ?? 'efectivo',
    'metodo_pago_label' => ucfirst($pedido['metodo_pago'] ?? 'efectivo'),
    'total'             => floatval($pedido['total']),
    'total_fmt'         => number_format(floatval($pedido['total']), 2),
    'fecha'             => $pedido['fecha'] ?? date('Y-m-d H:i:s'),
];

// ── Generar PDF ──
$pdf_content = generar_pdf_voucher($datos_pedido, $items);

// ── Enviar al navegador como descarga ──
$filename = 'Voucher_Brisamar_Pedido_' . str_pad($id_pedido, 6, '0', STR_PAD_LEFT) . '.pdf';

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($pdf_content));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

echo $pdf_content;
exit();
