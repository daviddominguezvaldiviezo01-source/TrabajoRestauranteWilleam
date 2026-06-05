<?php
session_start();
require_once __DIR__ . '/../conexion.php';
/** @var mysqli $conexion */
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') { header("Location:../cliente/login.php"); exit(); }

$msg = '';
$hero_image = '';

// Crear carpeta si no existe
$heroDir = __DIR__ . '/../images/hero/';
if (!is_dir($heroDir)) {
    mkdir($heroDir, 0755, true);
}

// Obtener imagen actual del hero
$heroFile = '';
$hero_image = '';
if (is_dir($heroDir)) {
    $files = scandir($heroDir);
    foreach ($files as $file) {
        if (in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif'])) {
            $heroFile = $heroDir . $file;
            $hero_image = '/RESTAURANTE2/images/hero/' . $file . '?t=' . filemtime($heroFile);
            break;
        }
    }
}

// Procesar subida de imagen
if (isset($_FILES['hero_imagen']) && $_FILES['hero_imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['hero_imagen']['error'] === UPLOAD_ERR_OK && is_uploaded_file($_FILES['hero_imagen']['tmp_name'])) {
        if (getimagesize($_FILES['hero_imagen']['tmp_name'])) {
            $allowedExt = ['jpg','jpeg','png','gif'];
            $ext = strtolower(pathinfo($_FILES['hero_imagen']['name'], PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowedExt, true)) {
                // Guardar con nombre fijo y extensión original
                $heroFile = $heroDir . 'hero.' . $ext;
                
                // Eliminar archivo anterior si existe
                foreach (['jpg', 'jpeg', 'png', 'gif'] as $oldExt) {
                    $oldFile = $heroDir . 'hero.' . $oldExt;
                    if (file_exists($oldFile) && $oldFile !== $heroFile) {
                        unlink($oldFile);
                    }
                }
                
                if (move_uploaded_file($_FILES['hero_imagen']['tmp_name'], $heroFile)) {
                    $msg = '✅ Imagen del hero actualizada correctamente.';
                    $hero_image = 'images/hero/hero.' . $ext . '?t=' . time();
                } else {
                    $msg = '❌ No se pudo guardar la imagen en el servidor.';
                }
            } else {
                $msg = '❌ Formato de imagen no permitido. Usa JPG, PNG o GIF.';
            }
        } else {
            $msg = '❌ El archivo subido no es una imagen válida.';
        }
    } else {
        $msg = '❌ Error al subir el archivo.';
    }
}

$active_page = 'configuracion';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Configuración - Brisamar Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
<?php include('_admin_layout.php'); ?>

<div class="main">
    <div class="page-header">
        <h1>Configuración de Tienda</h1>
        <p>Personaliza la imagen de bienvenida y otros elementos</p>
    </div>

    <?php if($msg): ?>
    <div class="alert-dark-success"><i class="fas fa-check-circle"></i> <?php echo $msg; ?></div>
    <?php endif; ?>

    <!-- IMAGEN HERO -->
    <div class="card-dark">
        <h4><i class="fas fa-image"></i> Imagen de Bienvenida (Hero)</h4>
        
        <div class="row g-3" style="margin-bottom:20px;">
            <div class="col-md-6">
                <div style="background:#111;border:1px solid #2a2a2a;border-radius:12px;overflow:hidden;padding:16px;text-align:center;">
                    <?php if(!empty($hero_image)): ?>
                        <img src="<?php echo htmlspecialchars($hero_image); ?>" alt="Hero actual" style="max-width:100%;height:auto;border-radius:8px;max-height:300px;">
                        <p style="margin-top:12px;color:rgba(255,255,255,.6);font-size:13px;">Imagen actual del hero</p>
                    <?php else: ?>
                        <div style="height:200px;display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.3);">
                            <i class="fas fa-image" style="font-size:48px;"></i>
                        </div>
                        <p style="margin-top:12px;color:rgba(255,255,255,.6);font-size:13px;">Sin imagen cargada</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-6">
                <form method="POST" enctype="multipart/form-data" class="form-dark">
                    <div class="form-group-dark">
                        <label for="hero_imagen">Seleccionar nueva imagen</label>
                        <input type="file" id="hero_imagen" name="hero_imagen" accept="image/*" required>
                        <small style="color:rgba(255,255,255,.4);display:block;margin-top:8px;">
                            Recomendado: 1200×380px o superior.<br>
                            Formatos: JPG, PNG, GIF
                        </small>
                    </div>
                    <button type="submit" class="btn-red" style="width:100%;"><i class="fas fa-upload"></i> Actualizar Imagen</button>
                </form>
                
                <div style="background:rgba(200,16,46,.1);border:1px solid rgba(200,16,46,.2);border-radius:10px;padding:14px;margin-top:16px;color:rgba(255,255,255,.7);font-size:13px;">
                    <i class="fas fa-info-circle"></i> <strong>Nota:</strong> Esta imagen aparecerá en el banner de bienvenida de la tienda. Se recomienda una imagen de alta calidad.
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
