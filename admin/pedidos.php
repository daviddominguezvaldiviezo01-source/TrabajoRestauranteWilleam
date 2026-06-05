<?php
session_start();
require_once __DIR__ . '/../conexion.php';
/** @var mysqli $conexion */
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') { header("Location:../cliente/login.php"); exit(); }

$msg = '';
$repartidores = mysqli_query($conexion, "SELECT id_usuario, nombre, email FROM usuarios WHERE rol='delivery' ORDER BY nombre");

// Comprobar si la tabla `pedidos` tiene columnas para datos de cliente (invitados)
$has_guest_columns = false;
$resCols = mysqli_query($conexion, "SHOW COLUMNS FROM pedidos LIKE 'nombre_cliente'");
if ($resCols && mysqli_num_rows($resCols) > 0) {
    $has_guest_columns = true;
}

$filtro_estado = $_GET['estado'] ?? '';
$estados_validos = ['pendiente','preparando','ir a recoger','en camino','entregado','cancelado'];
if (!in_array($filtro_estado, $estados_validos, true)) {
    $filtro_estado = '';
}

if (isset($_POST['cambiar_estado'])) {
    $id_pedido = intval($_POST['id_pedido'] ?? 0);
    $estado = trim($_POST['estado'] ?? '');
    $id_repartidor = intval($_POST['id_repartidor'] ?? 0);
    $msg = '';
    $estados_validos = ['pendiente','preparando','ir a recoger','en camino','entregado','cancelado'];

    if ($id_pedido > 0 && in_array($estado, $estados_validos, true)) {
        if ($id_repartidor > 0) {
            $stmt = mysqli_prepare($conexion, "UPDATE pedidos SET estado=?, id_repartidor=? WHERE id_pedido=?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sii", $estado, $id_repartidor, $id_pedido);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $msg = "Estado actualizado a: " . ucfirst($estado);
            }
        } else {
            $stmt = mysqli_prepare($conexion, "UPDATE pedidos SET estado=? WHERE id_pedido=?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "si", $estado, $id_pedido);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $msg = "Estado actualizado a: " . ucfirst($estado);
            }
        }
    }

    if ($msg === '') {
        $msg = 'No hubo cambios en el pedido.';
    }

    $_SESSION['admin_pedidos_message'] = $msg;
    $redirectUrl = 'pedidos.php';
    $redirectParams = [];
    if ($filtro_estado !== '') {
        $redirectParams['estado'] = $filtro_estado;
    }
    if (isset($_GET['id']) && intval($_GET['id']) > 0) {
        $redirectParams['id'] = intval($_GET['id']);
    } elseif ($id_pedido > 0) {
        $redirectParams['id'] = $id_pedido;
    }
    if (!empty($redirectParams)) {
        $redirectUrl .= '?' . http_build_query($redirectParams);
    }
    header('Location: ' . $redirectUrl);
    exit;
}

if (isset($_SESSION['admin_pedidos_message'])) {
    $msg = $_SESSION['admin_pedidos_message'];
    unset($_SESSION['admin_pedidos_message']);
}

if ($has_guest_columns) {
    $sqlBase = "SELECT p.*, COALESCE(u.nombre, p.nombre_cliente) AS cliente, COALESCE(u.email, p.email_cliente) AS email, COALESCE(u.telefono, p.telefono_cliente) AS telefono,\n"
             . "            ur.nombre AS repartidor, d.direccion, d.referencia, pg.metodo, pg.estado AS estado_pago\n"
             . "     FROM pedidos p\n"
             . "     LEFT JOIN usuarios u ON p.id_usuario=u.id_usuario\n"
             . "     LEFT JOIN usuarios ur ON p.id_repartidor=ur.id_usuario\n"
             . "     LEFT JOIN direcciones d ON p.id_direccion=d.id_direccion\n"
             . "     LEFT JOIN pagos pg ON p.id_pedido=pg.id_pedido";
} else {
    $sqlBase = "SELECT p.*, u.nombre AS cliente, u.email, u.telefono,\n"
             . "            ur.nombre AS repartidor, d.direccion, d.referencia, pg.metodo, pg.estado AS estado_pago\n"
             . "     FROM pedidos p\n"
             . "     LEFT JOIN usuarios u ON p.id_usuario=u.id_usuario\n"
             . "     LEFT JOIN usuarios ur ON p.id_repartidor=ur.id_usuario\n"
             . "     LEFT JOIN direcciones d ON p.id_direccion=d.id_direccion\n"
             . "     LEFT JOIN pagos pg ON p.id_pedido=pg.id_pedido";
}

