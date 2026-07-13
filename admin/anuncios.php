<?php
session_start();
require_once __DIR__ . '/../conexion.php';
/** @var mysqli $conexion */
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') { header("Location:../cliente/login.php"); exit(); }

$message = '';
$messageType = 'success';

mysqli_query($conexion, "CREATE TABLE IF NOT EXISTS anuncios (
    id_anuncio INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(120) DEFAULT NULL,
    descripcion VARCHAR(255) DEFAULT NULL,
    enlace VARCHAR(255) DEFAULT NULL,
    imagen VARCHAR(255) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'subir_anuncio') {
    if (!empty($_FILES['imagen_anuncio']['name']) && $_FILES['imagen_anuncio']['error'] === UPLOAD_ERR_OK) {
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $enlace = trim($_POST['enlace'] ?? '');
        $destinoDir = __DIR__ . '/../images/anuncios/';

        $extension = strtolower(pathinfo($_FILES['imagen_anuncio']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($extension, $allowedExtensions, true)) {
            $message = 'Solo se permiten imágenes JPG, PNG, GIF o WEBP.';
            $messageType = 'error';
        } elseif (!getimagesize($_FILES['imagen_anuncio']['tmp_name'])) {
            $message = 'El archivo subido no es una imagen válida.';
            $messageType = 'error';
        } else {
            if (!is_dir($destinoDir)) {
                mkdir($destinoDir, 0755, true);
            }

            $nombreArchivo = uniqid('ad_') . '.' . $extension;
            $rutaServidor = $destinoDir . $nombreArchivo; // ruta absoluta en servidor
            $rutaBD = 'images/anuncios/' . $nombreArchivo; // ruta relativa para la BD / front-end

            if (move_uploaded_file($_FILES['imagen_anuncio']['tmp_name'], $rutaServidor)) {
                if (!filter_var($enlace, FILTER_VALIDATE_URL)) {
                    $enlace = '';
                }

                $stmt = mysqli_prepare($conexion, "INSERT INTO anuncios (titulo, descripcion, enlace, imagen, activo) VALUES (?, ?, ?, ?, 1)");
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, 'ssss', $titulo, $descripcion, $enlace, $rutaBD);
                    if (mysqli_stmt_execute($stmt)) {
                        $message = 'Anuncio cargado correctamente.';
                        $messageType = 'success';
                    } else {
                        $message = 'Error al guardar los datos del anuncio.';
                        $messageType = 'error';
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $message = 'Error interno al preparar la consulta.';
                    $messageType = 'error';
                }
            } else {
                $message = 'No se pudo mover la imagen al directorio de anuncios.';
                $messageType = 'error';
            }
        }
    } else {
        $message = 'Selecciona una imagen válida para el anuncio.';
        $messageType = 'error';
    }
}

if (isset($_GET['eliminar_ad']) && is_numeric($_GET['eliminar_ad'])) {
    $idEliminar = intval($_GET['eliminar_ad']);
    $stmtAd = mysqli_prepare($conexion, "SELECT imagen FROM anuncios WHERE id_anuncio = ? LIMIT 1");
    if ($stmtAd) {
        mysqli_stmt_bind_param($stmtAd, 'i', $idEliminar);
        mysqli_stmt_execute($stmtAd);
        $adRes = mysqli_stmt_get_result($stmtAd);
        if ($adRes && mysqli_num_rows($adRes) > 0) {
            $ad = mysqli_fetch_assoc($adRes);
            $rutaArchivo = __DIR__ . '/../' . $ad['imagen'];
            if (file_exists($rutaArchivo)) {
                @unlink($rutaArchivo);
            }
            $deleteStmt = mysqli_prepare($conexion, "DELETE FROM anuncios WHERE id_anuncio = ?");
            if ($deleteStmt) {
                mysqli_stmt_bind_param($deleteStmt, 'i', $idEliminar);
                mysqli_stmt_execute($deleteStmt);
                mysqli_stmt_close($deleteStmt);
            }
            $message = 'Anuncio eliminado correctamente.';
        }
        mysqli_stmt_close($stmtAd);
    }
}

$resAnuncios = mysqli_query($conexion, "SELECT * FROM anuncios ORDER BY creado_en DESC");
$active_page = 'anuncios';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Publicidad - Brisamar Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="../assets/css/admin_anuncios.css">
</head>
<body>
<?php include('_admin_layout.php'); ?>

<div class="main">
    <div class="page-header">
        <h1>Publicidad</h1>
        <p>Administra los anuncios que aparecen en el carrusel de la página principal.</p>
    </div>

    <?php if($message): ?>
    <div class="<?php echo ($messageType==='error') ? 'alert-error-anuncio' : 'alert-success-anuncio'; ?>">
        <i class="fas <?php echo ($messageType==='error') ? 'fa-triangle-exclamation' : 'fa-check-circle'; ?>"></i>
        <span><?php echo htmlspecialchars($message); ?></span>
    </div>
    <?php endif; ?>

    <div class="card-dark">
        <h4><i class="fas fa-bullhorn"></i> Nueva publicidad</h4>
        <p class="anuncio-desc">Sube un nuevo anuncio para que aparezca en el carrusel de inicio.</p>
        <form class="form-dark" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="accion" value="subir_anuncio">
            <div class="form-group-dark">
                <label for="titulo">Título</label>
                <input type="text" id="titulo" name="titulo" placeholder="Título del anuncio (opcional)">
            </div>
            <div class="form-group-dark">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="3" placeholder="Texto breve del anuncio (opcional)" class="anuncio-textarea"></textarea>
            </div>
            <div class="form-group-dark">
                <label for="enlace">Enlace</label>
                <input type="text" id="enlace" name="enlace" placeholder="URL de destino (opcional)">
            </div>
            <div class="form-group-dark">
                <label for="imagen_anuncio">Imagen del anuncio</label>
                <input type="file" id="imagen_anuncio" name="imagen_anuncio" accept="image/*" required>
            </div>
            <button type="submit" class="btn-red"><i class="fas fa-upload"></i> Subir anuncio</button>
        </form>
    </div>

    <div class="card-dark">
        <h4><i class="fas fa-list"></i> Anuncios existentes</h4>
        <?php if(mysqli_num_rows($resAnuncios) > 0): ?>
        <table class="dark-table">
            <thead>
                <tr><th>ID</th><th>Título</th><th>Imagen</th><th>Activo</th><th>Fecha</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php while($anuncio = mysqli_fetch_assoc($resAnuncios)): ?>
                <tr>
                    <td><?php echo $anuncio['id_anuncio']; ?></td>
                    <td><?php echo htmlspecialchars($anuncio['titulo'] ?: 'Sin título'); ?></td>
                    <td><a href="../<?php echo htmlspecialchars($anuncio['imagen']); ?>" target="_blank" class="anuncio-link">Ver</a></td>
                    <td><?php echo $anuncio['activo'] ? 'Sí' : 'No'; ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($anuncio['creado_en'])); ?></td>
                    <td><a href="anuncios.php?eliminar_ad=<?php echo $anuncio['id_anuncio']; ?>" class="btn-del-dark" onclick="return confirm('Eliminar este anuncio?');">Eliminar</a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="anuncio-empty">No hay anuncios publicados aún.</p>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
