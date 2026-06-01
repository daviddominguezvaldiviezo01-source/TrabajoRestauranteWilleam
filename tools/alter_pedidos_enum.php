<?php
include(__DIR__ . '/../conexion.php');
// Añadir 'ir a recoger' al enum de estado
$sql = "ALTER TABLE pedidos MODIFY estado ENUM('pendiente','preparando','ir a recoger','en camino','entregado','cancelado') NULL DEFAULT 'pendiente'";
if (mysqli_query($conexion, $sql)) echo "ALTER_OK\n"; else echo "ALTER_ERR: " . mysqli_error($conexion) . "\n";
// Actualizar el pedido de prueba (id 27) si existe
$id = 27;
$u = mysqli_prepare($conexion, "UPDATE pedidos SET estado='ir a recoger' WHERE id_pedido=?");
mysqli_stmt_bind_param($u,'i',$id); mysqli_stmt_execute($u);
if (mysqli_stmt_affected_rows($u) > 0) echo "UPDATED_ORDER:$id\n"; else echo "NO_UPDATE\n";
?>