<?php
session_start();
require_once __DIR__ . '/../conexion.php';
/** @var mysqli $conexion */
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') { header("Location:../cliente/login.php"); exit(); }

$msg = ''; $msg_tipo = 'success';

/* ─── Función auxiliar: mueve la imagen subida y devuelve la ruta relativa ─── */
function procesarImagenProducto($fileKey, &$error) {
    if (empty($_FILES[$fileKey]['name']) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
        return null; // No se subió ningún archivo
    }
    $extension = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
    $allowed   = ['jpg','jpeg','png','gif','webp'];
    if (!in_array($extension, $allowed, true)) {
        $error = 'Solo se permiten imágenes JPG, PNG, GIF o WEBP.';
        return false;
    }
    if (!getimagesize($_FILES[$fileKey]['tmp_name'])) {
        $error = 'El archivo subido no es una imagen válida.';
        return false;
    }
    $destDir = __DIR__ . '/../images/productos/';
    if (!is_dir($destDir)) { mkdir($destDir, 0755, true); }
    $nombre   = uniqid('prod_') . '.' . $extension;
    $rutaAbs  = $destDir . $nombre;
    $rutaRel  = 'images/productos/' . $nombre;
    if (!move_uploaded_file($_FILES[$fileKey]['tmp_name'], $rutaAbs)) {
        $error = 'No se pudo guardar la imagen. Verifica permisos de la carpeta.';
        return false;
    }
    return $rutaRel;
}

// ─── CREAR ─────────────────────────────────────────────────────────────────
if (isset($_POST['crear'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $desc   = trim($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $stock  = intval($_POST['stock'] ?? 0);
    $id_cat = intval($_POST['id_categoria'] ?? 0);
    $disp   = isset($_POST['disponible']) ? 1 : 0;
    $fav    = isset($_POST['favorito'])   ? 1 : 0;
    $est    = isset($_POST['estrella'])   ? 1 : 0;

    if ($nombre === '' || $precio < 0 || $stock < 0) {
        $msg = "Completa correctamente nombre, precio y stock.";
        $msg_tipo = 'error';
    } else {
        $errImg = '';
        $imagen = procesarImagenProducto('imagen_archivo', $errImg);
        if ($imagen === false) {
            $msg = $errImg;
            $msg_tipo = 'error';
        } else {
            $imagen = $imagen ?? ''; // si no subió imagen queda vacío
            $stmt = mysqli_prepare($conexion,
                "INSERT INTO productos (nombre,descripcion,precio,stock,imagen,disponible,id_categoria,favorito,estrella) VALUES (?,?,?,?,?,?,?,?,?)");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt,"ssdisiiii",$nombre,$desc,$precio,$stock,$imagen,$disp,$id_cat,$fav,$est);
                if (mysqli_stmt_execute($stmt)) {
                    $msg = "Producto creado correctamente ✅";
                    $msg_tipo = 'success';
                } else {
                    $msg = "Error al crear el producto. Intenta nuevamente.";
                    $msg_tipo = 'error';
                }
                mysqli_stmt_close($stmt);
            } else {
                $msg = "Error interno al preparar la consulta.";
                $msg_tipo = 'error';
            }
        }
    }
}

// ─── EDITAR ────────────────────────────────────────────────────────────────
if (isset($_POST['editar'])) {
    $id     = intval($_POST['id_producto']);
    $nombre = trim($_POST['nombre'] ?? '');
    $desc   = trim($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $stock  = intval($_POST['stock'] ?? 0);
    $id_cat = intval($_POST['id_categoria'] ?? 0);
    $disp   = isset($_POST['disponible']) ? 1 : 0;
    $fav    = isset($_POST['favorito'])   ? 1 : 0;
    $est    = isset($_POST['estrella'])   ? 1 : 0;
    $imgActual = trim($_POST['imagen_actual'] ?? '');

    if ($nombre === '' || $precio < 0 || $stock < 0) {
        $msg = "Completa correctamente nombre, precio y stock.";
        $msg_tipo = 'error';
    } else {
        $errImg = '';
        $nuevaImg = procesarImagenProducto('imagen_archivo', $errImg);
        if ($nuevaImg === false) {
            $msg = $errImg;
            $msg_tipo = 'error';
        } else {
            // Si se subió imagen nueva, usarla; si no, conservar la actual
            $imagen = $nuevaImg ?? $imgActual;

            // Eliminar imagen antigua si se reemplazó
            if ($nuevaImg && $imgActual) {
                $rutaVieja = __DIR__ . '/../' . $imgActual;
                if (file_exists($rutaVieja)) { @unlink($rutaVieja); }
            }

            $stmt = mysqli_prepare($conexion,
                "UPDATE productos SET nombre=?,descripcion=?,precio=?,stock=?,imagen=?,disponible=?,id_categoria=?,favorito=?,estrella=? WHERE id_producto=?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt,"ssdisiiiii",$nombre,$desc,$precio,$stock,$imagen,$disp,$id_cat,$fav,$est,$id);
                if (mysqli_stmt_execute($stmt)) {
                    $msg = "Producto actualizado ✅";
                    $msg_tipo = 'success';
                } else {
                    $msg = "Error al actualizar el producto. Intenta nuevamente.";
                    $msg_tipo = 'error';
                }
                mysqli_stmt_close($stmt);
            } else {
                $msg = "Error interno al preparar la consulta.";
                $msg_tipo = 'error';
            }
        }
    }
}

// ─── ELIMINAR ──────────────────────────────────────────────────────────────
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    // Obtener imagen antes de eliminar
    $stmtImg = mysqli_prepare($conexion,"SELECT imagen FROM productos WHERE id_producto=?");
    mysqli_stmt_bind_param($stmtImg,"i",$id);
    mysqli_stmt_execute($stmtImg);
    $resImg = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtImg));
    if ($resImg && $resImg['imagen']) {
        $ruta = __DIR__ . '/../' . $resImg['imagen'];
        if (file_exists($ruta)) { @unlink($ruta); }
    }
    $stmt = mysqli_prepare($conexion,"DELETE FROM productos WHERE id_producto=?");
    mysqli_stmt_bind_param($stmt,"i",$id);
    mysqli_stmt_execute($stmt);
    $msg = "Producto eliminado ✅";
}

