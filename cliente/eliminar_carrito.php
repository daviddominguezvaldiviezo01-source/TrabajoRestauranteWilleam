<?php
session_start();

if (!isset($_GET['key'])) {
    header("Location:carrito.php");
    exit();
}

$key = intval($_GET['key']);

if (isset($_SESSION['carrito'][$key])) {
    unset($_SESSION['carrito'][$key]);
    $_SESSION['mensaje'] = "Producto eliminado del carrito ❌";
}

header("Location:carrito.php");
exit();
?>
