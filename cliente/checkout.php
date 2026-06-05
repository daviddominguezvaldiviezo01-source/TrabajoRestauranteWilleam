<?php
session_start();
require_once dirname(__FILE__) . '/../conexion.php';

$es_invitado = false;
$id_usuario = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : null;

if (!$id_usuario) {
    $_SESSION['error'] = 'Necesitas iniciar sesión o registrarte antes de finalizar tu pedido.';
    header('Location: login.php');
    exit();
}

if (!isset($_SESSION['carrito']) || count($_SESSION['carrito']) == 0) {
    header("Location: carrito.php"); exit();
}

$total = 0; $items = [];
$delivery = 5; $impuesto = 0.18;

foreach ($_SESSION['carrito'] as $id_producto => $cantidad) {
    $id = intval($id_producto);
    $stmt = mysqli_prepare($conexion, "SELECT * FROM productos WHERE id_producto = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($fila = mysqli_fetch_assoc($res)) {
        $subtotal = $fila['precio'] * $cantidad;
        $total += $subtotal;
        $items[] = ['producto' => $fila, 'cantidad' => $cantidad, 'subtotal' => $subtotal];
    }
}

$impuesto_total    = $total * $impuesto;
$total_con_impuesto = $total + $impuesto_total + $delivery;

$direcciones = [];
$usuario_nombre = $usuario_email = $usuario_telefono = '';

