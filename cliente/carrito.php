<?php
session_start();
require_once dirname(__FILE__) . '/../conexion.php';
require_once dirname(__FILE__) . '/../includes/functions.php';

$total = 0;
$items = [];

$id_usuario = isset($_SESSION['usuario']) ? intval($_SESSION['usuario']) : null;
$mis_pedidos = [];
if ($id_usuario) {
    $mis_pedidos = obtener_pedidos_cliente($id_usuario);
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

if (isset($_SESSION['carrito']) && count($_SESSION['carrito']) > 0) {
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
}

function imagen_producto_valida($rutaImagen) {
    if (empty($rutaImagen)) {
        return false;
    }

    if (preg_match('#^https?://#i', $rutaImagen)) {
        return true;
    }

    $rutaServidor = __DIR__ . '/../' . ltrim($rutaImagen, '/');
    return is_file($rutaServidor);
}

$totalCarrito = isset($_SESSION['carrito']) ? array_sum($_SESSION['carrito']) : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Carrito - Brisamar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { background:#111; font-family:'Segoe UI',sans-serif; color:#fff; min-height:100vh; }

/* NAVBAR */
.navbar-top {
    background:#c8102e;
    padding:0 40px;
    height:64px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    position:sticky;
    top:0;
    z-index:200;
    box-shadow:0 2px 12px rgba(0,0,0,.5);
}
.navbar-inner {
    max-width:1300px;
    width:100%;
    margin:0 auto;
    display:flex;
    align-items:center;
    justify-content:space-between;
}
.logo { font-size:22px; font-weight:900; color:#fff; text-decoration:none; display:flex; align-items:center; gap:10px; }
.btn-nav-back { color:#fff; text-decoration:none; font-weight:600; font-size:14px; display:flex; align-items:center; gap:7px; opacity:.85; transition:.2s; }
.btn-nav-back:hover { opacity:1; color:#fff; }

/* CONTENIDO */
.page-wrap { max-width:1000px; margin:40px auto; padding:0 20px 60px; }

.page-title { font-size:1.8rem; font-weight:900; margin-bottom:28px; display:flex; align-items:center; gap:12px; }

/* TOAST */
.toast-msg { background:#1e1e1e; border-left:4px solid #c8102e; color:#fff; padding:14px 20px; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,.4); display:flex; align-items:center; gap:10px; font-size:14px; margin-bottom:24px; }

/* TABLA */
.cart-table { width:100%; border-collapse:collapse; }
.cart-table thead tr { background:#1e1e1e; }
.cart-table th { padding:14px 18px; font-size:13px; font-weight:700; color:rgba(255,255,255,.5); text-transform:uppercase; letter-spacing:.6px; border-bottom:1px solid #2a2a2a; }
.cart-table td { padding:16px 18px; border-bottom:1px solid #1e1e1e; vertical-align:middle; }
.cart-table tbody tr { background:#161616; transition:.2s; }
.cart-table tbody tr:hover { background:#1e1e1e; }

.prod-thumb { width:64px; height:64px; object-fit:cover; border-radius:10px; background:#2a2a2a; }
.prod-thumb-fallback { width:64px; height:64px; border-radius:10px; background:#2a2a2a; display:flex; align-items:center; justify-content:center; color:rgba(255,255,255,.2); font-size:1.4rem; }
.prod-name { font-weight:700; font-size:15px; color:#fff; }
.price-cell { color:#fff; font-weight:700; font-size:15px; }
.qty-badge { background:#2a2a2a; color:#fff; padding:5px 14px; border-radius:20px; font-weight:700; font-size:14px; display:inline-block; }
.btn-del { background:#c8102e; color:#fff; border:none; width:34px; height:34px; border-radius:8px; display:flex; align-items:center; justify-content:center; text-decoration:none; transition:.2s; font-size:13px; }
.btn-del:hover { background:#a50d26; color:#fff; }

/* TOTAL BOX */
.total-bar {
    background:#1e1e1e;
    border:1px solid #2a2a2a;
    border-radius:16px;
    padding:24px 28px;
    margin-top:24px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:20px;
    flex-wrap:wrap;
}
.total-label { font-size:13px; color:rgba(255,255,255,.45); margin-bottom:4px; }
.total-amount { font-size:2rem; font-weight:900; color:#fff; }
.total-actions { display:flex; gap:12px; align-items:center; flex-wrap:wrap; }

.btn-primary-red { background:#c8102e; color:#fff; border:none; border-radius:30px; padding:12px 28px; font-weight:700; font-size:15px; text-decoration:none; display:flex; align-items:center; gap:8px; transition:.2s; }
.btn-primary-red:hover { background:#a50d26; color:#fff; transform:translateY(-2px); }
.btn-outline-w { background:transparent; color:rgba(255,255,255,.6); border:1.5px solid rgba(255,255,255,.2); border-radius:30px; padding:11px 22px; font-weight:600; font-size:14px; text-decoration:none; transition:.2s; }
.btn-outline-w:hover { border-color:rgba(255,255,255,.5); color:#fff; }

/* VACÍO */
.empty-cart { text-align:center; padding:80px 20px; color:rgba(255,255,255,.25); }
.empty-cart i { font-size:5rem; display:block; margin-bottom:20px; }
.empty-cart h3 { font-size:1.3rem; margin-bottom:8px; }
.empty-cart p { font-size:14px; margin-bottom:24px; }

.order-section {
    margin-top:40px;
    background:#1e1e1e;
    border:1px solid #2a2a2a;
    border-radius:20px;
    padding:24px;
}
.order-section h2 {
    font-size:1.2rem;
    font-weight:800;
    margin-bottom:18px;
    color:#fff;
    display:flex;
    align-items:center;
    gap:10px;
}
.order-card {
    background:#111;
    border:1px solid #2a2a2a;
    border-radius:16px;
    padding:18px 20px;
    margin-bottom:16px;
}
.order-card:last-child { margin-bottom:0; }
.order-card .order-line {
    display:flex;
    flex-wrap:wrap;
    align-items:center;
    justify-content:space-between;
    gap:8px;
    margin-bottom:12px;
}
.order-card .order-line span { color:rgba(255,255,255,.8); font-size:14px; }
.order-card .order-details {
    display:grid;
    grid-template-columns:repeat(2,minmax(180px,1fr));
    gap:10px;
    color:rgba(255,255,255,.65);
    font-size:13px;
}
.order-card .order-details span { display:block; }
.status-badge {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:6px 12px;
    border-radius:999px;
    font-size:12px;
    font-weight:700;
    text-transform:capitalize;
}
.status-pendiente { background:rgba(255,193,7,.15); color:#ffc107; }
.status-ir-a-recoger { background:rgba(0,123,255,.12); color:#65a5ff; }
.status-en-camino { background:rgba(13,110,253,.12); color:#8ec9ff; }
.status-entregado { background:rgba(25,135,84,.12); color:#7dd19d; }
.status-cancelado { background:rgba(220,53,69,.12); color:#ff6b7a; }
.order-empty { color:rgba(255,255,255,.55); font-size:14px; }
</style>
</head>
<body>

<nav class="navbar-top">
    <div class="navbar-inner">
        <a href="index.php" class="logo"><i class="fas fa-fire" style="color:#ffcc00;"></i> Brisamar</a>
        <a href="index.php" class="btn-nav-back"><i class="fas fa-arrow-left"></i> Volver al menú</a>
    </div>
</nav>

<div class="page-wrap">

    <?php if(isset($_SESSION['mensaje'])): ?>
    <div class="toast-msg">
        <i class="fas fa-check-circle" style="color:#c8102e;"></i>
        <span><?php echo $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></span>
    </div>
    <?php endif; ?>

    <div class="page-title"><i class="fas fa-shopping-bag" style="color:#c8102e;"></i> Mi Carrito</div>

    <?php if(!empty($mis_pedidos)): ?>
    <div class="order-section">
        <h2><i class="fas fa-list"></i> Mis pedidos recientes</h2>
        <?php foreach($mis_pedidos as $pedido): ?>
            <?php $clase_estado = 'status-' . str_replace(' ', '-', trim($pedido['estado'])); ?>
            <div class="order-card">
                <div class="order-line">
                    <span><strong>Pedido #<?php echo intval($pedido['id_pedido']); ?></strong></span>
                    <span class="status-badge <?php echo htmlspecialchars($clase_estado); ?>"><?php echo htmlspecialchars(ucfirst($pedido['estado'])); ?></span>
                </div>
                <div class="order-details">
                    <span><strong>Total:</strong> S/ <?php echo number_format($pedido['total'],2); ?></span>
                    <span><strong>Método:</strong> <?php echo htmlspecialchars(ucfirst($pedido['metodo'] ?? 'efectivo')); ?></span>
                    <span><strong>Pago:</strong> <?php echo htmlspecialchars(ucfirst($pedido['estado_pago'] ?? 'pendiente')); ?></span>
                    <span><strong>Dirección:</strong> <?php echo htmlspecialchars($pedido['direccion'] ?? 'No definida'); ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if(empty($items)): ?>
    <div class="empty-cart">
        <i class="fas fa-shopping-bag"></i>
        <h3>Tu carrito está vacío</h3>
        <p>Agrega productos desde el menú para continuar</p>
        <a href="index.php" class="btn-primary-red"><i class="fas fa-utensils"></i> Ver Menú</a>
    </div>
    <?php else: ?>

    <table class="cart-table">
        <thead>
            <tr>
                <th>Imagen</th>
                <th>Producto</th>
                <th>Precio</th>
                <th>Cantidad</th>
                <th>Subtotal</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($items as $item): ?>
        <tr>
            <td>
                <?php $imagenValida = imagen_producto_valida($item['producto']['imagen'] ?? ''); ?>
                <?php if ($imagenValida): ?>
                    <img src="<?php echo htmlspecialchars($item['producto']['imagen']); ?>"
                         class="prod-thumb"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="prod-thumb-fallback" style="display:none;"><i class="fas fa-utensils"></i></div>
                <?php else: ?>
                    <div class="prod-thumb-fallback"><i class="fas fa-utensils"></i></div>
                <?php endif; ?>
            </td>
            <td><div class="prod-name"><?php echo htmlspecialchars($item['producto']['nombre']); ?></div></td>
            <td class="price-cell">S/ <?php echo number_format($item['producto']['precio'],2); ?></td>
            <td><span class="qty-badge"><?php echo $item['cantidad']; ?></span></td>
            <td class="price-cell">S/ <?php echo number_format($item['subtotal'],2); ?></td>
            <td>
                <a href="eliminar_carrito.php?key=<?php echo $item['producto']['id_producto']; ?>"
                   class="btn-del" onclick="return confirm('¿Eliminar este producto?')">
                    <i class="fas fa-trash"></i>
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total-bar">
        <div>
            <div class="total-label">Total a pagar</div>
            <div class="total-amount">S/ <?php echo number_format($total,2); ?></div>
        </div>
        <div class="total-actions">
            <a href="index.php" class="btn-outline-w"><i class="fas fa-arrow-left"></i> Seguir comprando</a>
            <?php if(isset($_SESSION['usuario']) && !empty($_SESSION['usuario'])): ?>
                <a href="checkout.php" class="btn-primary-red"><i class="fas fa-credit-card"></i> Finalizar pedido</a>
            <?php else: ?>
                <a href="login.php?next=checkout.php" class="btn-primary-red"><i class="fas fa-user"></i> Iniciar sesión para pagar</a>
            <?php endif; ?>
        </div>
    </div>

    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
