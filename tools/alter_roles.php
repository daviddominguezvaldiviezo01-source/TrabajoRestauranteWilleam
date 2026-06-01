<?php
include(__DIR__ . '/../conexion.php');
$sql = "ALTER TABLE usuarios MODIFY rol ENUM('cliente','admin','delivery') NULL DEFAULT 'cliente'";
if (mysqli_query($conexion, $sql)) {
    echo "ALTER_OK\n";
} else {
    echo "ALTER_ERR: " . mysqli_error($conexion) . "\n";
}
// Update specific user
$email = 'delivery@restaurante.test';
$u = mysqli_prepare($conexion, "UPDATE usuarios SET rol='delivery' WHERE email=?");
mysqli_stmt_bind_param($u,'s',$email);
if (mysqli_stmt_execute($u)) echo "UPDATE_OK\n"; else echo "UPDATE_ERR: " . mysqli_error($conexion) . "\n";
?>