<?php
session_start();
require_once dirname(__FILE__) . '/../conexion.php';
/** @var mysqli $conexion */
require_once dirname(__FILE__) . '/../config/config.php';

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
if (empty($id_direccion) && !empty($nueva_direccion)) {
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
    $cantidad = intval($cantidad);
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
        $subtotal = round(floatval($fila['precio']) * $cantidad, 2);
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

$total = round($total, 2);
// Aplicar cálculos de impuesto y delivery
$impuesto = round($total * 0.18, 2); // 18% IGV
$delivery = 5.00; // Costo fijo
$total_con_impuesto = round($total + $impuesto + $delivery, 2);

// Insertar pedido (evitar columnas inexistentes)
// Determinar estado inicial según tipo de entrega (si el cliente elige "ir a recoger")
$tipo_entrega = trim($_POST['tipo_entrega'] ?? 'domicilio');
$estado_pedido = ($tipo_entrega === 'ir a recoger') ? 'ir a recoger' : 'pendiente';

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

// ── Envío de voucher por correo (PHPMailer + Gmail SMTP) ──
$email_resultado = ['ok' => false, 'error' => 'Correo no habilitado'];
if (defined('MAIL_ENABLED') && MAIL_ENABLED && !empty($correo)) {
    if (!defined('FPDFONTPATH')) {
        define('FPDFONTPATH', __DIR__ . '/../vendor/fpdf/font/');
    }
    require_once __DIR__ . '/../tools/mailer.php';
    $datos_pedido_mail = [
        'id_pedido'         => $id_pedido,
        'nombre'            => $nombre,
        'correo'            => $correo,
        'telefono'          => $telefono,
        'direccion'         => !empty($nueva_direccion) ? $nueva_direccion . ($referencia ? ' (' . $referencia . ')' : '') : 'Sin dirección',
        'estado'            => $estado_pedido,
        'metodo_pago'       => $metodo_pago,
        'metodo_pago_label' => ucfirst($metodo_pago),
        'total'             => $total_con_impuesto,
        'total_fmt'         => number_format($total_con_impuesto, 2),
        'fecha'             => date('Y-m-d H:i:s'),
    ];
    $email_resultado = enviar_voucher_email($datos_pedido_mail, $items_carrito, $correo, $nombre);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pedido confirmado - Mi Restaurante</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<link rel="stylesheet" href="../assets/css/procesar_pedido.css">
</head>
<body>
<div class="boucher">
    <div class="check-circle"><i class="fas fa-check"></i></div>
    <h1>¡Pedido Confirmado!</h1>
    <p class="sub">Tu pedido ha sido registrado exitosamente</p>
    <div class="pedido-num"># <?php echo str_pad($id_pedido, 6, '0', STR_PAD_LEFT); ?></div>

    <!-- Estado del envio de correo -->
    <?php if ($email_resultado['ok']): ?>
    <div class="email-badge ok">
        <i class="fas fa-envelope-circle-check"></i>
        <span>Voucher enviado a <strong><?php echo htmlspecialchars($correo); ?></strong></span>
    </div>
    <?php elseif (!empty($correo)): ?>
    <div class="email-badge err">
        <i class="fas fa-triangle-exclamation"></i>
        <span>No se pudo enviar el correo.
            <?php if (!empty($email_resultado['error'])): ?>
                Error: <?php echo htmlspecialchars($email_resultado['error']); ?>.
            <?php endif; ?>
            Descarga tu voucher más abajo.
        </span>
    </div>
    <?php endif; ?>

    <div class="info-box">
        <div class="info-row">
            <span class="lbl"><i class="fas fa-user"></i> Cliente</span>
            <span class="val"><?php echo htmlspecialchars($nombre); ?></span>
        </div>
        <div class="info-row">
            <span class="lbl"><i class="fas fa-map-marker-alt"></i> Dirección</span>
            <span class="val"><?php echo htmlspecialchars($nueva_direccion.($referencia?' ('.$referencia.')':'')); ?></span>
        </div>
        <div class="info-row">
            <span class="lbl"><i class="fas fa-credit-card"></i> Método de pago</span>
            <span class="val"><?php echo ucfirst($metodo_pago); ?></span>
        </div>
        <div class="info-row">
            <span class="lbl"><i class="fas fa-clock"></i> Estado</span>
            <span class="val text-warning-custom">Pendiente</span>
        </div>
        
        <div class="divider"></div>
        
        <div class="info-row">
            <span class="lbl">Subtotal</span>
            <span class="val">S/ <?php echo number_format($total, 2); ?></span>
        </div>
        <div class="info-row">
            <span class="lbl">IGV (18%)</span>
            <span class="val">S/ <?php echo number_format($impuesto, 2); ?></span>
        </div>
        <div class="info-row">
            <span class="lbl">Delivery</span>
            <span class="val">S/ <?php echo number_format($delivery, 2); ?></span>
        </div>
        
        <div class="divider"></div>
        
        <div class="info-row total-row">
            <span class="lbl">Total a Pagar</span>
            <span class="val">S/ <?php echo number_format($total_con_impuesto,2); ?></span>
        </div>
    </div>

    <!-- Botones de accion -->
    <div class="btn-group">
        <a href="generar_voucher.php?id_pedido=<?php echo $id_pedido; ?>" class="btn-download" download>
            <i class="fas fa-file-pdf"></i> Descargar Voucher
        </a>
        <a href="index.php" class="btn-home"><i class="fas fa-home"></i> Inicio</a>
        <?php if($id_usuario): ?>
        <a href="carrito.php" class="btn-sec"><i class="fas fa-list"></i> Mis Pedidos</a>
        <?php endif; ?>
    </div>
    <div class="countdown"><i class="fas fa-spinner fa-spin"></i> Redirigiendo en <span id="t">30</span>s...</div>
</div>



<script src="../assets/js/procesar_pedido.js"></script>
</body>
</html>

<!-- Email enviado server-side via PHPMailer + Gmail SMTP -->