<?php

$host     = "localhost";
$usuario  = "root";
$password = "";
$bd       = "restaurant_bd";

$conexion = mysqli_connect($host, $usuario, $password, $bd);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($conexion, "utf8mb4");

?>