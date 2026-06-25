<?php
require_once __DIR__ . '/../conexion.php';
header('Content-Type: application/json; charset=utf-8');

$has_guest_columns = false;
$resCols = mysqli_query($conexion, "SHOW COLUMNS FROM pedidos LIKE 'nombre_cliente'");
if ($resCols && mysqli_num_rows($resCols) > 0) {
    $has_guest_columns = true;
}

if ($has_guest_columns) {
    $sql = "SELECT id_pedido, total, COALESCE(nombre_cliente, '') AS nombre_cliente, COALESCE(email_cliente, '') AS email_cliente FROM pedidos WHERE estado = 'ir a recoger' ORDER BY id_pedido DESC LIMIT 5";
    $res = mysqli_query($conexion, $sql);
} else {
    $sql = "SELECT p.id_pedido, p.total, COALESCE(u.nombre, '') AS nombre_cliente, COALESCE(u.email, '') AS email_cliente FROM pedidos p LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario WHERE p.estado = 'ir a recoger' ORDER BY p.id_pedido DESC LIMIT 5";
    $res = mysqli_query($conexion, $sql);
}
$out = [];
while ($row = mysqli_fetch_assoc($res)) {
    $out[] = [
        'id_pedido' => intval($row['id_pedido']),
        'total' => floatval($row['total']),
        'cliente' => $row['nombre_cliente'],
        'email' => $row['email_cliente']
    ];
}

echo json_encode(['orders' => $out]);
exit;

?>
