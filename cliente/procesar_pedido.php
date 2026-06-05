<?php
session_start();
require_once dirname(__FILE__) . '/../conexion.php';

$id_usuario = isset($_SESSION['usuario']) ? intval($_SESSION['usuario']) : null;

if (!$id_usuario) {
    $_SESSION['error'] = 'Debes iniciar sesión antes de procesar el pedido.';
    header('Location: login.php');
    exit();
}

if (!isset($_SESSION['carrito']) || count($_SESSION['carrito']) == 0) {
    header("Location: carrito.php");
    exit();
}

// Obtener datos del formulario
$nombre = trim($_POST['nombre'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$nueva_direccion = trim($_POST['nueva_direccion'] ?? '');
$referencia = trim($_POST['referencia'] ?? '');
$id_direccion = intval($_POST['id_direccion'] ?? 0);
$metodo_pago = $_POST['metodo_pago'] ?? 'efectivo';
$notas = trim($_POST['notas'] ?? '');
$total_final = floatval($_POST['total'] ?? 0);

// Validar datos
$errores = [];
if (empty($nombre)) $errores[] = "Nombre requerido";
if (empty($correo)) $errores[] = "Correo requerido";
if (empty($telefono)) $errores[] = "Teléfono requerido";
if (empty($nueva_direccion) && empty($id_direccion)) $errores[] = "Dirección requerida";

if (!empty($errores)) {
    $_SESSION['error'] = implode(", ", $errores);
    header("Location: checkout.php");
    exit();
}

// Validar método de pago
$metodos_validos = ['efectivo','tarjeta','yape','plin'];
if (!in_array($metodo_pago, $metodos_validos)) {
    $metodo_pago = 'efectivo';
}

// ==========================================
// MANEJAR DIRECCIÓN (CORREGIDO)
// ==========================================
if (!empty($nueva_direccion)) {
    if ($id_usuario) {
        $stmtDir = mysqli_prepare($conexion, "INSERT INTO direcciones (id_usuario, direccion, referencia) VALUES (?,?,?)");
        mysqli_stmt_bind_param($stmtDir, "iss", $id_usuario, $nueva_direccion, $referencia);
        
        // CORRECCIÓN: Ejecutar solo si se preparó la consulta del usuario registrado
        mysqli_stmt_execute($stmtDir);
        $id_direccion = mysqli_insert_id($conexion);
    } else {
        // Para invitados, solo usar la dirección sin guardarla en la tabla 'direcciones'
        $id_direccion = null;
    }
} else {
    // Validar que la dirección seleccionada pertenezca al usuario
    if ($id_usuario && !empty($id_direccion)) {
        $stmtValidate = mysqli_prepare($conexion, "SELECT id_direccion FROM direcciones WHERE id_direccion = ? AND id_usuario = ?");
        mysqli_stmt_bind_param($stmtValidate, "ii", $id_direccion, $id_usuario);
        mysqli_stmt_execute($stmtValidate);
        $resValidate = mysqli_stmt_get_result($stmtValidate);
        if (mysqli_num_rows($resValidate) == 0) {
            $id_direccion = null;
        }
    } else {
        $id_direccion = null;
    }
}

// Calcular total y validar carrito
$total = 0;
$items_carrito = [];

foreach ($_SESSION['carrito'] as $id_producto => $cantidad) {
    $id = intval($id_producto);
    $stmt = mysqli_prepare($conexion, "SELECT id_producto, nombre, precio, stock FROM productos WHERE id_producto = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    
    if ($fila = mysqli_fetch_assoc($res)) {
        if ($fila['stock'] < $cantidad) {
            $_SESSION['error'] = "Producto " . htmlspecialchars($fila['nombre']) . " sin stock suficiente";
            header("Location: carrito.php");
            exit();
        }
        $subtotal = $fila['precio'] * $cantidad;
        $total += $subtotal;
        $items_carrito[] = [
            'id_producto' => $id,
            'nombre' => $fila['nombre'],
            'cantidad' => $cantidad,
            'precio' => $fila['precio'],
            'subtotal' => $subtotal
        ];
    }
}

// Aplicar cálculos de impuesto y delivery
$impuesto = $total * 0.18; // 18% IGV
$delivery = 5; // Costo fijo
$total_con_impuesto = $total + $impuesto + $delivery;

// Insertar pedido (evitar columnas inexistentes)
$estado_pedido = 'pendiente';

// Comprobar si existen columnas para datos de cliente en pedidos
$has_guest_columns = false;
$resCols = mysqli_query($conexion, "SHOW COLUMNS FROM pedidos LIKE 'nombre_cliente'");
if ($resCols && mysqli_num_rows($resCols) > 0) {
    $has_guest_columns = true;
}

if ($has_guest_columns) {
    $stmtPedido = mysqli_prepare($conexion,
        "INSERT INTO pedidos (id_usuario, id_direccion, estado, total, nombre_cliente, email_cliente, telefono_cliente) VALUES (?,?,?,?,?,?,?)");
    mysqli_stmt_bind_param($stmtPedido, "iisdsss", $id_usuario, $id_direccion, $estado_pedido, $total_con_impuesto, $nombre, $correo, $telefono);
} else {
    $stmtPedido = mysqli_prepare($conexion,
        "INSERT INTO pedidos (id_usuario, id_direccion, estado, total) VALUES (?,?,?,?)");
    mysqli_stmt_bind_param($stmtPedido, "iisd", $id_usuario, $id_direccion, $estado_pedido, $total_con_impuesto);
}

if (!mysqli_stmt_execute($stmtPedido)) {
    $_SESSION['error'] = "Error al crear el pedido. Intenta nuevamente.";
    header("Location: checkout.php");
    exit();
}

$id_pedido = mysqli_insert_id($conexion);

// Insertar detalles del pedido y descontar stock
foreach ($items_carrito as $item) {
    $id_prod = $item['id_producto'];
    $cant = $item['cantidad'];
    $subtotal = $item['subtotal'];
    
    // Insertar detalle
    $stmtDet = mysqli_prepare($conexion,
        "INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, subtotal) VALUES (?,?,?,?)");
    mysqli_stmt_bind_param($stmtDet, "iiid", $id_pedido, $id_prod, $cant, $subtotal);
    mysqli_stmt_execute($stmtDet);
    
    // Descontar stock
    $stmtStock = mysqli_prepare($conexion,
        "UPDATE productos SET stock = stock - ? WHERE id_producto = ?");
    mysqli_stmt_bind_param($stmtStock, "ii", $cant, $id_prod);
    mysqli_stmt_execute($stmtStock);
}

// Insertar pago
$estado_pago = 'pagado';
$stmtPago = mysqli_prepare($conexion,
    "INSERT INTO pagos (id_pedido, metodo, estado) VALUES (?,?,?)");
mysqli_stmt_bind_param($stmtPago, "iss", $id_pedido, $metodo_pago, $estado_pago);
mysqli_stmt_execute($stmtPago);

    $_SESSION['mensaje'] = "¡Pedido #{$id_pedido} realizado con éxito! 🎉";

// Limpiar carrito y cualquier marca de invitado residual
unset($_SESSION['carrito']);
unset($_SESSION['invitado']);

if (!isset($_SESSION['mensaje']) && !isset($_SESSION['error'])) {
    $_SESSION['mensaje'] = "¡Pedido #$id_pedido realizado con éxito! 🎉";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta http-equiv="refresh" content="5;url=index.php">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pedido confirmado - Mi Restaurante</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
* { margin:0; padding:0; box-sizing:border-box; }
body {
    background:#111;
    min-height:100vh;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    font-family:'Segoe UI',sans-serif;
    color:#fff;
    padding:20px;
}
.boucher {
    background:#1a1a1a;
    border:1px solid #2a2a2a;
    border-radius:20px;
    padding:40px 32px;
    max-width:460px;
    width:100%;
    text-align:center;
    box-shadow:0 20px 60px rgba(0,0,0,.5);
}
.check-circle {
    width:80px;height:80px;
    background:rgba(76,175,80,.15);
    border:2px solid rgba(76,175,80,.4);
    border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    margin:0 auto 20px;
    animation:pulse 1.5s infinite;
}
.check-circle i { font-size:36px; color:#81c784; }
@keyframes pulse{0%{transform:scale(1)}50%{transform:scale(1.06)}100%{transform:scale(1)}}
.boucher h1 { font-size:1.6rem; font-weight:900; color:#fff; margin-bottom:6px; }
.boucher .sub { color:rgba(255,255,255,.4); font-size:14px; margin-bottom:24px; }
.pedido-num {
    background:#111;border:1px solid #2a2a2a;border-radius:12px;
    padding:14px;font-size:1.4rem;font-weight:900;color:#fff;
    margin-bottom:20px;letter-spacing:1px;
}
.info-box {
    background:#111;border:1px solid #2a2a2a;border-radius:12px;
    padding:16px;margin-bottom:20px;text-align:left;
}
.info-row {
    display:flex;justify-content:space-between;align-items:center;
    padding:9px 0;border-bottom:1px solid #1e1e1e;font-size:14px;
}
.info-row:last-child{border-bottom:none;}
.info-row .lbl{color:rgba(255,255,255,.4);font-weight:600;}
.info-row .val{color:#fff;font-weight:700;}
.btn-home {
    background:#c8102e;color:#fff;border:none;border-radius:30px;
    padding:12px 28px;font-weight:700;font-size:14px;
    text-decoration:none;display:inline-flex;align-items:center;gap:8px;
    transition:.2s;
}
.btn-home:hover{background:#a50d26;color:#fff;transform:translateY(-2px);}
.btn-sec {
    background:transparent;color:rgba(255,255,255,.5);
    border:1.5px solid #2a2a2a;border-radius:30px;
    padding:11px 22px;font-weight:600;font-size:14px;
    text-decoration:none;display:inline-flex;align-items:center;gap:8px;
    transition:.2s;
}
.btn-sec:hover{border-color:rgba(255,255,255,.3);color:#fff;}
.countdown{font-size:12px;color:rgba(255,255,255,.25);margin-top:18px;}
</style>
</head>
<body>
<div class="boucher">
    <div class="check-circle"><i class="fas fa-check"></i></div>
    <h1>¡Pedido Confirmado!</h1>
    <p class="sub">Tu pedido ha sido registrado exitosamente</p>
    <div class="pedido-num"># <?php echo $id_pedido; ?></div>
    <div class="info-box">
        <div class="info-row">
            <span class="lbl"><i class="fas fa-money-bill"></i> Total</span>
            <span class="val">S/ <?php echo number_format($total_con_impuesto,2); ?></span>
        </div>
        <div class="info-row">
            <span class="lbl"><i class="fas fa-credit-card"></i> Método de pago</span>
            <span class="val"><?php echo ucfirst($metodo_pago); ?></span>
        </div>
        <div class="info-row">
            <span class="lbl"><i class="fas fa-clock"></i> Estado</span>
            <span class="val" style="color:#ffc107;">Pendiente</span>
        </div>
        <div class="info-row">
            <span class="lbl"><i class="fas fa-user"></i> Cliente</span>
            <span class="val"><?php echo htmlspecialchars($nombre); ?></span>
        </div>
        <div class="info-row">
            <span class="lbl"><i class="fas fa-map-marker-alt"></i> Dirección</span>
            <span class="val" style="font-size:13px;max-width:200px;text-align:right;"><?php echo htmlspecialchars($nueva_direccion.($referencia?' ('.$referencia.')':'')); ?></span>
        </div>
    </div>
    <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
        <a href="index.php" class="btn-home"><i class="fas fa-home"></i> Volver al Menú</a>
        <?php if($id_usuario): ?>
        <a href="carrito.php" class="btn-sec"><i class="fas fa-list"></i> Mis Pedidos</a>
        <?php endif; ?>
    </div>
    <div class="countdown"><i class="fas fa-spinner fa-spin"></i> Redirigiendo en <span id="t">5</span>s...</div>
</div>

<script>
let t=5;
const el=document.getElementById('t');
setInterval(()=>{ t--; if(el) el.textContent=t; },1000);
</script>

</body>
</html>