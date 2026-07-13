<?php
/**
 * ============================================================
 * ARCHIVO: cliente/solicitar_codigo_verificacion.php
 * ============================================================
 * Genera y envía un código de verificación por EMAIL.
 * Recibe: POST con 'tipo' = 'email'
 * ============================================================
 */

session_start();
require_once dirname(__FILE__) . '/../conexion.php';
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../tools/mailer.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Validar sesión
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'cliente') {
        echo json_encode(['ok' => false, 'error' => 'No autenticado']);
        exit();
    }

    if (!isset($_SESSION['usuario'])) {
        echo json_encode(['ok' => false, 'error' => 'ID de usuario no en sesión']);
        exit();
    }

    $id_usuario = intval($_SESSION['usuario']);
    $tipo = isset($_POST['tipo']) ? trim($_POST['tipo']) : 'email';

    // Solo aceptamos 'email' por ahora
    if ($tipo !== 'email') {
        echo json_encode(['ok' => false, 'error' => 'Solo verificación por email disponible']);
        exit();
    }

    // Obtener datos del usuario
    $query = "SELECT email, nombre FROM usuarios WHERE id_usuario = ? LIMIT 1";
    $stmt = mysqli_prepare($conexion, $query);
    if (!$stmt) {
        echo json_encode(['ok' => false, 'error' => 'Error en prep
    }aración de consulta: ' . mysqli_error($conexion)]);
        exit();

    mysqli_stmt_bind_param($stmt, 'i', $id_usuario);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $usuario = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if (!$usuario) {
        echo json_encode(['ok' => false, 'error' => 'Usuario no encontrado']);
        exit();
    }

    if (empty($usuario['email'])) {
        echo json_encode(['ok' => false, 'error' => 'No tienes email registrado']);
        exit();
    }

    // Generar código de 6 dígitos
    $codigo = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiracion = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    // Enviar código por email
    $resultado = enviar_codigo_verificacion_email($usuario['email'], $usuario['nombre'], $codigo);
    
    if (!$resultado['ok']) {
        echo json_encode(['ok' => false, 'error' => 'Error al enviar email: ' . $resultado['error']]);
        exit();
    }

    // Guardar código en BD
    $query_update = "UPDATE usuarios SET codigo_verificacion_email = ?, expiracion_codigo_email = ? WHERE id_usuario = ?";
    $stmt_update = mysqli_prepare($conexion, $query_update);
    if (!$stmt_update) {
        echo json_encode(['ok' => false, 'error' => 'Error en preparación de UPDATE: ' . mysqli_error($conexion)]);
        exit();
    }

    mysqli_stmt_bind_param($stmt_update, 'ssi', $codigo, $expiracion, $id_usuario);
    if (!mysqli_stmt_execute($stmt_update)) {
        mysqli_stmt_close($stmt_update);
        echo json_encode(['ok' => false, 'error' => 'Error al guardar código en BD: ' . mysqli_error($conexion)]);
        exit();
    }
    mysqli_stmt_close($stmt_update);

    // Éxito
    echo json_encode([
        'ok' => true, 
        'message' => '✓ Código de verificación enviado a ' . htmlspecialchars($usuario['email'])
    ]);

} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => 'Excepción: ' . $e->getMessage()]);
}
?>