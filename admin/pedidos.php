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

// Paginación
$items_por_pagina = 10;
$pagina_actual = max(1, intval($_GET['pag'] ?? 1));
$offset = ($pagina_actual - 1) * $items_por_pagina;

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
    $stmtPedidos = mysqli_prepare($conexion, $sqlBase . " WHERE p.estado = ? ORDER BY p.fecha DESC LIMIT ? OFFSET ?");
    if ($stmtPedidos) {
        mysqli_stmt_bind_param($stmtPedidos, 'sii', $filtro_estado, $items_por_pagina, $offset);
        mysqli_stmt_execute($stmtPedidos);
        $pedidos = mysqli_stmt_get_result($stmtPedidos);
        mysqli_stmt_close($stmtPedidos);
    } else {
        $pedidos = mysqli_query($conexion, $sqlBase . " ORDER BY p.fecha DESC LIMIT $items_por_pagina OFFSET $offset");
    }
    
    // Contar total de registros para paginación
    $countStmt = mysqli_prepare($conexion, "SELECT COUNT(*) as total FROM pedidos WHERE estado = ?");
    if ($countStmt) {
        mysqli_stmt_bind_param($countStmt, 's', $filtro_estado);
        mysqli_stmt_execute($countStmt);
        $countResult = mysqli_stmt_get_result($countStmt);
        $countRow = mysqli_fetch_assoc($countResult);
        $total_pedidos = $countRow['total'];
        mysqli_stmt_close($countStmt);
    } else {
        $countResult = mysqli_query($conexion, "SELECT COUNT(*) as total FROM pedidos WHERE estado = '$filtro_estado'");
        $countRow = mysqli_fetch_assoc($countResult);
        $total_pedidos = $countRow['total'];
    }
} else {
    $pedidos = mysqli_query($conexion, $sqlBase . " ORDER BY p.fecha DESC LIMIT $items_por_pagina OFFSET $offset");
    
    // Contar total de registros para paginación
    $countResult = mysqli_query($conexion, "SELECT COUNT(*) as total FROM pedidos");
    $countRow = mysqli_fetch_assoc($countResult);
    $total_pedidos = $countRow['total'];
}