// ─── Cargar producto para editar ────────────────────────────────────────────
$editar = null;
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $stmt = mysqli_prepare($conexion,"SELECT * FROM productos WHERE id_producto=?");
    mysqli_stmt_bind_param($stmt,"i",$id);
    mysqli_stmt_execute($stmt);
    $editar = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$productos   = mysqli_query($conexion,
    "SELECT p.*, c.nombre AS nombre_cat FROM productos p LEFT JOIN categorias c ON p.id_categoria=c.id_categoria ORDER BY p.id_producto DESC");
$categorias  = mysqli_fetch_all(mysqli_query($conexion,"SELECT * FROM categorias ORDER BY nombre"), MYSQLI_ASSOC);
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
<style>
/* ── Upload zone ── */
.upload-zone {
    border: 2px dashed rgba(255,255,255,.18);
    border-radius: 12px;
    padding: 22px 18px;
    text-align: center;
    cursor: pointer;
    transition: border-color .25s, background .25s;
    background: rgba(255,255,255,.03);
    position: relative;
}
.upload-zone:hover, .upload-zone.dragover {
    border-color: #e53935;
    background: rgba(229,57,53,.06);
}
.upload-zone input[type="file"] {
    position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
}
.upload-zone .uz-icon { font-size: 28px; color: rgba(255,255,255,.35); margin-bottom: 8px; }
.upload-zone .uz-text { color: rgba(255,255,255,.5); font-size: 13px; }
.upload-zone .uz-text strong { color: rgba(255,255,255,.8); }

