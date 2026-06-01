<?php
session_start();
include(__DIR__ . '/../conexion.php');

$error = '';
$tab = 'login';

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
                $basePath = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
                if($usuario['rol']=='admin') {
                    header("Location: $basePath/../admin/dashboard.php");
                } elseif($usuario['rol']=='delivery') {
                    header("Location: $basePath/../delivery.php");
                } else {
                    header("Location: $basePath/index.php");
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
                header("Location: index.php"); exit();
            } else { $error="Error al registrarse."; }
        }
    }
}

if(isset($_GET['invitado'])){ $_SESSION['invitado']=true; header("Location: carrito.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ingresar - Brisamar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body {
    background:#111;
    min-height:100vh;
    font-family:'Segoe UI',sans-serif;
    display:flex;
    flex-direction:column;
}

/* NAVBAR */
.navbar-top {
    background:#c8102e;
    padding:0 40px;
    height:64px;
    display:flex;
    align-items:center;
    box-shadow:0 2px 12px rgba(0,0,0,.5);
}
.navbar-inner { max-width:1300px; width:100%; margin:0 auto; display:flex; align-items:center; justify-content:space-between; }
.logo { font-size:22px; font-weight:900; color:#fff; text-decoration:none; display:flex; align-items:center; gap:10px; }
.btn-nav-back { color:#fff; text-decoration:none; font-weight:600; font-size:14px; display:flex; align-items:center; gap:7px; opacity:.85; transition:.2s; }
.btn-nav-back:hover { opacity:1; color:#fff; }

/* LAYOUT */
.auth-wrap {
    flex:1;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:40px 20px;
}
.auth-card {
    display:grid;
    grid-template-columns:1fr 1fr;
    max-width:960px;
    width:100%;
    border-radius:20px;
    overflow:hidden;
    box-shadow:0 20px 60px rgba(0,0,0,.6);
}

/* PANEL IZQUIERDO */
.auth-left {
    background:#c8102e;
    padding:50px 40px;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
    color:#fff;
}
.auth-left .brand { font-size:26px; font-weight:900; display:flex; align-items:center; gap:10px; margin-bottom:30px; }
.auth-left h2 { font-size:2rem; font-weight:900; line-height:1.3; margin-bottom:16px; }
.auth-left p { font-size:1rem; opacity:.85; margin-bottom:28px; }
.feature-list { display:flex; flex-direction:column; gap:12px; }
.feature-item { display:flex; align-items:center; gap:10px; font-size:14px; }
.feature-item i { color:#ffcc00; width:18px; }
.auth-left-footer { font-size:12px; opacity:.5; margin-top:30px; }

/* PANEL DERECHO */
.auth-right {
    background:#1a1a1a;
    padding:50px 40px;
    display:flex;
    flex-direction:column;
    justify-content:center;
}

/* TABS */
.auth-tabs { display:flex; gap:0; margin-bottom:30px; border-bottom:1px solid #2a2a2a; }
.tab-btn {
    background:none; border:none; color:rgba(255,255,255,.4);
    font-weight:700; font-size:14px; cursor:pointer;
    padding:10px 20px 12px; border-bottom:2px solid transparent;
    transition:.2s; letter-spacing:.3px;
}
.tab-btn.active { color:#fff; border-bottom-color:#c8102e; }

/* FORM */
.tab-content { display:none; }
.tab-content.active { display:block; }
.tab-content h3 { font-size:1.4rem; font-weight:900; color:#fff; margin-bottom:6px; }
.tab-content .sub { color:rgba(255,255,255,.4); font-size:13px; margin-bottom:22px; }

.form-group { margin-bottom:16px; }
.form-group label { display:block; color:rgba(255,255,255,.6); font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; margin-bottom:7px; }
.form-group input {
    width:100%; background:#111; border:1.5px solid #2a2a2a;
    border-radius:10px; color:#fff; padding:11px 14px; font-size:14px; transition:.2s;
}
.form-group input:focus { outline:none; border-color:#c8102e; background:#161616; }
.form-group input::placeholder { color:rgba(255,255,255,.2); }

.btn-submit {
    width:100%; background:#c8102e; color:#fff; border:none;
    border-radius:10px; padding:13px; font-weight:700; font-size:15px;
    cursor:pointer; transition:.2s; margin-top:6px;
}
.btn-submit:hover { background:#a50d26; transform:translateY(-1px); }

.btn-guest {
    width:100%; background:transparent; color:rgba(255,255,255,.5);
    border:1.5px solid #2a2a2a; border-radius:10px; padding:11px;
    font-weight:600; font-size:14px; cursor:pointer; transition:.2s; margin-top:10px;
    text-decoration:none; display:block; text-align:center;
}
.btn-guest:hover { border-color:rgba(255,255,255,.3); color:#fff; }

.alert-err {
    background:rgba(200,16,46,.15); border:1px solid rgba(200,16,46,.3);
    color:#ff8080; padding:11px 14px; border-radius:10px;
    margin-bottom:18px; font-size:13px; display:flex; align-items:center; gap:8px;
}

.link-tab { color:#c8102e; cursor:pointer; font-weight:700; background:none; border:none; padding:0; font-size:13px; }
.switch-text { text-align:center; margin-top:16px; color:rgba(255,255,255,.35); font-size:13px; }

@media(max-width:700px){
    .auth-card { grid-template-columns:1fr; }
    .auth-left { display:none; }
    .auth-right { padding:36px 24px; }
}
</style>
</head>
<body>

<nav class="navbar-top">
    <div class="navbar-inner">
        <a href="index.php" class="logo"><i class="fas fa-fire" style="color:#ffcc00;"></i> Brisamar</a>
        <a href="index.php" class="btn-nav-back"><i class="fas fa-arrow-left"></i> Volver al menú</a>
    </div>
</nav>

<div class="auth-wrap">
    <div class="auth-card">

        <!-- IZQUIERDA -->
        <div class="auth-left">
            <div>
                <div class="brand"><i class="fas fa-fire" style="color:#ffcc00;"></i> Brisamar</div>
                <h2>Los mejores sabores del mar</h2>
                <p>Inicia sesión para disfrutar de una experiencia de pedido más rápida y personalizada.</p>
                <div class="feature-list">
                    <div class="feature-item"><i class="fas fa-check"></i> Pedidos en línea rápidos</div>
                    <div class="feature-item"><i class="fas fa-check"></i> Múltiples métodos de pago</div>
                    <div class="feature-item"><i class="fas fa-check"></i> Historial de pedidos</div>
                    <div class="feature-item"><i class="fas fa-check"></i> Compra como invitado disponible</div>
                </div>
            </div>
            <div class="auth-left-footer">© 2024 Brisamar. Todos los derechos reservados.</div>
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

                <?php if($tab==='login' && !empty($error)): ?>
                <div class="alert-err"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label>Correo electrónico</label>
                        <input type="email" name="correo" placeholder="tu@email.com" required>
                    </div>
                    <div class="form-group">
                        <label>Contraseña</label>
                        <input type="password" name="clave" placeholder="••••••••" required>
                    </div>
                    <button type="submit" name="login" class="btn-submit">Ingresar</button>
                </form>
                <a href="?invitado=1" class="btn-guest"><i class="fas fa-user-secret"></i> Continuar como invitado</a>
            </div>

            <!-- REGISTRO -->
            <div class="tab-content <?php echo $tab==='registro'?'active':''; ?>" id="registro">
                <h3>Crear cuenta</h3>
                <p class="sub">Regístrate para acceder a más beneficios</p>

                <?php if($tab==='registro' && !empty($error)): ?>
                <div class="alert-err"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
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

<script>
function switchTab(tab, btn) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    document.getElementById(tab).classList.add('active');
    btn.classList.add('active');
}
</script>
</body>
</html>
