<?php
session_start();
require_once dirname(__FILE__) . '/../conexion.php';
require_once dirname(__FILE__) . '/../includes/functions.php';

global $conexion;

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

/**
 * @param string $rutaImagen
 * @return bool
 */
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
<link rel="stylesheet" href="../assets/css/carrito.css">
</head>
<body>

<nav class="navbar-top">
    <div class="navbar-inner">
        <a href="index.php" class="logo"><i class="fas fa-fire icon-fire"></i> Brisamar</a>
        <a href="index.php" class="btn-nav-back"><i class="fas fa-arrow-left"></i> Volver al menú</a>
    </div>
</nav>

<div class="page-wrap">

    <?php if(isset($_SESSION['mensaje'])): ?>
    <div class="toast-msg">
        <i class="fas fa-check-circle icon-danger"></i>
        <span><?php echo $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></span>
    </div>
    <?php endif; ?>

    <div class="global-layout">
        <!-- IZQUIERDA: Pedidos por hacer -->
        <div class="global-left">
            <div class="page-title"><i class="fas fa-shopping-bag icon-danger"></i> Pedidos por hacer (Mi carrito)</div>

            <?php if(empty($items)): ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-bag"></i>
                <h3>Tu carrito está vacío</h3>
                <p>Agrega productos desde el menú para continuar</p>
                <a href="index.php" class="btn-primary-red"><i class="fas fa-utensils"></i> Ver Menú</a>
            </div>
            <?php else: ?>
            <div class="cart-main">
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
                            <?php $imgSrc = preg_match('#^https?://#i', $item['producto']['imagen']) ? $item['producto']['imagen'] : ('../' . ltrim($item['producto']['imagen'], '/')); ?>
                            <img src="<?php echo htmlspecialchars($imgSrc); ?>"
                                 class="prod-thumb"
                                 onerror="handleProductImageError(this)">
                            <div class="prod-thumb-fallback d-none"><i class="fas fa-utensils"></i></div>
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
                
                <div class="cart-sidebar-box mt-4" style="position: static;">
                    <h3>Resumen del pedido</h3>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>S/ <?php echo number_format($total,2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Envío</span>
                        <span>Por calcular</span>
                    </div>
                    <div class="summary-total">
                        <span>Total</span>
                        <span>S/ <?php echo number_format($total,2); ?></span>
                    </div>
                    
                    <div style="display:flex; gap:15px; flex-wrap:wrap;">
                        <a href="index.php" class="btn-outline-w" style="flex:1; justify-content:center; margin-bottom:0;"><i class="fas fa-arrow-left"></i> Seguir comprando</a>
                        <?php if(isset($_SESSION['usuario']) && !empty($_SESSION['usuario'])): ?>
                            <a href="checkout.php" class="btn-primary-red" style="flex:1; justify-content:center; margin-bottom:0;"><i class="fas fa-credit-card"></i> Finalizar pedido</a>
                        <?php else: ?>
                            <a href="login.php?next=checkout.php" class="btn-primary-red" style="flex:1; justify-content:center; margin-bottom:0;"><i class="fas fa-user"></i> Iniciar sesión</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- DERECHA: Pedidos hechos -->
        <div class="global-right">
            <div class="order-section" style="margin-top:0; border:none; background:transparent; padding:0;">
                <h2><i class="fas fa-list"></i> Pedidos hechos</h2>
                <?php if(!empty($mis_pedidos)): ?>
                <div class="orders-list">
                    <?php foreach($mis_pedidos as $pedido): ?>
                        <?php $clase_estado = 'status-' . str_replace(' ', '-', trim($pedido['estado'])); ?>
                        <div class="order-card">
                            <div class="order-line">
                                <span><strong>Pedido #<?php echo intval($pedido['id_pedido']); ?></strong></span>
                                <div class="flex-center-gap">
                                    <span class="status-badge <?php echo htmlspecialchars($clase_estado); ?>"><?php echo htmlspecialchars(ucfirst($pedido['estado'])); ?></span>
                                    <a href="generar_voucher.php?id_pedido=<?php echo intval($pedido['id_pedido']); ?>"
                                       title="Descargar Voucher PDF"
                                       class="btn-voucher"
                                       download>
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="order-details" style="grid-template-columns: 1fr 1fr;">
                                <span><strong>Total:</strong> S/ <?php echo number_format($pedido['total'],2); ?></span>
                                <span><strong>Método:</strong> <?php echo htmlspecialchars(ucfirst($pedido['metodo'] ?? 'efectivo')); ?></span>
                                <span><strong>Pago:</strong> <?php echo htmlspecialchars(ucfirst($pedido['estado_pago'] ?? 'pendiente')); ?></span>
                                <span class="address-line" title="<?php echo htmlspecialchars($pedido['direccion'] ?? 'No definida'); ?>"><strong>Dirección:</strong> <?php echo htmlspecialchars($pedido['direccion'] ?? 'No definida'); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-cart" style="padding:40px 20px;">
                    <i class="fas fa-receipt"></i>
                    <h3>Sin pedidos previos</h3>
                    <p>Tus compras completadas aparecerán aquí</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/carrito.js"></script>
</body>
</html>
