<?php
session_start();
require_once dirname(__FILE__) . '/../conexion.php';
/** @var mysqli $conexion */
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') { header("Location:../cliente/login.php"); exit(); }

$totalVentas     = mysqli_fetch_assoc(mysqli_query($conexion,"SELECT COALESCE(SUM(total),0) AS v FROM pedidos WHERE estado != 'cancelado'"))['v'];
$totalPedidos    = mysqli_fetch_assoc(mysqli_query($conexion,"SELECT COUNT(*) AS c FROM pedidos"))['c'];
$totalProductos  = mysqli_fetch_assoc(mysqli_query($conexion,"SELECT COUNT(*) AS c FROM productos"))['c'];
$totalClientes   = mysqli_fetch_assoc(mysqli_query($conexion,"SELECT COUNT(*) AS c FROM usuarios WHERE rol='cliente'"))['c'];
$pedidosPendientes = mysqli_fetch_assoc(mysqli_query($conexion,"SELECT COUNT(*) AS c FROM pedidos WHERE estado='pendiente'"))['c'];

$resUltimos = mysqli_query($conexion,"SELECT vv.*, vv.cliente FROM vista_ventas vv ORDER BY vv.fecha DESC LIMIT 8");

$resMeses = mysqli_query($conexion,"SELECT DATE_FORMAT(fecha,'%b') AS mes, SUM(total) AS total FROM pedidos WHERE fecha >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY MONTH(fecha), DATE_FORMAT(fecha,'%b') ORDER BY MONTH(fecha)");
$meses=[]; $ventas_mes=[];
while($row=mysqli_fetch_assoc($resMeses)){ $meses[]=$row['mes']; $ventas_mes[]=floatval($row['total']); }
if(empty($meses)) { $meses = ['Sin datos']; $ventas_mes = [0]; }

$active_page = 'dashboard';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - Brisamar Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="../assets/css/admin_dashboard.css">
</head>
<body>
<?php include('_admin_layout.php'); ?>

<div class="main">
    <div class="page-header">
        <h1>Dashboard</h1>
        <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?> · <?php echo date('d/m/Y'); ?></p>
    </div>

    <?php if($pedidosPendientes > 0): ?>
    <div class="alert-dashboard">
        <i class="fas fa-bell text-danger-custom"></i>
        <span>Tienes <strong><?php echo $pedidosPendientes; ?></strong> pedido(s) pendiente(s) por revisar.</span>
        <a href="pedidos.php" class="link-danger-custom">Ver pedidos &rarr;</a>
    </div>
    <?php endif; ?>

    <!-- STATS -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon stat-icon-red"><i class="fas fa-sack-dollar"></i></div>
            <div class="stat-label">Total Ventas</div>
            <div class="stat-value">S/ <?php echo number_format($totalVentas,2); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-green"><i class="fas fa-receipt"></i></div>
            <div class="stat-label">Total Pedidos</div>
            <div class="stat-value"><?php echo $totalPedidos; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-blue"><i class="fas fa-box"></i></div>
            <div class="stat-label">Productos</div>
            <div class="stat-value"><?php echo $totalProductos; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-purple"><i class="fas fa-users"></i></div>
            <div class="stat-label">Clientes</div>
            <div class="stat-value"><?php echo $totalClientes; ?></div>
        </div>
    </div>

    <!-- GRÁFICOS -->
    <div class="charts-row">
        <div class="card-dark">
            <h4><i class="fas fa-chart-line"></i> Ventas últimos 6 meses</h4>
            <canvas id="chartVentas" height="100"></canvas>
        </div>
        <div class="card-dark">
            <h4><i class="fas fa-chart-pie"></i> Estado de Pedidos</h4>
            <canvas id="chartPie" height="200"></canvas>
        </div>
    </div>

    <!-- ÚLTIMOS PEDIDOS -->
    <div class="card-dark">
        <h4><i class="fas fa-clock"></i> Últimos Pedidos</h4>
        <table class="dark-table">
            <thead>
                <tr><th>#</th><th>Cliente</th><th>Total</th><th>Estado</th><th>Fecha</th><th></th></tr>
            </thead>
            <tbody>
                <?php while($p=mysqli_fetch_assoc($resUltimos)): ?>
                <tr>
                    <td><strong>#<?php echo $p['id_pedido']; ?></strong></td>
                    <td><?php echo htmlspecialchars($p['cliente'] ?? 'Invitado'); ?></td>
                    <td class="text-white-bold">S/ <?php echo number_format($p['total'],2); ?></td>
                    <td><span class="badge-estado badge-<?php echo str_replace(' ','_',$p['estado']); ?>"><?php echo ucfirst($p['estado']); ?></span></td>
                    <td class="text-muted-13"><?php echo date('d/m/Y H:i',strtotime($p['fecha'])); ?></td>
                    <td><a href="pedidos.php?id=<?php echo $p['id_pedido']; ?>" class="btn-edit-dark"><i class="fas fa-eye"></i> Ver</a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <div class="mt-16">
            <a href="pedidos.php" class="link-danger-plain">Ver todos los pedidos &rarr;</a>
        </div>
    </div>
</div>
<?php
$resE=mysqli_query($conexion,"SELECT estado,COUNT(*) as t FROM pedidos GROUP BY estado");
$le=[];$de=[];$ce=[];
$cm=['pendiente'=>'rgba(255,193,7,.8)','preparando'=>'rgba(33,150,243,.8)','en camino'=>'rgba(0,188,212,.8)','entregado'=>'rgba(76,175,80,.8)','cancelado'=>'rgba(244,67,54,.8)'];
while($e=mysqli_fetch_assoc($resE)){$le[]=ucfirst($e['estado']);$de[]=(int)$e['t'];$ce[]=($cm[$e['estado']]??'#999');}
if(empty($le)) { $le=['Sin datos']; $de=[1]; $ce=['#999']; }
?>
<script type="application/json" id="dashboard-data">
<?php
echo json_encode([
    'meses_js' => $meses,
    'ventas_js' => $ventas_mes,
    'pie_labels' => $le,
    'pie_data' => $de,
    'pie_colors' => $ce
]);
?>
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/admin_dashboard.js"></script>
</body>
</html>