$total_paginas = ceil($total_pedidos / $items_por_pagina);

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
<link rel="stylesheet" href="../assets/css/admin_pedidos.css">
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
    <div class="card-dark pedidos-detail-card">
        <div class="pedidos-header">
            <h4 class="pedidos-title"><i class="fas fa-receipt"></i> Pedido #<?php echo $detalle_pedido['id_pedido']; ?></h4>
            <a href="pedidos.php" class="pedidos-close">✕</a>
        </div>
        <div class="row g-3 mb-16">
            <div class="col-md-4">
                <div class="detail-label">CLIENTE</div>
                <div class="detail-val-700"><?php echo htmlspecialchars($detalle_pedido['cliente'] ?? 'Invitado'); ?></div>
                <div class="detail-sub"><?php echo htmlspecialchars($detalle_pedido['email'] ?? '-'); ?></div>
                <div class="detail-sub"><?php echo htmlspecialchars($detalle_pedido['telefono'] ?? '-'); ?></div>
            </div>
            <div class="col-md-4">
                <div class="detail-label">DIRECCIÓN</div>
                <div class="detail-val-600"><?php echo htmlspecialchars($detalle_pedido['direccion'] ?? 'No especificada'); ?></div>
                <div class="detail-sub"><?php echo htmlspecialchars($detalle_pedido['referencia'] ?? ''); ?></div>
            </div>
            <div class="col-md-4">
                <div class="detail-label">PAGO</div>
                <div class="detail-val-600"><?php echo ucfirst($detalle_pedido['metodo'] ?? '-'); ?></div>
                <div class="detail-sub"><?php echo date('d/m/Y H:i',strtotime($detalle_pedido['fecha'])); ?></div>
            </div>
        </div>
        <table class="dark-table mb-16">
            <thead><tr><th>Imagen</th><th>Producto</th><th>Cantidad</th><th>Subtotal</th></tr></thead>
            <tbody>
                <?php foreach($detalle_items as $item): ?>
                <tr>
                    <td><img src="<?php echo htmlspecialchars($item['imagen']); ?>" class="item-img" onerror="this.style.display='none'"></td>
                    <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                    <td><?php echo $item['cantidad']; ?></td>
                    <td class="detail-val-700">S/ <?php echo number_format($item['subtotal'],2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="total-right">
            Total: S/ <?php echo number_format($detalle_pedido['total'],2); ?>
        </div>
        <hr class="hr-dark">
        <form method="POST" class="form-dark form-flex-end">
            <input type="hidden" name="id_pedido" value="<?php echo $detalle_pedido['id_pedido']; ?>">
            <div class="form-group-dark m-0 min-w-220">
                <label>Cambiar estado</label>
                <select name="estado" class="w-100">
                    <?php foreach(['pendiente','preparando','ir a recoger','en camino','entregado','cancelado'] as $e): ?>
                    <option value="<?php echo $e; ?>" <?php if($detalle_pedido['estado']==$e) echo 'selected'; ?>><?php echo ucfirst($e); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group-dark m-0 min-w-240">
                <label>Asignar repartidor</label>
                <select name="id_repartidor" class="w-100">
                    <option value="">Sin asignar</option>
                    <?php 
                    $repartidores_detalle = mysqli_query($conexion, "SELECT id_usuario, nombre, email FROM usuarios WHERE rol='delivery' ORDER BY nombre");
                    while($rep = mysqli_fetch_assoc($repartidores_detalle)): 
                    ?>
                        <option value="<?php echo $rep['id_usuario']; ?>" <?php if($detalle_pedido['id_repartidor'] == $rep['id_usuario']) echo 'selected'; ?>><?php echo htmlspecialchars($rep['nombre'] . ' (' . $rep['email'] . ')'); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" name="cambiar_estado" class="btn-red"><i class="fas fa-save"></i> Actualizar</button>
        </form>
        <!-- Botones: descargar y reenviar voucher -->
        <div class="mt-14 d-flex gap-12 flex-wrap">
            <a href="generar_voucher_admin.php?id_pedido=<?php echo $detalle_pedido['id_pedido']; ?>" download class="btn-pdf">
                <i class="fas fa-file-pdf"></i> Descargar Voucher PDF
            </a>
            <a href="reenviar_voucher.php?id_pedido=<?php echo $detalle_pedido['id_pedido']; ?>" class="btn-email">
                <i class="fas fa-envelope"></i> Reenviar Voucher por Email
            </a>
        </div>
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
                    <td class="detail-val-700">S/ <?php echo number_format($p['total'],2); ?></td>
                    <td class="detail-sub"><?php echo ucfirst($p['metodo'] ?? '-'); ?></td>
                    <td><span class="badge-estado badge-<?php echo str_replace(' ','_',$p['estado']); ?>"><?php echo ucfirst($p['estado']); ?></span></td>
                    <td class="detail-sub"><?php echo date('d/m/Y H:i',strtotime($p['fecha'])); ?></td>
                    <td>
                        <div class="action-btns">
                            <a href="pedidos.php?id=<?php echo $p['id_pedido']; ?>" class="btn-edit-dark"><i class="fas fa-eye"></i> Ver</a>
                            <a href="generar_voucher_admin.php?id_pedido=<?php echo $p['id_pedido']; ?>" download title="Descargar Voucher" class="btn-pdf-sm">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                            <a href="reenviar_voucher.php?id_pedido=<?php echo $p['id_pedido']; ?>" title="Reenviar Voucher por Email" class="btn-email-sm">
                                <i class="fas fa-envelope"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <!-- PAGINACIÓN -->
        <?php if($total_paginas > 1): ?>
        <div class="pagination-wrapper">
            <!-- Anterior -->
            <?php if($pagina_actual > 1): ?>
                <a href="pedidos.php?pag=<?php echo $pagina_actual-1; ?><?php if($filtro_estado) echo '&estado='.urlencode($filtro_estado); ?>" class="pagination-btn">
                    <i class="fas fa-chevron-left"></i> Anterior
                </a>
            <?php else: ?>
                <span class="pagination-disabled">
                    <i class="fas fa-chevron-left"></i> Anterior
                </span>
            <?php endif; ?>
            
            <!-- Números de página -->
            <div class="page-numbers-wrapper">
                <?php 
                $inicio = max(1, $pagina_actual - 2);
                $fin = min($total_paginas, $pagina_actual + 2);
                
                if($inicio > 1): ?>
                    <a href="pedidos.php?pag=1<?php if($filtro_estado) echo '&estado='.urlencode($filtro_estado); ?>" class="page-number">1</a>
                    <?php if($inicio > 2): ?>
                        <span class="page-dots">...</span>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php for($p = $inicio; $p <= $fin; $p++): ?>
                    <?php if($p == $pagina_actual): ?>
                        <span class="page-number active"><?php echo $p; ?></span>
                    <?php else: ?>
                        <a href="pedidos.php?pag=<?php echo $p; ?><?php if($filtro_estado) echo '&estado='.urlencode($filtro_estado); ?>" class="page-number"><?php echo $p; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if($fin < $total_paginas): ?>
                    <?php if($fin < $total_paginas - 1): ?>
                        <span class="page-dots">...</span>
                    <?php endif; ?>
                    <a href="pedidos.php?pag=<?php echo $total_paginas; ?><?php if($filtro_estado) echo '&estado='.urlencode($filtro_estado); ?>" class="page-number"><?php echo $total_paginas; ?></a>
                <?php endif; ?>
            </div>
            
            <!-- Siguiente -->
            <?php if($pagina_actual < $total_paginas): ?>
                <a href="pedidos.php?pag=<?php echo $pagina_actual+1; ?><?php if($filtro_estado) echo '&estado='.urlencode($filtro_estado); ?>" class="pagination-btn">
                    Siguiente <i class="fas fa-chevron-right"></i>
                </a>
            <?php else: ?>
                <span class="pagination-disabled">
                    Siguiente <i class="fas fa-chevron-right"></i>
                </span>
            <?php endif; ?>
            
            <!-- Info -->
            <div class="page-info-text">
                Página <?php echo $pagina_actual; ?> de <?php echo $total_paginas; ?> (<?php echo $total_pedidos; ?> pedidos)
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="../assets/js/admin_pedidos.js"></script>
</body>
</html>
