<?php
require_once __DIR__ . '/../conexion.php';
header('Content-Type: application/json; charset=utf-8');

$sql = "SELECT COUNT(*) AS cnt FROM pedidos WHERE estado = 'ir a recoger'";
$res = mysqli_query($conexion, $sql);
$cnt = 0;
if ($res && $row = mysqli_fetch_assoc($res)) {
    $cnt = intval($row['cnt']);
}

echo json_encode(['count' => $cnt]);
exit;

?>
