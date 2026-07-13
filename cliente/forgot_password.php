<?php
session_start();
require_once dirname(__FILE__) . '/../conexion.php';
/** @var mysqli $conexion */
require_once dirname(__FILE__) . '/../tools/mailer.php';

$error = '';
$success = '';

if(isset($_POST['recuperar'])){
    $correo = trim($_POST['correo']);
    
    if(empty($correo)) {
        $error = "Por favor ingresa tu correo electrónico";
    } else {
        $stmt = mysqli_prepare($conexion, "SELECT * FROM usuarios WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $correo);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        
        if(mysqli_num_rows($resultado) > 0) {
            $usuario = mysqli_fetch_assoc($resultado);
            
            // Generar código aleatorio de 8 caracteres
            $codigo = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
            $expiracion = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            
            // Actualizar DB
            $upd = mysqli_prepare($conexion, "UPDATE usuarios SET token_reset_password=?, expiracion_token_password=? WHERE id_usuario=?");
            mysqli_stmt_bind_param($upd, "ssi", $codigo, $expiracion, $usuario['id_usuario']);
            
            if(mysqli_stmt_execute($upd)) {
                // Enviar email
                $envio = enviar_codigo_recuperacion_password($correo, $codigo, $usuario['nombre']);
                if($envio['ok']) {
                    header("Location: reset_password.php?email=" . urlencode($correo));
                    exit();
                } else {
                    $error = "Error al enviar el correo: " . $envio['error'];
                }
            } else {
                $error = "Error al generar el token. Intenta de nuevo.";
            }
        } else {
            // Por seguridad, no decimos que el correo no existe, simplemente mostramos el mismo mensaje
            // O podemos mostrar un error genérico
            $error = "Si el correo está registrado, se ha enviado un código de recuperación.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recuperar Contraseña - Brisamar</title>
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
                <h3 style="text-align:center;">Recuperar Contraseña</h3>
                <p class="sub" style="text-align:center;">Ingresa tu correo para recibir un código de restablecimiento</p>

                <?php if(!empty($error)): ?>
                <div class="alert-err" style="background:rgba(255,152,0,0.1); border-color:rgba(255,152,0,0.3); color:#ffb74d;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
                <?php endif; ?>
                <?php if(!empty($success)): ?>
                <div class="alert-err" style="background:rgba(76,175,80,0.1); border-color:rgba(76,175,80,0.3); color:#81c784;">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label>Correo electrónico</label>
                        <input type="email" name="correo" placeholder="tu@email.com" required>
                    </div>
                    <button type="submit" name="recuperar" class="btn-submit">Enviar código</button>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>
