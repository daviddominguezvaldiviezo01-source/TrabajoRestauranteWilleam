<?php
session_start();
require_once dirname(__FILE__) . '/../conexion.php';
/** @var mysqli $conexion */

$error = '';
$success = '';
$email = isset($_GET['email']) ? trim($_GET['email']) : '';

if(empty($email)) {
    header("Location: login.php");
    exit();
}

if(isset($_POST['resetear'])){
    $codigo = trim($_POST['codigo']);
    $clave = trim($_POST['clave']);
    $clave_conf = trim($_POST['clave_conf']);
    
    if(empty($codigo) || empty($clave) || empty($clave_conf)) {
        $error = "Por favor completa todos los campos";
    } elseif($clave !== $clave_conf) {
        $error = "Las contraseñas no coinciden";
    } elseif(strlen($clave) < 6) {
        $error = "La contraseña debe tener mínimo 6 caracteres";
    } else {
        $stmt = mysqli_prepare($conexion, "SELECT * FROM usuarios WHERE email = ? AND token_reset_password = ?");
        mysqli_stmt_bind_param($stmt, "ss", $email, $codigo);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        
        if(mysqli_num_rows($resultado) > 0) {
            $usuario = mysqli_fetch_assoc($resultado);
            
            // Verificar expiración
            if(strtotime($usuario['expiracion_token_password']) < time()) {
                $error = "El código ha expirado. Por favor solicita uno nuevo.";
            } else {
                // Actualizar contraseña
                $hash = password_hash($clave, PASSWORD_DEFAULT);
                $upd = mysqli_prepare($conexion, "UPDATE usuarios SET password=?, token_reset_password=NULL, expiracion_token_password=NULL WHERE id_usuario=?");
                mysqli_stmt_bind_param($upd, "si", $hash, $usuario['id_usuario']);
                
                if(mysqli_stmt_execute($upd)) {
                    $_SESSION['error'] = "Tu contraseña ha sido actualizada correctamente. Por favor inicia sesión.";
                    header("Location: login.php");
                    exit();
                } else {
                    $error = "Error al actualizar la contraseña. Intenta de nuevo.";
                }
            }
        } else {
            $error = "El código de verificación es incorrecto.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Restablecer Contraseña - Brisamar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>

<nav class="navbar-top">
    <div class="navbar-inner">
        <a href="index.php" class="logo"><i class="fas fa-fire icon-fire"></i> Brisamar</a>
        <a href="login.php" class="btn-nav-back"><i class="fas fa-arrow-left"></i> Volver al Login</a>
    </div>
</nav>

<div class="auth-wrap">
    <div class="auth-card" style="max-width: 500px; margin: 0 auto; display: block;">
        <div class="auth-right" style="width: 100%; border-radius: 20px;">
            <div class="tab-content active">
                <h3 style="text-align:center;">Crear Nueva Contraseña</h3>
                <p class="sub" style="text-align:center;">Ingresa el código que enviamos a <strong><?php echo htmlspecialchars($email); ?></strong></p>

                <?php if(!empty($error)): ?>
                <div class="alert-err"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label>Código de Recuperación (8 dígitos)</label>
                        <input type="text" name="codigo" placeholder="EJ: A1B2C3D4" style="text-transform: uppercase; letter-spacing: 2px; text-align: center; font-weight: bold; font-size: 18px;" required>
                    </div>
                    <div class="form-group">
                        <label>Nueva Contraseña</label>
                        <input type="password" name="clave" placeholder="Mínimo 6 caracteres" required>
                    </div>
                    <div class="form-group">
                        <label>Confirmar Nueva Contraseña</label>
                        <input type="password" name="clave_conf" placeholder="Repite tu contraseña" required>
                    </div>
                    <button type="submit" name="resetear" class="btn-submit">Actualizar Contraseña</button>
                </form>
                
                <div class="switch-text" style="margin-top:20px;">
                    ¿No recibiste el código? <a href="forgot_password.php" style="color:#dc2626; text-decoration:none;">Reenviar correo</a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
