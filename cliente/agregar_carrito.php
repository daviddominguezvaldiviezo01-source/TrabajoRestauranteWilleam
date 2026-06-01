<?php
session_start();
include(__DIR__ . '/../conexion.php');

// Evitar que usuarios con rol 'delivery' agreguen productos
include(__DIR__ . '/guard_delivery.php');

if (!isset($_GET['id'])) {
    header("Location:index.php");
    exit();
}

$id = intval($_GET['id']);

// Verificar que el producto existe y tiene stock
$stmt = mysqli_prepare($conexion, "SELECT * FROM productos WHERE id_producto = ? AND disponible = 1 AND stock > 0");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($res) == 0) {
    $_SESSION['mensaje_error'] = "Producto no disponible";
    header("Location:index.php");
    exit();
}

// El carrito es un array asociativo: id_producto => cantidad
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

if (isset($_SESSION['carrito'][$id])) {
    $_SESSION['carrito'][$id]++;
} else {
    $_SESSION['carrito'][$id] = 1;
}

$_SESSION['mensaje'] = "Producto agregado al carrito 🛒";
header('Location: index.php');
exit();
