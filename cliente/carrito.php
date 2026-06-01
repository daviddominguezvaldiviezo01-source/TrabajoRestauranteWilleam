<?php
session_start();
include(__DIR__ . '/../conexion.php');

// Evitar que usuarios con rol 'delivery' vean el carrito/cliente
include(__DIR__ . '/guard_delivery.php');

$total = 0;
$items = [];

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
                <?php if(!empty($item['producto']['imagen'])): ?>
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
            <a href="checkout.php" class="btn-primary-red"><i class="fas fa-credit-card"></i> Finalizar pedido</a>
        </div>
    </div>

    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
