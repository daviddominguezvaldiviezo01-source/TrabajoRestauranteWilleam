<?php
// _admin_layout.php — incluir al inicio del <body> en cada página admin
// Uso: include('_admin_layout.php'); con $active_page definido antes
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../conexion.php';
/** @var mysqli $conexion */
$username = htmlspecialchars($_SESSION['nombre'] ?? 'Administrador');
?>
<link rel="stylesheet" href="../assets/css/admin.css">

<!-- SIDEBAR -->
<div class="sidebar">
    <a href="dashboard.php" class="sidebar-brand">
        <i class="fas fa-fire"></i> Brisamar
    </a>
    <div class="sidebar-user">
        <div class="sidebar-avatar"><?php echo strtoupper(substr($_SESSION['nombre'],0,1)); ?></div>
        <div>
            <div class="sidebar-user-name"><?php echo htmlspecialchars($_SESSION['nombre']); ?></div>
            <div class="sidebar-user-role">Administrador</div>
        </div>
    </div>
    <hr class="sidebar-sep">
    <a href="dashboard.php" class="<?php echo ($active_page==='dashboard')?'active':''; ?>">
        <i class="fas fa-chart-line"></i> Dashboard
    </a>
    <a href="productos.php" class="<?php echo ($active_page==='productos')?'active':''; ?>">
        <i class="fas fa-box"></i> Productos
    </a>
    <a href="anuncios.php" class="<?php echo ($active_page==='anuncios')?'active':''; ?>">
        <i class="fas fa-bullhorn"></i> Publicidad
    </a>
    <a href="pedidos.php" class="<?php echo ($active_page==='pedidos')?'active':''; ?>">
        <i class="fas fa-receipt"></i> Pedidos
        <?php
        $pp = 0;
        $estadoPendiente = 'pendiente';
        $stmtCount = mysqli_prepare($conexion, "SELECT COUNT(*) AS c FROM pedidos WHERE estado = ?");
        if ($stmtCount) {
            mysqli_stmt_bind_param($stmtCount, 's', $estadoPendiente);
            mysqli_stmt_execute($stmtCount);
            $resCount = mysqli_stmt_get_result($stmtCount);
            if ($resCount) {
                $pp = mysqli_fetch_assoc($resCount)['c'] ?? 0;
            }
            mysqli_stmt_close($stmtCount);
        }
        if($pp > 0): ?><span class="sidebar-badge"><?php echo $pp; ?></span><?php endif; ?>
    </a>
    <a href="clientes.php" class="<?php echo ($active_page==='clientes')?'active':''; ?>">
        <i class="fas fa-users"></i> Clientes
    </a>
    <a href="categorias.php" class="<?php echo ($active_page==='categorias')?'active':''; ?>">
        <i class="fas fa-tags"></i> Categorías
    </a>
    <a href="configuracion.php" class="<?php echo ($active_page==='configuracion')?'active':''; ?>">
        <i class="fas fa-cog"></i> Configuración
    </a>
    <hr class="sidebar-sep">
    <a href="../logout.php" class="danger">
        <i class="fas fa-right-from-bracket"></i> Cerrar Sesión
    </a>
</div>
