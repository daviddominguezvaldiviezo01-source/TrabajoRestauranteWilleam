<?php
session_start();
require_once dirname(__FILE__) . '/../conexion.php';
require_once dirname(__FILE__) . '/../includes/security.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'cliente') {
    header("Location: login.php");
    exit();
}

/** @var mysqli $conexion */

$id_usuario = intval($_SESSION['usuario']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $email  = mysqli_real_escape_string($conexion, trim($_POST['email']));
    $tel    = mysqli_real_escape_string($conexion, trim($_POST['telefono']));
    
    // Check email
    $check = mysqli_query($conexion, "SELECT id_usuario FROM usuarios WHERE email='$email' AND id_usuario != $id_usuario");
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['error_perfil'] = "El email ya está en uso por otra cuenta.";
        header("Location: perfil.php");
        exit();
    } 
    
    $update_queries = [];
    $error = "";
    
    // Manejar subida de avatar
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $dir_destino = __DIR__ . '/../images/avatares/';
            if (!is_dir($dir_destino)) {
                mkdir($dir_destino, 0777, true);
            }
            $nombre_archivo = 'avatar_' . $id_usuario . '_' . time() . '.' . $ext;
            $ruta_subida = $dir_destino . $nombre_archivo;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $ruta_subida)) {
                $ruta_bd = 'images/avatares/' . $nombre_archivo;
                $update_queries[] = "avatar='$ruta_bd'";
                $_SESSION['avatar'] = $ruta_bd; // Actualizar sesión
            } else {
                $error = "Error al subir la imagen.";
            }
        } else {
            $error = "Formato de imagen no permitido. Usa JPG, PNG o WEBP.";
        }
    }
    
    if (!empty($error)) {
        $_SESSION['error_perfil'] = $error;
        header("Location: perfil.php");
        exit();
    }
    
    // Manejar contraseña
    if (!empty($_POST['password'])) {
        $pass_hash = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
        $update_queries[] = "password='$pass_hash'";
    }
    
    // Otros campos
    $update_queries[] = "nombre='$nombre'";
    $update_queries[] = "email='$email'";
    $update_queries[] = "telefono='$tel'";
    
    $query_str = "UPDATE usuarios SET " . implode(", ", $update_queries) . " WHERE id_usuario=$id_usuario";
    if (mysqli_query($conexion, $query_str)) {
        $_SESSION['nombre'] = $nombre; // Actualizar sesión
        $_SESSION['success_perfil'] = "Perfil actualizado correctamente.";
    } else {
        $_SESSION['error_perfil'] = "Error al actualizar la base de datos.";
    }
    
    header("Location: perfil.php");
    exit();
}

// Acceso directo a este archivo sin POST
header("Location: perfil.php");
exit();
?>
