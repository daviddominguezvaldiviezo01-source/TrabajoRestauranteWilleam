<?php
// _admin_layout.php — incluir al inicio del <body> en cada página admin
// Uso: include('_admin_layout.php'); con $active_page definido antes
?>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{background:#111;font-family:'Segoe UI',sans-serif;color:#fff;min-height:100vh;}

/* SIDEBAR */
.sidebar{
    width:240px;height:100vh;position:fixed;top:0;left:0;
    background:#0d0d0d;border-right:1px solid #1e1e1e;
    padding:24px 16px;display:flex;flex-direction:column;z-index:300;overflow-y:auto;
}
.sidebar-brand{
    display:flex;align-items:center;gap:10px;
    font-size:20px;font-weight:900;color:#fff;
    padding:0 8px;margin-bottom:20px;text-decoration:none;
}
.sidebar-brand i{color:#ffcc00;}
.sidebar-user{
    background:#1a1a1a;border:1px solid #2a2a2a;border-radius:12px;
    padding:14px;margin-bottom:20px;display:flex;align-items:center;gap:12px;
}
.sidebar-avatar{
    width:40px;height:40px;background:#c8102e;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    font-weight:900;font-size:16px;flex-shrink:0;
}
.sidebar-user-name{font-weight:700;font-size:14px;color:#fff;}
.sidebar-user-role{font-size:11px;color:rgba(255,255,255,.4);}
.sidebar-sep{border:none;border-top:1px solid #1e1e1e;margin:10px 0;}
.sidebar a{
    display:flex;align-items:center;gap:10px;
    color:rgba(255,255,255,.45);text-decoration:none;
    padding:11px 12px;border-radius:10px;margin-bottom:4px;
    font-size:14px;font-weight:600;transition:.2s;
}
.sidebar a i{width:18px;font-size:14px;}
.sidebar a:hover{color:#fff;background:#1a1a1a;}
.sidebar a.active{color:#fff;background:#c8102e;}
.sidebar a.danger{color:rgba(255,100,100,.6);}
.sidebar a.danger:hover{color:#fff;background:rgba(200,16,46,.3);}
.sidebar-badge{
    margin-left:auto;background:#c8102e;color:#fff;
    font-size:10px;font-weight:900;padding:2px 7px;border-radius:20px;
}

/* MAIN */
.main{margin-left:240px;padding:32px;min-height:100vh;}
.page-header{margin-bottom:28px;}
.page-header h1{font-size:1.6rem;font-weight:900;color:#fff;}
.page-header p{color:rgba(255,255,255,.4);font-size:14px;margin-top:4px;}

/* CARDS OSCURAS */
.card-dark{
    background:#1a1a1a;border:1px solid #2a2a2a;
    border-radius:16px;padding:24px;margin-bottom:20px;
}
.card-dark h4{
    font-size:14px;font-weight:800;color:#fff;
    margin-bottom:20px;text-transform:uppercase;letter-spacing:.5px;
    display:flex;align-items:center;gap:8px;
}
.card-dark h4 i{color:#c8102e;}

/* TABLA */
.dark-table{width:100%;border-collapse:collapse;}
.dark-table thead tr{background:#111;}
.dark-table th{
    padding:12px 16px;font-size:11px;font-weight:700;
    color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.6px;
    border-bottom:1px solid #2a2a2a;
}
.dark-table td{
    padding:13px 16px;border-bottom:1px solid #1e1e1e;
    vertical-align:middle;font-size:14px;
}
.dark-table tbody tr{transition:.15s;}
.dark-table tbody tr:hover{background:#1e1e1e;}

/* BADGES ESTADO */
.badge-estado{padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;}
.badge-pendiente{background:rgba(255,193,7,.15);color:#ffc107;}
.badge-preparando{background:rgba(33,150,243,.15);color:#64b5f6;}
.badge-en_camino{background:rgba(0,188,212,.15);color:#4dd0e1;}
.badge-entregado{background:rgba(76,175,80,.15);color:#81c784;}
.badge-cancelado{background:rgba(244,67,54,.15);color:#e57373;}

/* FORM DARK */
.form-dark input,.form-dark select,.form-dark textarea{
    background:#111;border:1.5px solid #2a2a2a;border-radius:10px;
    color:#fff;padding:10px 14px;font-size:14px;width:100%;transition:.2s;
}
.form-dark input:focus,.form-dark select:focus,.form-dark textarea:focus{
    outline:none;border-color:#c8102e;
}
.form-dark input::placeholder,.form-dark textarea::placeholder{color:rgba(255,255,255,.2);}
.form-dark select option{background:#1a1a1a;}
.form-dark label{
    display:block;color:rgba(255,255,255,.5);
    font-size:11px;font-weight:700;text-transform:uppercase;
    letter-spacing:.5px;margin-bottom:6px;
}
.form-group-dark{margin-bottom:16px;}

/* BOTONES */
.btn-red{background:#c8102e;color:#fff;border:none;border-radius:10px;padding:10px 20px;font-weight:700;font-size:14px;cursor:pointer;transition:.2s;text-decoration:none;display:inline-flex;align-items:center;gap:7px;}
.btn-red:hover{background:#a50d26;color:#fff;transform:translateY(-1px);}
.btn-outline-dark{background:transparent;color:rgba(255,255,255,.5);border:1.5px solid #2a2a2a;border-radius:10px;padding:9px 18px;font-weight:600;font-size:13px;cursor:pointer;transition:.2s;text-decoration:none;display:inline-flex;align-items:center;gap:7px;}
.btn-outline-dark:hover{border-color:rgba(255,255,255,.3);color:#fff;}
.btn-edit-dark{background:#1e1e1e;color:#fff;border:1px solid #2a2a2a;border-radius:8px;padding:6px 13px;font-size:12px;font-weight:700;text-decoration:none;transition:.2s;display:inline-flex;align-items:center;gap:5px;}
.btn-edit-dark:hover{background:#2a2a2a;color:#fff;}
.btn-del-dark{background:rgba(200,16,46,.15);color:#e57373;border:1px solid rgba(200,16,46,.2);border-radius:8px;padding:6px 13px;font-size:12px;font-weight:700;text-decoration:none;transition:.2s;display:inline-flex;align-items:center;gap:5px;}
.btn-del-dark:hover{background:#c8102e;color:#fff;}

/* ALERT */
.alert-dark-success{background:rgba(76,175,80,.1);border:1px solid rgba(76,175,80,.3);color:#81c784;padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:8px;}

/* STAT CARDS */
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;}
.stat-card{background:#1a1a1a;border:1px solid #2a2a2a;border-radius:14px;padding:20px;}
.stat-card .stat-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;margin-bottom:14px;}
.stat-card .stat-label{font-size:12px;color:rgba(255,255,255,.4);font-weight:600;margin-bottom:6px;}
.stat-card .stat-value{font-size:1.7rem;font-weight:900;color:#fff;}

/* FILTRO TABS */
.filter-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px;}
.filter-tab{padding:7px 16px;border-radius:20px;font-size:13px;font-weight:600;text-decoration:none;border:1.5px solid #2a2a2a;color:rgba(255,255,255,.45);transition:.2s;}
.filter-tab:hover{color:#fff;border-color:rgba(255,255,255,.3);}
.filter-tab.active{background:#c8102e;border-color:#c8102e;color:#fff;}

@media(max-width:900px){
    .sidebar{width:200px;}
    .main{margin-left:200px;padding:20px;}
    .stats-grid{grid-template-columns:repeat(2,1fr);}
}
</style>

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
    <a href="pedidos.php" class="<?php echo ($active_page==='pedidos')?'active':''; ?>">
        <i class="fas fa-receipt"></i> Pedidos
        <?php
        $pp = mysqli_fetch_assoc(mysqli_query($conexion,"SELECT COUNT(*) AS c FROM pedidos WHERE estado='pendiente'"))['c'];
        if($pp > 0): ?><span class="sidebar-badge"><?php echo $pp; ?></span><?php endif; ?>
    </a>
    <a href="clientes.php" class="<?php echo ($active_page==='clientes')?'active':''; ?>">
        <i class="fas fa-users"></i> Clientes
    </a>
    <a href="categorias.php" class="<?php echo ($active_page==='categorias')?'active':''; ?>">
        <i class="fas fa-tags"></i> Categorías
    </a>
    <hr class="sidebar-sep">
    <a href="../logout.php" class="danger">
        <i class="fas fa-right-from-bracket"></i> Cerrar Sesión
    </a>
</div>
