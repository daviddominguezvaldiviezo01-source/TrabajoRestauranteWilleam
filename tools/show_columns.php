<?php
include(__DIR__ . '/../conexion.php');
$res = mysqli_query($conexion, "SHOW COLUMNS FROM usuarios");
$cols = mysqli_fetch_all($res, MYSQLI_ASSOC);
echo json_encode($cols, JSON_PRETTY_PRINT);
?>