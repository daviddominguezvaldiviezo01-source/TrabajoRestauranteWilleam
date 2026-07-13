<?php
session_start();
require_once dirname(__FILE__) . '/../conexion.php';
/** @var mysqli $conexion */

$error = '';
$tab = 'login';
$next = trim($_GET['next'] ?? $_POST['next'] ?? '');
if ($next !== '' && (strpos($next, '..') !== false || strpos($next, '://') !== false)) {
    $next = '';
}
$session_error = '';
if (isset($_SESSION['error'])) {
    $session_error = $_SESSION['error'];
    unset($_SESSION['error']);
}

if(isset($_POST['login'])){
    $correo = trim($_POST['correo']);
    $clave  = trim($_POST['clave']);
    if(empty($correo) || empty($clave)) {
        $error = "Por favor completa todos los campos";
    } else {
        $stmt = mysqli_prepare($conexion, "SELECT * FROM usuarios WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $correo);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        if(mysqli_num_rows($resultado) > 0) {
            $usuario = mysqli_fetch_assoc($resultado);
            $ok = false;
            if(password_verify($clave, $usuario['password'])) {
                $ok = true;
            } elseif(hash_equals($usuario['password'], $clave)) {
                $ok = true;
                $hash = password_hash($clave, PASSWORD_DEFAULT);
                $su = mysqli_prepare($conexion, "UPDATE usuarios SET password=? WHERE id_usuario=?");
                mysqli_stmt_bind_param($su, "si", $hash, $usuario['id_usuario']);
                mysqli_stmt_execute($su);
            }
            if($ok) {
                $_SESSION['usuario'] = $usuario['id_usuario'];
                $_SESSION['nombre']  = $usuario['nombre'];
                $_SESSION['rol']     = $usuario['rol'];
                $_SESSION['avatar']  = $usuario['avatar'] ?? null;
                if ($usuario['rol'] === 'admin') {
                    header("Location: ../admin/dashboard.php");
                } elseif ($usuario['rol'] === 'delivery') {
                    header("Location: ../delivery.php");
                } elseif (!empty($next)) {
                    header("Location: " . $next);
                } else {
                    header("Location: index.php");
                }
                exit();
            } else { $error = "Correo o contraseña incorrectos"; }
        } else { $error = "Correo o contraseña incorrectos"; }
    }
}

