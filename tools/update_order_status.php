<?php
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../includes/security.php';
iniciar_sesion_segura();

header('Content-Type: application/json; charset=utf-8');

if (!validar_rol('delivery')) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$id_pedido = intval($_POST['id_pedido'] ?? 0);
$accion = trim($_POST['accion'] ?? '');
$id_repartidor = intval($_SESSION['usuario'] ?? 0);

if ($id_pedido <= 0) {
    echo json_encode(['success' => false, 'message' => 'Pedido inválido']); exit;
}

if (!in_array($accion, ['tomar','entregar','cancelar'])) {
    echo json_encode(['success' => false, 'message' => 'Acción inválida']); exit;
}

// validar token csrf si viene en POST
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']); exit;
}

if ($accion === 'tomar') {
    $stmt = mysqli_prepare($conexion,
        "UPDATE pedidos SET estado='en camino', id_repartidor = ? WHERE id_pedido = ? AND estado = 'ir a recoger'");
    mysqli_stmt_bind_param($stmt,'ii',$id_repartidor,$id_pedido);
    mysqli_stmt_execute($stmt);
    $ok = mysqli_stmt_affected_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => $ok, 'action' => 'tomar']);
    exit;
} elseif ($accion === 'entregar') {
    $stmt = mysqli_prepare($conexion,
        "UPDATE pedidos SET estado='entregado' WHERE id_pedido = ? AND id_repartidor = ? AND estado = 'en camino'");
    mysqli_stmt_bind_param($stmt,'ii',$id_pedido,$id_repartidor);
    mysqli_stmt_execute($stmt);
    $ok = mysqli_stmt_affected_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => $ok, 'action' => 'entregar']);
    exit;
} else {
    $stmt = mysqli_prepare($conexion,
        "UPDATE pedidos SET estado='cancelado' WHERE id_pedido = ? AND id_repartidor = ? AND estado IN ('ir a recoger','en camino')");
    mysqli_stmt_bind_param($stmt,'ii',$id_pedido,$id_repartidor);
    mysqli_stmt_execute($stmt);
    $ok = mysqli_stmt_affected_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => $ok, 'action' => 'cancelar']);
    exit;
}

?>
