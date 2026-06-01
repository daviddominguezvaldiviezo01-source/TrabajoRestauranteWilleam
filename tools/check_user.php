<?php
include(__DIR__ . '/../conexion.php');
$email = 'delivery@restaurante.test';
$stmt = mysqli_prepare($conexion, "SELECT id_usuario,nombre,email,rol FROM usuarios WHERE email=?");
mysqli_stmt_bind_param($stmt,'s',$email);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($res)) {
    echo json_encode($row, JSON_PRETTY_PRINT);
} else {
    echo "NOT FOUND\n";
}
?>