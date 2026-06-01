<?php
include(__DIR__ . '/../conexion.php');
$email = 'delivery@restaurante.test';
$stmt = mysqli_prepare($conexion, "UPDATE usuarios SET rol='delivery' WHERE email=?");
mysqli_stmt_bind_param($stmt,'s',$email);
if (mysqli_stmt_execute($stmt)) {
    echo "OK\n";
} else {
    echo "ERR: " . mysqli_error($conexion) . "\n";
}
?>