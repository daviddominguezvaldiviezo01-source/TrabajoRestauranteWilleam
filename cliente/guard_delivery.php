<?php
session_start();
// Redirige a la interfaz delivery si el usuario tiene rol 'delivery'
if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'delivery') {
    $basePath = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    header("Location: $basePath/../delivery.php");
    exit();
}
