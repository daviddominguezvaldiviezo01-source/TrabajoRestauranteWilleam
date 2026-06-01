<?php
session_start();
require_once __DIR__ . '/../conexion.php';
/** @var mysqli $conexion */
if (!isset($conexion) || !$conexion) {
    die('Error de conexión: no se pudo establecer la conexión a la base de datos.');
}
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') { header("Location:../cliente/login.php"); exit(); }

$msg = '';
$has_repartidor = mysqli_num_rows(mysqli_query($conexion,"SHOW COLUMNS FROM pedidos LIKE 'id_repartidor'")) > 0;

if (isset($_POST['cambiar_estado'])) {
    $id_pedido = intval($_POST['id_pedido']);
    $estado = $_POST['estado'];
    $id_repartidor = isset($_POST['id_repartidor']) && is_numeric($_POST['id_repartidor']) ? intval($_POST['id_repartidor']) : null;
    if (in_array($estado,['pendiente','preparando','ir a recoger','en camino','entregado','cancelado'])) {
        if ($has_repartidor) {
            if ($id_repartidor) {
                $stmt = mysqli_prepare($conexion,"UPDATE pedidos SET estado=?, id_repartidor=? WHERE id_pedido=?");
                mysqli_stmt_bind_param($stmt,"sii",$estado,$id_repartidor,$id_pedido);
            } else {
                $stmt = mysqli_prepare($conexion,"UPDATE pedidos SET estado=?, id_repartidor=NULL WHERE id_pedido=?");
                mysqli_stmt_bind_param($stmt,"si",$estado,$id_pedido);
            }
        } else {
            $stmt = mysqli_prepare($conexion,"UPDATE pedidos SET estado=? WHERE id_pedido=?");
            mysqli_stmt_bind_param($stmt,"si",$estado,$id_pedido);
        }
        mysqli_stmt_execute($stmt);
        $msg = "Estado actualizado a: ".ucfirst($estado);
    }
}

$filtro_estado = $_GET['estado'] ?? '';
$where = $filtro_estado ? "WHERE p.estado = '".mysqli_real_escape_string($conexion,$filtro_estado)."'" : '';

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$itemsPerPage = 10;
$offset = ($page - 1) * $itemsPerPage;

$totalPedidosQuery = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM pedidos p $where");
$totalPedidos = mysqli_fetch_assoc($totalPedidosQuery)['total'];
$totalPages = max(1, ceil($totalPedidos / $itemsPerPage));
if ($page > $totalPages) { $page = $totalPages; $offset = ($page - 1) * $itemsPerPage; }

function buildPaginationItems($current, $total) {
    $pages = [];
    if ($total <= 7) {
        return range(1, $total);
    }
    $pages[] = 1;
    $pages[] = 2;
    for ($i = $current - 1; $i <= $current + 1; $i++) {
        if ($i > 2 && $i < $total - 1) {
            $pages[] = $i;
        }
    }
    $pages[] = $total - 1;
    $pages[] = $total;
    $pages = array_values(array_unique($pages));
    sort($pages);

    $items = [];
    $last = 0;
    foreach ($pages as $pageNumber) {
        if ($last && $pageNumber > $last + 1) {
            $items[] = '...';
        }
        $items[] = $pageNumber;
        $last = $pageNumber;
    }
    return $items;
}

$pageItems = buildPaginationItems($page, $totalPages);

$repartidor_select = $has_repartidor ? ", u2.nombre AS repartidor_nombre" : "";
$repartidor_join = $has_repartidor ? "LEFT JOIN usuarios u2 ON p.id_repartidor=u2.id_usuario" : "";