/* ── Image preview ── */
.img-preview-wrap {
    display: none;
    position: relative;
    width: 130px;
    margin: 14px auto 0;
}
.img-preview-wrap img {
    width: 130px; height: 130px; object-fit: cover;
    border-radius: 10px; border: 2px solid rgba(255,255,255,.12);
    display: block;
}
.img-preview-wrap .btn-remove-img {
    position: absolute; top: -8px; right: -8px;
    background: #e53935; border: none; border-radius: 50%;
    width: 26px; height: 26px; display: flex; align-items: center; justify-content: center;
    color: #fff; cursor: pointer; font-size: 12px; padding: 0;
    box-shadow: 0 2px 8px rgba(0,0,0,.4);
}
.img-preview-wrap .btn-remove-img:hover { background: #b71c1c; }

/* ── Current image (edit mode) ── */
.current-img-box {
    display: flex; align-items: center; gap: 14px;
    background: rgba(255,255,255,.04); border-radius: 10px;
    padding: 12px 14px; margin-bottom: 12px;
    border: 1px solid rgba(255,255,255,.08);
}
.current-img-box img {
    width: 68px; height: 68px; object-fit: cover; border-radius: 8px;
    border: 1px solid rgba(255,255,255,.1);
}
.current-img-box .ci-info { flex: 1; }
.current-img-box .ci-label { color: rgba(255,255,255,.45); font-size: 11px; text-transform: uppercase; letter-spacing: .06em; }
.current-img-box .ci-name  { color: rgba(255,255,255,.8); font-size: 13px; margin-top: 2px; word-break: break-all; }
</style>
</head>
<body>
<?php include('_admin_layout.php'); ?>

<div class="main">
<div class="page-header">
    <h1>Gestión de Productos</h1>
    <p>Crea, edita y administra los productos del menú</p>
</div>

<?php if($msg): ?>
<div class="alert-dark-success" style="<?php echo $msg_tipo==='error' ? 'background:rgba(244,67,54,.12);border-color:rgba(244,67,54,.3);color:#ef9a9a;' : ''; ?>">
    <i class="fas <?php echo $msg_tipo==='error' ? 'fa-triangle-exclamation' : 'fa-check-circle'; ?>"></i> <?php echo $msg; ?>
</div>
<?php endif; ?>

<!-- ══ FORMULARIO ══════════════════════════════════════════════════════════ -->
<div class="card-dark">
    <h4><i class="fas fa-<?php echo $editar ? 'pen' : 'plus'; ?>"></i> <?php echo $editar ? 'Editar Producto' : 'Nuevo Producto'; ?></h4>
    <form method="POST" enctype="multipart/form-data" class="form-dark">
        <?php if($editar): ?><input type="hidden" name="id_producto" value="<?php echo $editar['id_producto']; ?>"><?php endif; ?>
        <div class="row g-3">

            <!-- Nombre -->
            <div class="col-md-6">
                <div class="form-group-dark"><label>Nombre</label>
                <input type="text" name="nombre" required value="<?php echo htmlspecialchars($editar['nombre'] ?? ''); ?>"></div>
            </div>

            <!-- Precio -->
            <div class="col-md-3">
                <div class="form-group-dark"><label>Precio (S/)</label>
                <input type="number" name="precio" step="0.01" required value="<?php echo $editar['precio'] ?? ''; ?>"></div>
            </div>

            <!-- Stock -->
            <div class="col-md-3">
                <div class="form-group-dark"><label>Stock</label>
                <input type="number" name="stock" required value="<?php echo $editar['stock'] ?? 0; ?>"></div>
            </div>

            <!-- Descripción -->
            <div class="col-md-6">
                <div class="form-group-dark"><label>Descripción</label>
                <textarea name="descripcion" rows="2"><?php echo htmlspecialchars($editar['descripcion'] ?? ''); ?></textarea></div>
            </div>

            <!-- ── IMAGEN ──────────────────────────────────────────────────── -->
            <div class="col-md-6">
                <div class="form-group-dark">
                    <label><?php echo $editar ? 'Cambiar imagen del producto' : 'Imagen del producto'; ?></label>

                    <?php if ($editar): ?>
                        <!-- Imagen actual en modo edición -->
                        <?php if (!empty($editar['imagen'])): ?>
                        <div class="current-img-box" id="currentImgBox">
                            <img src="../<?php echo htmlspecialchars($editar['imagen']); ?>"
                                 onerror="this.src='https://via.placeholder.com/68x68/2a2a2a/666?text=?'">
                            <div class="ci-info">
                                <div class="ci-label">Imagen actual</div>
                                <div class="ci-name"><?php echo htmlspecialchars(basename($editar['imagen'])); ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <input type="hidden" name="imagen_actual" value="<?php echo htmlspecialchars($editar['imagen'] ?? ''); ?>">
                        <p style="color:rgba(255,255,255,.4);font-size:12px;margin-bottom:8px;">
                            <i class="fas fa-info-circle"></i> Selecciona un archivo nuevo para reemplazarla. Si no seleccionas nada, se conservará la imagen actual.
                        </p>
                    <?php endif; ?>

                    <!-- Zona de subida -->
                    <div class="upload-zone" id="uploadZone">
                        <input type="file" name="imagen_archivo" id="imagenArchivo" accept="image/*">
                        <div class="uz-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                        <div class="uz-text">
                            <strong>Haz clic o arrastra</strong> una imagen aquí<br>
                            <span>JPG, PNG, WEBP o GIF · Máx. recomendado 5 MB</span>
                        </div>
                    </div>

                    <!-- Vista previa del nuevo archivo -->
                    <div class="img-preview-wrap" id="imgPreviewWrap">
                        <img id="imgPreview" src="" alt="Vista previa">
                        <button type="button" class="btn-remove-img" id="btnRemoveImg" title="Quitar imagen seleccionada">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
            <!-- ─────────────────────────────────────────────────────────── -->

            <!-- Categoría -->
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

            <!-- Checkboxes -->
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

            <!-- Botones -->
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

<!-- ══ TABLA ═══════════════════════════════════════════════════════════════ -->
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
            <td>
                <?php
                // Soporta rutas relativas guardadas (images/productos/...) y URLs externas
                $imgSrc = $p['imagen'] ?? '';
                if ($imgSrc && !preg_match('/^https?:\/\//', $imgSrc)) {
                    $imgSrc = '../' . ltrim($imgSrc, '/');
                }
                ?>
                <img src="<?php echo htmlspecialchars($imgSrc); ?>"
                     style="width:52px;height:52px;object-fit:cover;border-radius:8px;background:#2a2a2a;"
                     onerror="this.style.display='none'">
            </td>
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
<script>
(function () {
    const input     = document.getElementById('imagenArchivo');
    const zone      = document.getElementById('uploadZone');
    const prevWrap  = document.getElementById('imgPreviewWrap');
    const prevImg   = document.getElementById('imgPreview');
    const btnRemove = document.getElementById('btnRemoveImg');

    function showPreview(file) {
        if (!file || !file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = e => {
            prevImg.src = e.target.result;
            prevWrap.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }

    function clearPreview() {
        prevImg.src = '';
        prevWrap.style.display = 'none';
        input.value = '';
    }

    input.addEventListener('change', () => {
        if (input.files && input.files[0]) showPreview(input.files[0]);
        else clearPreview();
    });

    btnRemove && btnRemove.addEventListener('click', clearPreview);

    // Drag & drop
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
    zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.classList.remove('dragover');
        const dt = e.dataTransfer;
        if (dt && dt.files && dt.files[0]) {
            // Asignar al input
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(dt.files[0]);
            input.files = dataTransfer.files;
            showPreview(dt.files[0]);
        }
    });
})();
</script>
</body>
</html>
