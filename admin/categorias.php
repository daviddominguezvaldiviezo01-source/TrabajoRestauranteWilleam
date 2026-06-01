<?php
session_start();
include(__DIR__ . '/../conexion.php');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') { header("Location:../cliente/login.php"); exit(); }

$msg = '';
if (isset($_POST['crear'])) {
    $nombre = trim($_POST['nombre']);
    $stmt = mysqli_prepare($conexion,"INSERT INTO categorias (nombre) VALUES (?)");
    mysqli_stmt_bind_param($stmt,"s",$nombre);
    mysqli_stmt_execute($stmt);
    $msg = "Categoría creada correctamente";
}
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $stmt = mysqli_prepare($conexion,"DELETE FROM categorias WHERE id_categoria=?");
    mysqli_stmt_bind_param($stmt,"i",$id);
    mysqli_stmt_execute($stmt);
    $msg = "Categoría eliminada";
}
$cats = mysqli_query($conexion,
    "SELECT c.*, COUNT(p.id_producto) AS total FROM categorias c LEFT JOIN productos p ON c.id_categoria=p.id_categoria GROUP BY c.id_categoria ORDER BY c.nombre");

$active_page = 'categorias';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Categorías - Brisamar Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
<?php include('_admin_layout.php'); ?>

<div class="main">
    <div class="page-header">
        <h1>Categorías</h1>
        <p>Gestiona las categorías de productos</p>
    </div>

    <?php if($msg): ?>
    <div class="alert-dark-success"><i class="fas fa-check-circle"></i> <?php echo $msg; ?></div>
    <?php endif; ?>

    <!-- CREAR -->
    <div class="card-dark">
        <h4><i class="fas fa-plus"></i> Nueva Categoría</h4>
        <form method="POST" class="form-dark" style="display:flex;gap:12px;align-items:flex-end;">
            <div class="form-group-dark" style="flex:1;margin:0;">
                <label>Nombre de la categoría</label>
                <input type="text" name="nombre" placeholder="Ej: Mariscos, Bebidas..." required>
            </div>
            <button type="submit" name="crear" class="btn-red"><i class="fas fa-plus"></i> Crear</button>
        </form>
    </div>

    <!-- LISTA -->
    <div class="card-dark">
        <h4><i class="fas fa-tags"></i> Categorías (<?php echo mysqli_num_rows($cats); ?>)</h4>
        <?php while($c=mysqli_fetch_assoc($cats)): ?>
        <div style="background:#111;border:1px solid #2a2a2a;border-radius:12px;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
            <div>
                <div style="font-weight:700;font-size:15px;"><?php echo htmlspecialchars($c['nombre']); ?></div>
                <div style="font-size:12px;color:rgba(255,255,255,.35);margin-top:3px;"><?php echo $c['total']; ?> producto(s)</div>
            </div>
            <a href="categorias.php?eliminar=<?php echo $c['id_categoria']; ?>"
               class="btn-del-dark" onclick="return confirm('¿Eliminar esta categoría?')">
                <i class="fas fa-trash"></i> Eliminar
            </a>
        </div>
        <?php endwhile; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