if ($filtro_estado !== '') {
    $stmtPedidos = mysqli_prepare($conexion, $sqlBase . " WHERE p.estado = ? ORDER BY p.fecha DESC");
    if ($stmtPedidos) {
        mysqli_stmt_bind_param($stmtPedidos, 's', $filtro_estado);
        mysqli_stmt_execute($stmtPedidos);
        $pedidos = mysqli_stmt_get_result($stmtPedidos);
        mysqli_stmt_close($stmtPedidos);
    } else {
        $pedidos = mysqli_query($conexion, $sqlBase . " ORDER BY p.fecha DESC");
    }
} else {
    $pedidos = mysqli_query($conexion, $sqlBase . " ORDER BY p.fecha DESC");
}

$detalle_pedido = null; $detalle_items = [];
if (isset($_GET['id'])) {
    $id_ver = intval($_GET['id']);
    if ($id_ver > 0) {
        if ($has_guest_columns) {
            $sp_sql = "SELECT p.*, COALESCE(u.nombre, p.nombre_cliente) AS cliente, COALESCE(u.email, p.email_cliente) AS email, COALESCE(u.telefono, p.telefono_cliente) AS telefono, ur.nombre AS repartidor, d.direccion, d.referencia, pg.metodo, pg.estado AS estado_pago\n"
                    . "             FROM pedidos p LEFT JOIN usuarios u ON p.id_usuario=u.id_usuario\n"
                    . "             LEFT JOIN usuarios ur ON p.id_repartidor=ur.id_usuario\n"
                    . "             LEFT JOIN direcciones d ON p.id_direccion=d.id_direccion\n"
                    . "             LEFT JOIN pagos pg ON p.id_pedido=pg.id_pedido WHERE p.id_pedido=?";
        } else {
            $sp_sql = "SELECT p.*, u.nombre AS cliente, u.email, u.telefono, ur.nombre AS repartidor, d.direccion, d.referencia, pg.metodo, pg.estado AS estado_pago\n"
                    . "             FROM pedidos p LEFT JOIN usuarios u ON p.id_usuario=u.id_usuario\n"
                    . "             LEFT JOIN usuarios ur ON p.id_repartidor=ur.id_usuario\n"
                    . "             LEFT JOIN direcciones d ON p.id_direccion=d.id_direccion\n"
                    . "             LEFT JOIN pagos pg ON p.id_pedido=pg.id_pedido WHERE p.id_pedido=?";
        }
        $sp = mysqli_prepare($conexion, $sp_sql);
        if ($sp) {
            mysqli_stmt_bind_param($sp, "i", $id_ver);
            mysqli_stmt_execute($sp);
            $result = mysqli_stmt_get_result($sp);
            if ($result) {
                $detalle_pedido = mysqli_fetch_assoc($result);
            }
            mysqli_stmt_close($sp);
        }

        if ($detalle_pedido) {
            $sd = mysqli_prepare($conexion,
                "SELECT dp.*, pr.nombre, pr.imagen FROM detalle_pedido dp LEFT JOIN productos pr ON dp.id_producto=pr.id_producto WHERE dp.id_pedido=?");
            if ($sd) {
                mysqli_stmt_bind_param($sd, "i", $id_ver);
                mysqli_stmt_execute($sd);
                $resultItems = mysqli_stmt_get_result($sd);
                if ($resultItems) {
                    $detalle_items = mysqli_fetch_all($resultItems, MYSQLI_ASSOC);
                }
                mysqli_stmt_close($sd);
            }
        }
    }
}