if(isset($_POST['registro'])){
    $nombre    = trim($_POST['nombre']);
    $correo    = trim($_POST['correo']);
    $telefono  = trim($_POST['telefono']);
    $clave     = trim($_POST['clave']);
    $clave_conf= trim($_POST['clave_conf']);
    $tab = 'registro';
    if(empty($nombre)||empty($correo)||empty($clave)) { $error="Completa todos los campos"; }
    elseif($clave!==$clave_conf) { $error="Las contraseñas no coinciden"; }
    elseif(strlen($clave)<6) { $error="Mínimo 6 caracteres"; }
    else {
        $sc = mysqli_prepare($conexion,"SELECT id_usuario FROM usuarios WHERE email=?");
        mysqli_stmt_bind_param($sc,"s",$correo); mysqli_stmt_execute($sc);
        if(mysqli_num_rows(mysqli_stmt_get_result($sc))>0) { $error="El correo ya está registrado"; }
        else {
            $hash = password_hash($clave, PASSWORD_DEFAULT);
            $si = mysqli_prepare($conexion,"INSERT INTO usuarios (nombre,email,telefono,password,rol) VALUES (?,?,?,?,'cliente')");
            mysqli_stmt_bind_param($si,"ssss",$nombre,$correo,$telefono,$hash);
            if(mysqli_stmt_execute($si)){
                $_SESSION['usuario']=mysqli_insert_id($conexion);
                $_SESSION['nombre']=$nombre; $_SESSION['rol']='cliente';
                if (!empty($next)) {
                    header("Location: " . $next);
                } else {
                    header("Location: index.php");
                }
                exit();
            } else { $error="Error al registrarse."; }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ingresar - Brisamar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>

<nav class="navbar-top">
    <div class="navbar-inner">
        <a href="index.php" class="logo"><i class="fas fa-fire icon-fire"></i> Brisamar</a>
        <a href="index.php" class="btn-nav-back"><i class="fas fa-arrow-left"></i> Volver al menú</a>
    </div>
</nav>

<div class="auth-wrap">
    <div class="auth-card">

        <!-- IZQUIERDA -->
        <div class="auth-left">
            <div>
                <div class="brand"><i class="fas fa-fire icon-fire"></i> Brisamar</div>
                <h2>Los mejores sabores del mar</h2>
                <p>Inicia sesión para disfrutar de una experiencia de pedido más rápida y personalizada.</p>
                <div class="feature-list">
                    <div class="feature-item"><i class="fas fa-check"></i> Pedidos en línea rápidos</div>
                    <div class="feature-item"><i class="fas fa-check"></i> Múltiples métodos de pago</div>
                    <div class="feature-item"><i class="fas fa-check"></i> Historial de pedidos</div>
                    <div class="feature-item"><i class="fas fa-check"></i> Compra segura solo con cuenta registrada</div>
                </div>
            </div>
            <div class="auth-left-footer">© 2026 Brisamar. Todos los derechos reservados.</div>
        </div>

        <!-- DERECHA -->
        <div class="auth-right">
            <div class="auth-tabs">
                <button class="tab-btn <?php echo $tab==='login'?'active':''; ?>" onclick="switchTab('login',this)">
                    Ingresar
                </button>
                <button class="tab-btn <?php echo $tab==='registro'?'active':''; ?>" onclick="switchTab('registro',this)">
                    Registrarse
                </button>
            </div>

            <!-- LOGIN -->
            <div class="tab-content <?php echo $tab==='login'?'active':''; ?>" id="login">
                <h3>Bienvenido de nuevo</h3>
                <p class="sub">Ingresa con tu cuenta para continuar</p>

                <?php if(!empty($session_error)): ?>
                <div class="alert-err"><i class="fas fa-exclamation-circle"></i> <?php echo $session_error; ?></div>
                <?php endif; ?>
                <?php if($tab==='login' && !empty($error)): ?>
                <div class="alert-err"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="next" value="<?php echo htmlspecialchars($next); ?>">
                    <div class="form-group">
                        <label>Correo electrónico</label>
                        <input type="email" name="correo" placeholder="tu@email.com" required>
                    </div>
                    <div class="form-group">
                        <label>Contraseña</label>
                        <input type="password" name="clave" placeholder="••••••••" required>
                    </div>
                    <div style="text-align: right; margin-top: -10px; margin-bottom: 20px;">
                        <a href="forgot_password.php" style="color: #dc2626; font-size: 13px; text-decoration: none; font-weight: 500;">¿Olvidaste tu contraseña?</a>
                    </div>
                    <button type="submit" name="login" class="btn-submit">Ingresar</button>
                </form>
            </div>

            <!-- REGISTRO -->
            <div class="tab-content <?php echo $tab==='registro'?'active':''; ?>" id="registro">
                <h3>Crear cuenta</h3>
                <p class="sub">Regístrate para acceder a más beneficios</p>

                <?php if($tab==='registro' && !empty($error)): ?>
                <div class="alert-err"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="next" value="<?php echo htmlspecialchars($next); ?>">
                    <div class="form-group">
                        <label>Nombre completo</label>
                        <input type="text" name="nombre" placeholder="Juan Pérez" required>
                    </div>
                    <div class="form-group">
                        <label>Correo electrónico</label>
                        <input type="email" name="correo" placeholder="tu@email.com" required>
                    </div>
                    <div class="form-group">
                        <label>Teléfono (opcional)</label>
                        <input type="tel" name="telefono" placeholder="+51 999 999 999">
                    </div>
                    <div class="form-group">
                        <label>Contraseña</label>
                        <input type="password" name="clave" placeholder="Mínimo 6 caracteres" required>
                    </div>
                    <div class="form-group">
                        <label>Confirmar contraseña</label>
                        <input type="password" name="clave_conf" placeholder="Repite tu contraseña" required>
                    </div>
                    <button type="submit" name="registro" class="btn-submit">Crear cuenta</button>
                </form>
                <div class="switch-text">
                    ¿Ya tienes cuenta? <button class="link-tab" onclick="switchTab('login', document.querySelectorAll('.tab-btn')[0])">Inicia sesión</button>
                </div>
            </div>

        </div>
    </div>
</div>


<script src="../assets/js/login.js"></script>
</body>
</html>
