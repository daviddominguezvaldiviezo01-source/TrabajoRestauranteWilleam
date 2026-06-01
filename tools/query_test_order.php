<?php
include(__DIR__ . '/../conexion.php');
$id_rep = 6;
$res = mysqli_query($conexion, "SELECT id_pedido,estado,id_repartidor,total FROM pedidos WHERE id_repartidor=$id_rep AND estado='ir a recoger' ORDER BY id_pedido DESC LIMIT 5");
$rows = mysqli_fetch_all($res, MYSQLI_ASSOC);
echo json_encode($rows, JSON_PRETTY_PRINT);
?>