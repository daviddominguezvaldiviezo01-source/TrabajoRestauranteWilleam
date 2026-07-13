<?php
session_start();
require_once dirname(__FILE__) . '/../conexion.php';
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') { header("Location:../cliente/login.php"); exit(); }

/** @var mysqli $conexion */

$success = "";
$error = "";

if (isset($_SESSION['success_clientes'])) {
    $success = $_SESSION['success_clientes'];
    unset($_SESSION['success_clientes']);
}
if (isset($_SESSION['error_clientes'])) {
    $error = $_SESSION['error_clientes'];
    unset($_SESSION['error_clientes']);
}

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
    
    <?php if($success): ?>
        <div class="alert alert-success bg-dark text-success border-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-danger bg-dark text-danger border-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card-dark">
        <h4><i class="fas fa-users"></i> Clientes</h4>
        <table class="dark-table">
            <thead>
                <tr><th>Avatar</th><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Pedidos</th><th>Total Gastado</th><th>Registro</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php while($c=mysqli_fetch_assoc($clientes)): ?>
                <tr>
                    <td>
                        <?php if(!empty($c['avatar']) && file_exists(__DIR__ . '/../' . $c['avatar'])): ?>
                            <div class="cliente-avatar-wrap">
                                <img src="../<?php echo $c['avatar']; ?>" class="cliente-avatar-img">
                            </div>
                        <?php else: ?>
                            <div class="cliente-avatar-default">
                                <?php echo strtoupper(substr($c['nombre'],0,1)); ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td><strong><?php echo htmlspecialchars($c['nombre']); ?></strong></td>
                    <td class="cliente-text-muted"><?php echo htmlspecialchars($c['email']); ?></td>
                    <td class="cliente-text-muted"><?php echo htmlspecialchars($c['telefono'] ?? '-'); ?></td>
                    <td>
                        <span class="cliente-badge-role">
                            <?php echo $c['total_pedidos']; ?>
                        </span>
                    </td>
                    <td class="cliente-total">S/ <?php echo number_format($c['total_gastado'],2); ?></td>
                    <td class="cliente-date"><?php echo date('d/m/Y',strtotime($c['fecha_registro'])); ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-light" onclick="editarCliente(<?php echo $c['id_usuario']; ?>, '<?php echo addslashes($c['nombre']); ?>', '<?php echo addslashes($c['email']); ?>', '<?php echo addslashes($c['telefono'] ?? ''); ?>')">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Editar Cliente -->
<div class="modal fade" id="modalEditarCliente" tabindex="-1" data-bs-theme="dark">
    <div class="modal-dialog">
        <form action="procesar_cliente.php" method="POST" class="modal-content modal-content-dark">
            <div class="modal-header modal-header-dark">
                <h5 class="modal-title text-white">Editar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="edit_cliente">
                <input type="hidden" name="id_usuario" id="edit_id_cliente">
                
                <div class="mb-3">
                    <label class="form-label text-white-50">Nombre</label>
                    <input type="text" name="nombre" id="edit_nombre" class="form-control bg-dark text-white border-secondary" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-white-50">Email</label>
                    <input type="email" name="email" id="edit_email" class="form-control bg-dark text-white border-secondary" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-white-50">Teléfono</label>
                    <input type="text" name="telefono" id="edit_telefono" class="form-control bg-dark text-white border-secondary">
                </div>
                <div class="mb-3">
                    <label class="form-label text-white-50">Nueva Contraseña (Opcional)</label>
                    <input type="password" name="password" class="form-control bg-dark text-white border-secondary" placeholder="Dejar en blanco para no cambiar">
                </div>
            </div>
            <div class="modal-footer modal-footer-dark">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger btn-danger-custom">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="../assets/js/admin_clientes.js"></script>
</body>
</html>
