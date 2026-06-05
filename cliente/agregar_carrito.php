<?php
session_start();
require_once __DIR__ . '/../conexion.php';
/** @var mysqli $conexion */

if (!isset($_GET['id'])) {
    header("Location:index.php");
    exit();
}

$id = intval($_GET['id']);

// Verificar que el producto existe y tiene stock
$stmt = mysqli_prepare($conexion, "SELECT * FROM productos WHERE id_producto = ? AND disponible = 1 AND stock > 0");
$fila = null;
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($res) {
        $fila = mysqli_fetch_assoc($res);
    }
    mysqli_stmt_close($stmt);
}

if (!$fila) {
    $_SESSION['mensaje_error'] = "Producto no disponible";
    header("Location:index.php");
    exit();
}

// El carrito es un array asociativo: id_producto => cantidad
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$currentQty = $_SESSION['carrito'][$id] ?? 0;
if ($currentQty + 1 > $fila['stock']) {
    $_SESSION['mensaje'] = "No hay stock suficiente para agregar más unidades.";
    $_SESSION['mensaje_tipo'] = 'error';
    header("Location: index.php");
    exit();
}

$_SESSION['carrito'][$id] = $currentQty + 1;
$_SESSION['mensaje'] = "Producto agregado al carrito 🛒";
$_SESSION['mensaje_tipo'] = 'success';
header("Location: index.php");
exit();
