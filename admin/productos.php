<?php
session_start();
include(__DIR__ . '/../conexion.php');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') { header("Location:../cliente/login.php"); exit(); }

$msg = ''; $msg_tipo = 'success';

// CREAR
if (isset($_POST['crear'])) {
    $nombre = $_POST['nombre'];
    $desc = $_POST['descripcion'];
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);
    $imagen = $_POST['imagen'];
    $id_cat = intval($_POST['id_categoria']);
    $disp = isset($_POST['disponible']) ? 1 : 0;
    $fav = isset($_POST['favorito']) ? 1 : 0;
    $est = isset($_POST['estrella']) ? 1 : 0;
    $stmt = mysqli_prepare($conexion,
        "INSERT INTO productos (nombre,descripcion,precio,stock,imagen,disponible,id_categoria,favorito,estrella) VALUES (?,?,?,?,?,?,?,?,?)");
    mysqli_stmt_bind_param($stmt,"ssdisiiii",$nombre,$desc,$precio,$stock,$imagen,$disp,$id_cat,$fav,$est);
    mysqli_stmt_execute($stmt);
    $msg = "Producto creado correctamente ✅";
}

// EDITAR
if (isset($_POST['editar'])) {
    $id = intval($_POST['id_producto']);
    $nombre = $_POST['nombre'];
    $desc = $_POST['descripcion'];
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);
    $imagen = $_POST['imagen'];
    $id_cat = intval($_POST['id_categoria']);
    $disp = isset($_POST['disponible']) ? 1 : 0;
    $fav = isset($_POST['favorito']) ? 1 : 0;
    $est = isset($_POST['estrella']) ? 1 : 0;
    $stmt = mysqli_prepare($conexion,
        "UPDATE productos SET nombre=?,descripcion=?,precio=?,stock=?,imagen=?,disponible=?,id_categoria=?,favorito=?,estrella=? WHERE id_producto=?");
    mysqli_stmt_bind_param($stmt,"ssdisiiiii",$nombre,$desc,$precio,$stock,$imagen,$disp,$id_cat,$fav,$est,$id);
    mysqli_stmt_execute($stmt);
    $msg = "Producto actualizado ✅";
}

// ELIMINAR
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $stmt = mysqli_prepare($conexion,"DELETE FROM productos WHERE id_producto=?");
    mysqli_stmt_bind_param($stmt,"i",$id);
    mysqli_stmt_execute($stmt);
    $msg = "Producto eliminado ✅";
}

// Cargar producto para editar
$editar = null;
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $stmt = mysqli_prepare($conexion,"SELECT * FROM productos WHERE id_producto=?");
    mysqli_stmt_bind_param($stmt,"i",$id);
    mysqli_stmt_execute($stmt);
    $editar = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$productos = mysqli_query($conexion,
    "SELECT p.*, c.nombre AS nombre_cat FROM productos p LEFT JOIN categorias c ON p.id_categoria=c.id_categoria ORDER BY p.id_producto DESC");
$categorias = mysqli_fetch_all(mysqli_query($conexion,"SELECT * FROM categorias ORDER BY nombre"), MYSQLI_ASSOC);
?>
<?php $active_page = 'productos'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Productos - Brisamar Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
<?php include('_admin_layout.php'); ?>

<div class="main">
<div class="page-header">
    <h1>Gestión de Productos</h1>
    <p>Crea, edita y administra los productos del menú</p>
</div>

<?php if($msg): ?>
<div class="alert-dark-success"><i class="fas fa-check-circle"></i> <?php echo $msg; ?></div>
<?php endif; ?>

