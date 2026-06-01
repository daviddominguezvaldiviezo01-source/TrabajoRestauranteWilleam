<?php
session_start();
include(__DIR__ . '/../conexion.php');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') { header("Location:../cliente/login.php"); exit(); }

$clientes = mysqli_query($conexion,
    "SELECT u.*, COUNT(p.id_pedido) AS total_pedidos, COALESCE(SUM(p.total),0) AS total_gastado
     FROM usuarios u LEFT JOIN pedidos p ON u.id_usuario=p.id_usuario
     WHERE u.rol='cliente' GROUP BY u.id_usuario ORDER BY u.fecha_registro DESC");

$active_page = 'clientes';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Clientes - Brisamar Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
<?php include('_admin_layout.php'); ?>

<div class="main">
    <div class="page-header">
        <h1>Clientes Registrados</h1>
        <p>Listado de todos los clientes de la plataforma</p>
    </div>

    <div class="card-dark">
        <h4><i class="fas fa-users"></i> Clientes</h4>
        <table class="dark-table">
            <thead>
                <tr><th>Avatar</th><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Pedidos</th><th>Total Gastado</th><th>Registro</th></tr>
            </thead>
            <tbody>
                <?php while($c=mysqli_fetch_assoc($clientes)): ?>
                <tr>
                    <td>
                        <div style="width:38px;height:38px;background:#c8102e;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:15px;">
                            <?php echo strtoupper(substr($c['nombre'],0,1)); ?>
                        </div>
                    </td>
                    <td><strong><?php echo htmlspecialchars($c['nombre']); ?></strong></td>
                    <td style="color:rgba(255,255,255,.5);font-size:13px;"><?php echo htmlspecialchars($c['email']); ?></td>
                    <td style="color:rgba(255,255,255,.5);font-size:13px;"><?php echo htmlspecialchars($c['telefono'] ?? '-'); ?></td>
                    <td>
                        <span style="background:#2a2a2a;padding:3px 12px;border-radius:20px;font-weight:700;font-size:13px;">
                            <?php echo $c['total_pedidos']; ?>
                        </span>
                    </td>
                    <td style="font-weight:700;">S/ <?php echo number_format($c['total_gastado'],2); ?></td>
                    <td style="color:rgba(255,255,255,.4);font-size:13px;"><?php echo date('d/m/Y',strtotime($c['fecha_registro'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
