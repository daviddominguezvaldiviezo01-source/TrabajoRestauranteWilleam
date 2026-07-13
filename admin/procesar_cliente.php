<?php
session_start();
require_once dirname(__FILE__) . '/../conexion.php';
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') { header("Location:../cliente/login.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_cliente') {
    $id_cli = intval($_POST['id_usuario']);
    $nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $email  = mysqli_real_escape_string($conexion, trim($_POST['email']));
    $tel    = mysqli_real_escape_string($conexion, trim($_POST['telefono']));
    
    // Verificar si el email ya existe en otro usuario
    $check = mysqli_query($conexion, "SELECT id_usuario FROM usuarios WHERE email='$email' AND id_usuario != $id_cli");
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['error_clientes'] = "El email ya está en uso por otro cliente.";
    } else {
        if (!empty($_POST['password'])) {
            $pass_hash = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
            mysqli_query($conexion, "UPDATE usuarios SET nombre='$nombre', email='$email', telefono='$tel', password='$pass_hash' WHERE id_usuario=$id_cli AND rol='cliente'");
        } else {
            mysqli_query($conexion, "UPDATE usuarios SET nombre='$nombre', email='$email', telefono='$tel' WHERE id_usuario=$id_cli AND rol='cliente'");
        }
        $_SESSION['success_clientes'] = "Cliente actualizado correctamente.";
    }
}

header("Location: clientes.php");
exit();
?>