if ($id_usuario) {
    $sd = mysqli_prepare($conexion, "SELECT * FROM direcciones WHERE id_usuario = ?");
    mysqli_stmt_bind_param($sd, "i", $id_usuario); mysqli_stmt_execute($sd);
    $direcciones = mysqli_fetch_all(mysqli_stmt_get_result($sd), MYSQLI_ASSOC);

    $su = mysqli_prepare($conexion, "SELECT nombre,email,telefono FROM usuarios WHERE id_usuario = ?");
    mysqli_stmt_bind_param($su, "i", $id_usuario); mysqli_stmt_execute($su);
    if ($u = mysqli_fetch_assoc(mysqli_stmt_get_result($su))) {
        $usuario_nombre = $u['nombre']; $usuario_email = $u['email']; $usuario_telefono = $u['telefono'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout - Brisamar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { background:#111; font-family:'Segoe UI',sans-serif; color:#fff; min-height:100vh; }

/* NAVBAR */
.navbar-top {
    background:#c8102e; padding:0 40px; height:64px;
    display:flex; align-items:center;
    position:sticky; top:0; z-index:200;
    box-shadow:0 2px 12px rgba(0,0,0,.5);
}
.navbar-inner { max-width:1300px; width:100%; margin:0 auto; display:flex; align-items:center; justify-content:space-between; }
.logo { font-size:22px; font-weight:900; color:#fff; text-decoration:none; display:flex; align-items:center; gap:10px; }
.btn-nav-back { color:#fff; text-decoration:none; font-weight:600; font-size:14px; display:flex; align-items:center; gap:7px; opacity:.85; transition:.2s; }
.btn-nav-back:hover { opacity:1; color:#fff; }

/* LAYOUT */
.page-wrap { max-width:1200px; margin:36px auto; padding:0 20px 60px; }
.page-title { font-size:1.6rem; font-weight:900; margin-bottom:28px; display:flex; align-items:center; gap:12px; }

.checkout-grid { display:grid; grid-template-columns:1fr 380px; gap:24px; }

/* CARDS */
.card-dark {
    background:#1a1a1a; border:1px solid #2a2a2a;
    border-radius:16px; padding:26px; margin-bottom:20px;
}
.card-dark h4 {
    font-size:15px; font-weight:800; color:#fff;
    margin-bottom:20px; display:flex; align-items:center; gap:10px;
    text-transform:uppercase; letter-spacing:.5px;
}
.card-dark h4 i { color:#c8102e; }

/* FORM */
.form-group { margin-bottom:16px; }
.form-group label { display:block; color:rgba(255,255,255,.5); font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; margin-bottom:7px; }
.form-group input, .form-group select, .form-group textarea {
    width:100%; background:#111; border:1.5px solid #2a2a2a;
    border-radius:10px; color:#fff; padding:11px 14px; font-size:14px; transition:.2s;
}
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline:none; border-color:#c8102e; }
.form-group input::placeholder, .form-group textarea::placeholder { color:rgba(255,255,255,.2); }
.form-group input:disabled { opacity:.4; cursor:not-allowed; }
.form-group select option { background:#1a1a1a; }
.grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }

/* MÉTODOS DE PAGO */
.metodos-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.metodo-btn {
    background:#111; border:1.5px solid #2a2a2a; border-radius:12px;
    padding:16px 12px; cursor:pointer; text-align:center; transition:.2s;
}
.metodo-btn:hover { border-color:#c8102e; }
.metodo-btn.activo { border-color:#c8102e; background:rgba(200,16,46,.08); }
.metodo-btn i { font-size:1.6rem; color:rgba(255,255,255,.6); display:block; margin-bottom:7px; }
.metodo-btn span { font-weight:700; font-size:13px; display:block; }
.metodo-btn small { color:rgba(255,255,255,.3); font-size:11px; }
.metodo-radio { display:none; }

/* QR */
.qr-box { display:none; margin-top:18px; text-align:center; background:#111; border:1px solid #2a2a2a; border-radius:12px; padding:20px; }
.qr-box h5 { color:#c8102e; font-weight:700; margin-bottom:12px; }
.qr-box img { width:180px; height:180px; border-radius:10px; }
.qr-box p { color:rgba(255,255,255,.4); font-size:13px; margin-top:12px; }

/* RESUMEN */
.resumen-item { display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid #222; font-size:14px; }
.resumen-item:last-of-type { border-bottom:2px solid #2a2a2a; }
.resumen-item .name { color:#fff; font-weight:600; }
.resumen-item .qty { color:rgba(255,255,255,.35); font-size:12px; margin-left:6px; }
.resumen-item .price { color:#fff; font-weight:700; }

.resumen-fila { display:flex; justify-content:space-between; padding:8px 0; font-size:14px; color:rgba(255,255,255,.55); }
.resumen-fila.total { font-size:1.2rem; font-weight:900; color:#fff; padding:14px 0; border-top:1px solid #2a2a2a; margin-top:4px; }

.btn-pedir {
    width:100%; background:#c8102e; color:#fff; border:none;
    border-radius:12px; padding:15px; font-size:15px; font-weight:700;
    cursor:pointer; transition:.2s; margin-top:16px;
    display:flex; align-items:center; justify-content:center; gap:10px;
}
.btn-pedir:hover { background:#a50d26; transform:translateY(-2px); }

.btn-back-link { display:flex; align-items:center; gap:7px; color:rgba(255,255,255,.35); text-decoration:none; font-size:13px; font-weight:600; margin-top:14px; justify-content:center; transition:.2s; }
.btn-back-link:hover { color:#fff; }

.alert-info-dark { background:rgba(255,255,255,.05); border:1px solid #2a2a2a; color:rgba(255,255,255,.5); padding:12px 16px; border-radius:10px; margin-bottom:20px; font-size:13px; display:flex; align-items:center; gap:8px; }
.verified-badge { background:rgba(200,16,46,.15); border:1px solid rgba(200,16,46,.3); color:#ff8080; padding:10px 14px; border-radius:10px; margin-bottom:20px; font-size:13px; display:flex; align-items:center; gap:8px; }

@media(max-width:900px){
    .checkout-grid { grid-template-columns:1fr; }
    .grid-2 { grid-template-columns:1fr; }
    .metodos-grid { grid-template-columns:1fr 1fr; }
}
</style>
</head>
<body>

<nav class="navbar-top">
    <div class="navbar-inner">
        <a href="index.php" class="logo"><i class="fas fa-fire" style="color:#ffcc00;"></i> Brisamar</a>
        <a href="carrito.php" class="btn-nav-back"><i class="fas fa-arrow-left"></i> Volver al carrito</a>
    </div>
</nav>

<div class="page-wrap">

    <?php if(isset($_SESSION['error'])): ?>
    <div class="alert-info-dark"><i class="fas fa-exclamation-circle" style="color:#c8102e;"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if($es_invitado): ?>
    <div class="alert-info-dark"><i class="fas fa-info-circle"></i> Estás comprando como invitado. Proporciona tus datos para completar el pedido.</div>
    <?php elseif($id_usuario): ?>
    <div class="verified-badge"><i class="fas fa-check-circle"></i> <strong><?php echo htmlspecialchars($usuario_nombre); ?></strong> &nbsp;·&nbsp; <?php echo htmlspecialchars($usuario_email); ?></div>
    <?php endif; ?>

    <div class="page-title"><i class="fas fa-receipt" style="color:#c8102e;"></i> Finalizar Pedido</div>

    <form method="POST" action="procesar_pedido.php">
        <div class="checkout-grid">

            <!-- COLUMNA IZQUIERDA -->
            <div>
                <!-- DATOS PERSONALES -->
                <div class="card-dark">
                    <h4><i class="fas fa-user"></i> Datos Personales</h4>
                    <?php if($es_invitado || !$id_usuario): ?>
                    <div class="form-group">
                        <label>Nombre completo</label>
                        <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario_nombre); ?>" required>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Correo</label>
                            <input type="email" name="correo" value="<?php echo htmlspecialchars($usuario_email); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="tel" name="telefono" value="<?php echo htmlspecialchars($usuario_telefono); ?>" required>
                        </div>
                    </div>
                    <?php else: ?>
                    <input type="hidden" name="nombre" value="<?php echo htmlspecialchars($usuario_nombre); ?>">
                    <input type="hidden" name="correo" value="<?php echo htmlspecialchars($usuario_email); ?>">
                    <input type="hidden" name="telefono" value="<?php echo htmlspecialchars($usuario_telefono); ?>">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" value="<?php echo htmlspecialchars($usuario_nombre); ?>" disabled>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Correo</label>
                            <input type="email" value="<?php echo htmlspecialchars($usuario_email); ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="tel" value="<?php echo htmlspecialchars($usuario_telefono); ?>" disabled>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- DIRECCIÓN -->
                <div class="card-dark">
                    <h4><i class="fas fa-map-marker-alt"></i> Dirección de Entrega</h4>
                    <?php if(!$es_invitado && count($direcciones) > 0): ?>
                    <div class="form-group">
                        <label>Dirección guardada</label>
                        <select name="id_direccion">
                            <option value="">-- Nueva dirección --</option>
                            <?php foreach($direcciones as $dir): ?>
                            <option value="<?php echo $dir['id_direccion']; ?>"><?php echo htmlspecialchars($dir['direccion']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label>Dirección</label>
                        <input type="text" name="nueva_direccion" placeholder="Ej: Av. Principal 234" required>
                    </div>
                    <div class="form-group">
                        <label>Referencia (opcional)</label>
                        <input type="text" name="referencia" placeholder="Ej: Frente al parque, casa azul">
                    </div>
                </div>

                <!-- PAGO -->
                <div class="card-dark">
                    <h4><i class="fas fa-credit-card"></i> Método de Pago</h4>
                    <div class="metodos-grid">
                        <label class="metodo-btn activo">
                            <input type="radio" name="metodo_pago" value="efectivo" class="metodo-radio" checked>
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Efectivo</span>
                            <small>Al entregar</small>
                        </label>
                        <label class="metodo-btn">
                            <input type="radio" name="metodo_pago" value="tarjeta" class="metodo-radio">
                            <i class="fas fa-credit-card"></i>
                            <span>Tarjeta</span>
                            <small>Visa / Mastercard</small>
                        </label>
                        <label class="metodo-btn">
                            <input type="radio" name="metodo_pago" value="yape" class="metodo-radio">
                            <i class="fas fa-mobile-screen"></i>
                            <span>Yape</span>
                            <small>App de pagos</small>
                        </label>
                        <label class="metodo-btn">
                            <input type="radio" name="metodo_pago" value="plin" class="metodo-radio">
                            <i class="fas fa-qrcode"></i>
                            <span>Plin</span>
                            <small>App de pagos</small>
                        </label>
                    </div>
                    <div class="qr-box" id="qrPlin">
                        <h5>Código QR - Plin</h5>
                        <img src="../images/pago_QR.png" alt="QR Plin">
                        <p>Escanea este código con tu app Plin para completar el pago</p>
                    </div>
                </div>

                <!-- NOTAS -->
                <div class="card-dark">
                    <h4><i class="fas fa-sticky-note"></i> Notas Especiales</h4>
                    <div class="form-group">
                        <textarea name="notas" rows="3" placeholder="Ej: Sin picante, sin cebolla..." style="resize:none;"></textarea>
                    </div>
                </div>
            </div>

            <!-- COLUMNA DERECHA: RESUMEN -->
            <div>
                <div class="card-dark" style="position:sticky; top:80px;">
                    <h4><i class="fas fa-receipt"></i> Resumen</h4>

                    <?php foreach($items as $item): ?>
                    <div class="resumen-item">
                        <div>
                            <span class="name"><?php echo htmlspecialchars($item['producto']['nombre']); ?></span>
                            <span class="qty">x<?php echo $item['cantidad']; ?></span>
                        </div>
                        <span class="price">S/ <?php echo number_format($item['subtotal'],2); ?></span>
                    </div>
                    <?php endforeach; ?>

                    <div class="resumen-fila"><span>Subtotal</span><span>S/ <?php echo number_format($total,2); ?></span></div>
                    <div class="resumen-fila"><span>IGV (18%)</span><span>S/ <?php echo number_format($impuesto_total,2); ?></span></div>
                    <div class="resumen-fila"><span><i class="fas fa-truck"></i> Delivery</span><span>S/ <?php echo number_format($delivery,2); ?></span></div>
                    <div class="resumen-fila total"><span>Total</span><span>S/ <?php echo number_format($total_con_impuesto,2); ?></span></div>

                    <button type="submit" class="btn-pedir"><i class="fas fa-check-circle"></i> Confirmar Pedido</button>
                    <a href="carrito.php" class="btn-back-link"><i class="fas fa-arrow-left"></i> Volver al carrito</a>
                </div>
            </div>

        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.metodo-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.metodo-btn').forEach(b => b.classList.remove('activo'));
        this.classList.add('activo');
        const val = this.querySelector('input').value;
        document.getElementById('qrPlin').style.display = val === 'plin' ? 'block' : 'none';
    });
});
</script>
</body>
</html>
