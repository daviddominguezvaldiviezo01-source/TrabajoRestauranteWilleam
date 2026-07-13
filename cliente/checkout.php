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

// Validar que el usuario no sea admin o delivery
if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['admin', 'delivery'])) {
    $_SESSION['error'] = 'Los administradores y repartidores no pueden hacer pedidos.';
    if ($_SESSION['rol'] === 'admin') {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: ../delivery.php');
    }
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
        $subtotal = round(floatval($fila['precio']) * intval($cantidad), 2);
        $total += $subtotal;
        $items[] = ['producto' => $fila, 'cantidad' => $cantidad, 'subtotal' => $subtotal];
    }
}

$total = round($total, 2);
$impuesto_total    = round($total * $impuesto, 2);
$total_con_impuesto = round($total + $impuesto_total + $delivery, 2);

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
<link rel="stylesheet" href="../assets/css/checkout.css">
</head>
<body>

<nav class="navbar-top">
    <div class="navbar-inner">
        <a href="index.php" class="logo"><i class="fas fa-fire icon-fire"></i> Brisamar</a>
        <a href="carrito.php" class="btn-nav-back"><i class="fas fa-arrow-left"></i> Volver al carrito</a>
    </div>
</nav>

<div class="page-wrap">

    <?php if(isset($_SESSION['error'])): ?>
    <div class="alert-info-dark"><i class="fas fa-exclamation-circle icon-danger"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if($es_invitado): ?>
    <div class="alert-info-dark"><i class="fas fa-info-circle"></i> Estás comprando como invitado. Proporciona tus datos para completar el pedido.</div>
    <?php elseif($id_usuario): ?>
    <div class="verified-badge"><i class="fas fa-check-circle"></i> <strong><?php echo htmlspecialchars($usuario_nombre); ?></strong> &nbsp;·&nbsp; <?php echo htmlspecialchars($usuario_email); ?></div>
    <?php endif; ?>

    <div class="page-title"><i class="fas fa-receipt icon-danger"></i> Finalizar Pedido</div>

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
                            <option value="<?php echo $dir['id_direccion']; ?>" data-dir="<?php echo htmlspecialchars($dir['direccion']); ?>" data-ref="<?php echo htmlspecialchars($dir['referencia']); ?>"><?php echo htmlspecialchars($dir['direccion']); ?></option>
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
                    <div class="mt-12">
                        <label class="checkout-label">Tipo de entrega</label>
                        <select name="tipo_entrega" class="checkout-select">
                            <option value="domicilio">Envío a domicilio</option>
                            <option value="ir a recoger">Ir a recoger (recoger en tienda)</option>
                        </select>
                    </div>
                </div>

                <!-- NOTAS -->
                <div class="card-dark">
                    <h4><i class="fas fa-sticky-note"></i> Notas Especiales</h4>
                    <div class="form-group">
                        <textarea name="notas" rows="3" placeholder="Ej: Sin picante, sin cebolla..." class="resize-none"></textarea>
                    </div>
                </div>
            </div>

            <!-- COLUMNA DERECHA: RESUMEN -->
            <div>
                <div class="card-dark sticky-top-80">
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

<script src="../assets/js/checkout.js"></script>
</body>
</html>