<!-- FORMULARIO -->
<div class="card-dark">
    <h4><i class="fas fa-<?php echo $editar ? 'pen' : 'plus'; ?>"></i> <?php echo $editar ? 'Editar Producto' : 'Nuevo Producto'; ?></h4>
    <form method="POST" class="form-dark">
        <?php if($editar): ?><input type="hidden" name="id_producto" value="<?php echo $editar['id_producto']; ?>"><?php endif; ?>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="form-group-dark"><label>Nombre</label>
                <input type="text" name="nombre" required value="<?php echo htmlspecialchars($editar['nombre'] ?? ''); ?>"></div>
            </div>
            <div class="col-md-3">
                <div class="form-group-dark"><label>Precio (S/)</label>
                <input type="number" name="precio" step="0.01" required value="<?php echo $editar['precio'] ?? ''; ?>"></div>
            </div>
            <div class="col-md-3">
                <div class="form-group-dark"><label>Stock</label>
                <input type="number" name="stock" required value="<?php echo $editar['stock'] ?? 0; ?>"></div>
            </div>
            <div class="col-md-6">
                <div class="form-group-dark"><label>Descripción</label>
                <textarea name="descripcion" rows="2"><?php echo htmlspecialchars($editar['descripcion'] ?? ''); ?></textarea></div>
            </div>
            <div class="col-md-6">
                <div class="form-group-dark"><label>URL de Imagen</label>
                <input type="text" name="imagen" placeholder="https://..." value="<?php echo htmlspecialchars($editar['imagen'] ?? ''); ?>"></div>
            </div>
            <div class="col-md-6">
                <div class="form-group-dark"><label>Categoría</label>
                <select name="id_categoria">
                    <option value="">Sin categoría</option>
                    <?php foreach($categorias as $cat): ?>
                    <option value="<?php echo $cat['id_categoria']; ?>" <?php if(($editar['id_categoria'] ?? '') == $cat['id_categoria']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($cat['nombre']); ?>
                    </option>
                    <?php endforeach; ?>
                </select></div>
            </div>
            <div class="col-md-6" style="display:flex;gap:24px;align-items:center;padding-top:8px;">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;color:rgba(255,255,255,.7);font-size:14px;text-transform:none;letter-spacing:0;">
                    <input type="checkbox" name="disponible" <?php echo (!$editar || $editar['disponible']) ? 'checked' : ''; ?> style="width:16px;height:16px;"> Disponible
                </label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;color:rgba(255,255,255,.7);font-size:14px;text-transform:none;letter-spacing:0;">
                    <input type="checkbox" name="favorito" <?php echo ($editar && $editar['favorito']) ? 'checked' : ''; ?> style="width:16px;height:16px;"> Favorito
                </label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;color:rgba(255,255,255,.7);font-size:14px;text-transform:none;letter-spacing:0;">
                    <input type="checkbox" name="estrella" <?php echo ($editar && $editar['estrella']) ? 'checked' : ''; ?> style="width:16px;height:16px;"> Estrella
                </label>
            </div>
            <div class="col-12" style="display:flex;gap:10px;">
                <button type="submit" name="<?php echo $editar ? 'editar' : 'crear'; ?>" class="btn-red">
                    <i class="fas fa-<?php echo $editar ? 'save' : 'plus'; ?>"></i>
                    <?php echo $editar ? 'Guardar cambios' : 'Crear Producto'; ?>
                </button>
                <?php if($editar): ?>
                    <a href="productos.php" class="btn-outline-dark">Cancelar</a>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<!-- TABLA -->
<div class="card-dark">
    <h4><i class="fas fa-list"></i> Lista de Productos (<?php echo mysqli_num_rows($productos); ?>)</h4>
    <table class="dark-table">
        <thead>
            <tr><th>#</th><th>Imagen</th><th>Nombre</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Estado</th><th>Fav</th><th>★</th><th>Acciones</th></tr>
        </thead>
        <tbody>
        <?php while($p = mysqli_fetch_assoc($productos)): ?>
        <tr>
            <td style="color:rgba(255,255,255,.4);"><?php echo $p['id_producto']; ?></td>
            <td><img src="<?php echo htmlspecialchars($p['imagen']); ?>" style="width:52px;height:52px;object-fit:cover;border-radius:8px;background:#2a2a2a;"
                     onerror="this.style.display='none'"></td>
            <td>
                <strong><?php echo htmlspecialchars($p['nombre']); ?></strong>
                <div style="font-size:12px;color:rgba(255,255,255,.35);margin-top:2px;"><?php echo htmlspecialchars(substr($p['descripcion'],0,40)); ?>...</div>
            </td>
            <td><span style="background:#2a2a2a;padding:3px 10px;border-radius:20px;font-size:12px;"><?php echo htmlspecialchars($p['nombre_cat'] ?? '-'); ?></span></td>
            <td style="font-weight:700;">S/ <?php echo number_format($p['precio'],2); ?></td>
            <td><?php echo $p['stock']; ?></td>
            <td>
                <?php if($p['disponible']): ?>
                    <span class="badge-estado badge-entregado">Activo</span>
                <?php else: ?>
                    <span class="badge-estado badge-cancelado">Inactivo</span>
                <?php endif; ?>
            </td>
            <td><?php echo $p['favorito'] ? '⭐' : '<span style="color:rgba(255,255,255,.2)">—</span>'; ?></td>
            <td><?php echo $p['estrella'] ? '🌟' : '<span style="color:rgba(255,255,255,.2)">—</span>'; ?></td>
            <td>
                <div class="d-flex gap-2">
                    <a href="productos.php?editar=<?php echo $p['id_producto']; ?>" class="btn-edit-dark"><i class="fas fa-pen"></i></a>
                    <a href="productos.php?eliminar=<?php echo $p['id_producto']; ?>" class="btn-del-dark" onclick="return confirm('¿Eliminar este producto?')"><i class="fas fa-trash"></i></a>
                </div>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
