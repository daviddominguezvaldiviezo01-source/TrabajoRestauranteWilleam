<?php
include(__DIR__ . '/../conexion.php');

// Agregar campos para datos de cliente en pedidos si no existen (MySQL 8+ soporta IF NOT EXISTS)
$sql = "ALTER TABLE pedidos 
    ADD COLUMN IF NOT EXISTS nombre_cliente VARCHAR(255) NULL, 
    ADD COLUMN IF NOT EXISTS email_cliente VARCHAR(255) NULL, 
    ADD COLUMN IF NOT EXISTS telefono_cliente VARCHAR(50) NULL";

if (mysqli_query($conexion, $sql)) {
    echo "MIGRATION_OK: columnas añadidas o ya existían.\n";
} else {
    echo "MIGRATION_ERR: " . mysqli_error($conexion) . "\n";
}

// Información adicional: mostrar estructura mínima para verificación
$res = mysqli_query($conexion, "SHOW COLUMNS FROM pedidos LIKE 'nombre_cliente'");
if ($res && mysqli_num_rows($res) > 0) echo "COLUMN_PRESENT:nombre_cliente\n";
$res = mysqli_query($conexion, "SHOW COLUMNS FROM pedidos LIKE 'email_cliente'");
if ($res && mysqli_num_rows($res) > 0) echo "COLUMN_PRESENT:email_cliente\n";
$res = mysqli_query($conexion, "SHOW COLUMNS FROM pedidos LIKE 'telefono_cliente'");
if ($res && mysqli_num_rows($res) > 0) echo "COLUMN_PRESENT:telefono_cliente\n";

?>