$active_page = 'pedidos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pedidos - Brisamar Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
<?php include('_admin_layout.php'); ?>

<div class="main">
    <div class="page-header">
        <h1>Gestión de Pedidos</h1>
        <p>Administra y actualiza el estado de los pedidos</p>
    </div>

    <?php if($msg): ?>
    <div class="alert-dark-success"><i class="fas fa-check-circle"></i> <?php echo $msg; ?></div>
    <?php endif; ?>

    <!-- FILTROS -->
    <div class="filter-tabs">
        <a href="pedidos.php" class="filter-tab <?php echo !$filtro_estado?'active':''; ?>">Todos</a>
        <?php foreach(['pendiente','preparando','ir a recoger','en camino','entregado','cancelado'] as $e): ?>
        <a href="pedidos.php?estado=<?php echo urlencode($e); ?>"
           class="filter-tab <?php echo $filtro_estado==$e?'active':''; ?>"><?php echo ucfirst($e); ?></a>
        <?php endforeach; ?>
    </div>

    <!-- DETALLE -->
    <?php if($detalle_pedido): ?>
    <div class="card-dark" style="border-left:3px solid #c8102e;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h4 style="margin:0;"><i class="fas fa-receipt"></i> Pedido #<?php echo $detalle_pedido['id_pedido']; ?></h4>
            <a href="pedidos.php" style="color:rgba(255,255,255,.4);text-decoration:none;font-size:18px;">✕</a>
        </div>
        <div class="row g-3" style="margin-bottom:16px;">
            <div class="col-md-4">
                <div style="font-size:12px;color:rgba(255,255,255,.4);margin-bottom:4px;">CLIENTE</div>
                <div style="font-weight:700;"><?php echo htmlspecialchars($detalle_pedido['cliente'] ?? 'Invitado'); ?></div>
                <div style="font-size:13px;color:rgba(255,255,255,.4);"><?php echo htmlspecialchars($detalle_pedido['email'] ?? '-'); ?></div>
                <div style="font-size:13px;color:rgba(255,255,255,.4);"><?php echo htmlspecialchars($detalle_pedido['telefono'] ?? '-'); ?></div>
            </div>
            <div class="col-md-4">
                <div style="font-size:12px;color:rgba(255,255,255,.4);margin-bottom:4px;">DIRECCIÓN</div>
                <div style="font-weight:600;"><?php echo htmlspecialchars($detalle_pedido['direccion'] ?? 'No especificada'); ?></div>
                <div style="font-size:13px;color:rgba(255,255,255,.4);"><?php echo htmlspecialchars($detalle_pedido['referencia'] ?? ''); ?></div>
            </div>
            <div class="col-md-4">
                <div style="font-size:12px;color:rgba(255,255,255,.4);margin-bottom:4px;">PAGO</div>
                <div style="font-weight:600;"><?php echo ucfirst($detalle_pedido['metodo'] ?? '-'); ?></div>
                <div style="font-size:13px;color:rgba(255,255,255,.4);"><?php echo date('d/m/Y H:i',strtotime($detalle_pedido['fecha'])); ?></div>
            </div>
        </div>
        <table class="dark-table" style="margin-bottom:16px;">
            <thead><tr><th>Imagen</th><th>Producto</th><th>Cantidad</th><th>Subtotal</th></tr></thead>
            <tbody>
                <?php foreach($detalle_items as $item): ?>
                <tr>
                    <td><img src="<?php echo htmlspecialchars($item['imagen']); ?>" style="width:46px;height:46px;object-fit:cover;border-radius:8px;background:#2a2a2a;" onerror="this.style.display='none'"></td>
                    <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                    <td><?php echo $item['cantidad']; ?></td>
                    <td style="font-weight:700;">S/ <?php echo number_format($item['subtotal'],2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div style="text-align:right;font-size:1.2rem;font-weight:900;margin-bottom:16px;">
            Total: S/ <?php echo number_format($detalle_pedido['total'],2); ?>
        </div>
        <hr style="border-color:#2a2a2a;margin-bottom:16px;">
        <form method="POST" class="form-dark" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            <input type="hidden" name="id_pedido" value="<?php echo $detalle_pedido['id_pedido']; ?>">
            <div class="form-group-dark" style="margin:0;min-width:220px;">
                <label>Cambiar estado</label>
                <select name="estado" style="width:100%;">
                    <?php foreach(['pendiente','preparando','ir a recoger','en camino','entregado','cancelado'] as $e): ?>
                    <option value="<?php echo $e; ?>" <?php if($detalle_pedido['estado']==$e) echo 'selected'; ?>><?php echo ucfirst($e); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group-dark" style="margin:0;min-width:240px;">
                <label>Asignar repartidor</label>
                <select name="id_repartidor" style="width:100%;">
                    <option value="">Sin asignar</option>
                    <?php while($rep = mysqli_fetch_assoc($repartidores)): ?>
                        <option value="<?php echo $rep['id_usuario']; ?>" <?php if($detalle_pedido['id_repartidor'] == $rep['id_usuario']) echo 'selected'; ?>><?php echo htmlspecialchars($rep['nombre'] . ' (' . $rep['email'] . ')'); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" name="cambiar_estado" class="btn-red"><i class="fas fa-save"></i> Actualizar</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- TABLA -->
    <div class="card-dark">
        <h4><i class="fas fa-list"></i> Lista de Pedidos</h4>
        <table class="dark-table">
            <thead>
                <tr><th>#</th><th>Cliente</th><th>Repartidor</th><th>Total</th><th>Método</th><th>Estado</th><th>Fecha</th><th></th></tr>
            </thead>
            <tbody>
                <?php while($p=mysqli_fetch_assoc($pedidos)): ?>
                <tr>
                    <td><strong>#<?php echo $p['id_pedido']; ?></strong></td>
                    <td><?php echo htmlspecialchars($p['cliente'] ?? 'Invitado'); ?></td>
                    <td><?php echo htmlspecialchars($p['repartidor'] ?? '-'); ?></td>
                    <td style="font-weight:700;">S/ <?php echo number_format($p['total'],2); ?></td>
                    <td style="color:rgba(255,255,255,.5);font-size:13px;"><?php echo ucfirst($p['metodo'] ?? '-'); ?></td>
                    <td><span class="badge-estado badge-<?php echo str_replace(' ','_',$p['estado']); ?>"><?php echo ucfirst($p['estado']); ?></span></td>
                    <td style="color:rgba(255,255,255,.4);font-size:13px;"><?php echo date('d/m/Y H:i',strtotime($p['fecha'])); ?></td>
                    <td><a href="pedidos.php?id=<?php echo $p['id_pedido']; ?>" class="btn-edit-dark"><i class="fas fa-eye"></i> Ver</a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const adminPedidosRefreshInterval = 15000; // 15 segundos
    let adminPedidosRefreshTimer = null;

    function scheduleAdminPedidosRefresh() {
        if (document.hidden) {
            return;
        }
        adminPedidosRefreshTimer = window.setTimeout(() => {
            window.location.reload();
        }, adminPedidosRefreshInterval);
    }

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            if (adminPedidosRefreshTimer) {
                clearTimeout(adminPedidosRefreshTimer);
            }
            scheduleAdminPedidosRefresh();
        }
    });

    scheduleAdminPedidosRefresh();
</script>
</body>
</html>