$pedidos = mysqli_query($conexion,
    "SELECT p.*, u.nombre AS cliente, u.email, u.telefono,
            d.direccion, d.referencia, pg.metodo, pg.estado AS estado_pago$repartidor_select
     FROM pedidos p
     LEFT JOIN usuarios u ON p.id_usuario=u.id_usuario
     LEFT JOIN direcciones d ON p.id_direccion=d.id_direccion
     LEFT JOIN pagos pg ON p.id_pedido=pg.id_pedido
     $repartidor_join
     $where ORDER BY p.fecha DESC LIMIT $offset,$itemsPerPage");

$repartidores = $has_repartidor ? mysqli_query($conexion, "SELECT id_usuario, nombre FROM usuarios WHERE rol='delivery' ORDER BY nombre") : null;

$detalle_pedido = null; $detalle_items = [];
if (isset($_GET['id'])) {
    $id_ver = intval($_GET['id']);
    $sp = mysqli_prepare($conexion,
        "SELECT p.*, u.nombre AS cliente, u.email, u.telefono, d.direccion, d.referencia, pg.metodo, pg.estado AS estado_pago
         FROM pedidos p LEFT JOIN usuarios u ON p.id_usuario=u.id_usuario
         LEFT JOIN direcciones d ON p.id_direccion=d.id_direccion
         LEFT JOIN pagos pg ON p.id_pedido=pg.id_pedido WHERE p.id_pedido=?");
    mysqli_stmt_bind_param($sp,"i",$id_ver); mysqli_stmt_execute($sp);
    $detalle_pedido = mysqli_fetch_assoc(mysqli_stmt_get_result($sp));
    $sd = mysqli_prepare($conexion,
        "SELECT dp.*, pr.nombre, pr.imagen FROM detalle_pedido dp LEFT JOIN productos pr ON dp.id_producto=pr.id_producto WHERE dp.id_pedido=?");
    mysqli_stmt_bind_param($sd,"i",$id_ver); mysqli_stmt_execute($sd);
    $detalle_items = mysqli_fetch_all(mysqli_stmt_get_result($sd), MYSQLI_ASSOC);
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
<style>
.pagination-custom{display:flex;justify-content:center;align-items:center;gap:0.35rem;flex-wrap:wrap;margin-top:20px;}
.pagination-custom .page-item{list-style:none;}
.pagination-custom .page-link{display:inline-flex;align-items:center;justify-content:center;min-width:44px;padding:0.55rem 0.9rem;border-radius:999px;border:1px solid #2a2a2a;background:#171717;color:#ddd;text-decoration:none;transition:all .2s ease;}
.pagination-custom .page-link:hover{background:rgba(200,16,46,.14);color:#fff;border-color:#c8102e;}
.pagination-custom .page-item.active .page-link{background:#c8102e;color:#fff;border-color:#c8102e;box-shadow:0 0 0 3px rgba(200,16,46,.12);}
.pagination-custom .page-item.disabled .page-link{background:#111;color:rgba(255,255,255,.35);border-color:#2a2a2a;cursor:not-allowed;pointer-events:none;}
.pagination-info{color:rgba(255,255,255,.65);font-size:0.95rem;}
.pagination-row{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-top:18px;}
</style>
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
            <div class="form-group-dark" style="margin:0;">
                <label>Cambiar estado</label>
                <select name="estado" style="width:200px;">
                    <?php foreach(['pendiente','preparando','ir a recoger','en camino','entregado','cancelado'] as $e): ?>
                    <option value="<?php echo $e; ?>" <?php if($detalle_pedido['estado']==$e) echo 'selected'; ?>><?php echo ucfirst($e); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if($has_repartidor): ?>
            <div class="form-group-dark" style="margin:0;">
                <label>Repartidor</label>
                <select name="id_repartidor" style="width:220px;">
                    <option value="">Sin asignar</option>
                    <?php while($r = mysqli_fetch_assoc($repartidores)): ?>
                    <option value="<?php echo $r['id_usuario']; ?>" <?php if($detalle_pedido['id_repartidor']==$r['id_usuario']) echo 'selected'; ?>><?php echo htmlspecialchars($r['nombre']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <?php endif; ?>
            <button type="submit" name="cambiar_estado" class="btn-red"><i class="fas fa-save"></i> Actualizar</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- TABLA -->
    <div class="card-dark">
        <h4><i class="fas fa-list"></i> Lista de Pedidos</h4>
        <table class="dark-table">
            <thead>
                <tr><th>#</th><th>Cliente</th><th>Total</th><th>Método</th><?php if($has_repartidor): ?><th>Repartidor</th><?php endif; ?><th>Estado</th><th>Fecha</th><th></th></tr>
            </thead>
            <tbody>
                <?php while($p=mysqli_fetch_assoc($pedidos)): ?>
                <tr>
                    <td><strong>#<?php echo $p['id_pedido']; ?></strong></td>
                    <td><?php echo htmlspecialchars($p['cliente'] ?? 'Invitado'); ?></td>
                    <td style="font-weight:700;">S/ <?php echo number_format($p['total'],2); ?></td>
                    <td style="color:rgba(255,255,255,.5);font-size:13px;"><?php echo ucfirst($p['metodo'] ?? '-'); ?></td>
                    <?php if($has_repartidor): ?>
                    <td style="color:rgba(255,255,255,.5);font-size:13px;"><?php echo htmlspecialchars($p['repartidor_nombre'] ?? '---'); ?></td>
                    <?php endif; ?>
                    <td><span class="badge-estado badge-<?php echo str_replace(' ','_',$p['estado']); ?>"><?php echo ucfirst($p['estado']); ?></span></td>
                    <td style="color:rgba(255,255,255,.4);font-size:13px;"><?php echo date('d/m/Y H:i',strtotime($p['fecha'])); ?></td>
                    <td><a href="pedidos.php?id=<?php echo $p['id_pedido']; ?>" class="btn-edit-dark"><i class="fas fa-eye"></i> Ver</a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php if ($totalPages > 1): ?>
        <div class="pagination-row">
            <div class="pagination-info">
                Mostrando <?php echo min($offset + 1, $totalPedidos); ?> - <?php echo min($offset + $itemsPerPage, $totalPedidos); ?> de <?php echo $totalPedidos; ?> pedidos
            </div>
            <nav aria-label="Paginación de pedidos">
                <ul class="pagination-custom">
                    <?php
                    $queryPrefix = 'pedidos.php?';
                    if ($filtro_estado) { $queryPrefix .= 'estado=' . urlencode($filtro_estado) . '&'; }
                    ?>
                    <li class="page-item <?php echo ($page<=1)?'disabled':''; ?>">
                        <a class="page-link" href="<?php echo $queryPrefix; ?>page=<?php echo max(1,$page-1); ?>">Anterior</a>
                    </li>
                    <?php foreach ($pageItems as $item): ?>
                        <?php if ($item === '...'): ?>
                            <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                        <?php else: ?>
                            <li class="page-item <?php echo ($page==$item)?'active':''; ?>">
                                <a class="page-link" href="<?php echo $queryPrefix; ?>page=<?php echo $item; ?>"><?php echo $item; ?></a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <li class="page-item <?php echo ($page>=$totalPages)?'disabled':''; ?>">
                        <a class="page-link" href="<?php echo $queryPrefix; ?>page=<?php echo min($totalPages,$page+1); ?>">Siguiente</a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
