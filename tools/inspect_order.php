<?php
include(__DIR__ . '/../conexion.php');
$id = 27;
$stmt = mysqli_prepare($conexion, "SELECT * FROM pedidos WHERE id_pedido=?");
mysqli_stmt_bind_param($stmt,'i',$id); mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
var_export($row);
?>