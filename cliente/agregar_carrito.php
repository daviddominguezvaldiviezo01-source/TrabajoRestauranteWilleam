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
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta http-equiv="refresh" content="3;url=index.php">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Producto agregado</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
body {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(135deg, #2d1569, #4b2bb3);
    font-family: 'Segoe UI', sans-serif;
    margin: 0;
}

.card-loading {
    width: 100%;
    max-width: 320px;
    background: rgba(255,255,255,0.98);
    border-radius: 24px;
    padding: 22px 18px;
    text-align: center;
    box-shadow: 0 16px 40px rgba(0,0,0,.18);
}

.icon {
    width: 78px;
    height: 78px;
    background: #ff9800;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0 auto;
    animation: pulse 1.5s infinite;
}

.icon i {
    font-size: 36px;
    color: white;
}

h1 {
    margin-top: 18px;
    font-size: 24px;
    font-weight: 900;
    color: #2d1569;
}

p {
    margin-top: 10px;
    color: #555;
    font-size: 15px;
    line-height: 1.4;
}

.botones {
    margin-top: 22px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn-menu,
.btn-carrito {
    flex: 1 1 140px;
    padding: 10px 14px;
    border-radius: 14px;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.95rem;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
}

.btn-menu {
    background: #2d1569;
    color: white;
}

.btn-menu:hover {
    background: #1b0c4d;
}

.btn-carrito {
    background: #ff9800;
    color: white;
}

.btn-carrito:hover {
    background: #ff8500;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.08); }
    100% { transform: scale(1); }
}
</style>
</head>
<body>
<div class="card-loading">
    <div class="icon"><i class="fa-solid fa-cart-shopping"></i></div>
    <h1>Producto agregado</h1>
    <p>Tu platillo fue añadido correctamente al carrito.</p>
    <div class="botones">
        <a href="index.php" class="btn-menu"><i class="fa-solid fa-arrow-left"></i> Volver al menú</a>
        <a href="carrito.php" class="btn-carrito"><i class="fa-solid fa-cart-shopping"></i> Ver carrito</a>
    </div>
</div>
</body>
</html>
