<?php
session_start();
require_once dirname(__FILE__) . '/../conexion.php';
require_once dirname(__FILE__) . '/../includes/security.php';

// Validar que el usuario sea cliente
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'cliente') {
    header("Location: login.php");
    exit();
}

$id_usuario = intval($_SESSION['usuario']);
$success = "";
$error = "";

if (isset($_SESSION['success_perfil'])) {
    $success = $_SESSION['success_perfil'];
    unset($_SESSION['success_perfil']);
}
if (isset($_SESSION['error_perfil'])) {
    $error = $_SESSION['error_perfil'];
    unset($_SESSION['error_perfil']);
}

// Obtener datos actuales
$res = mysqli_query($conexion, "SELECT * FROM usuarios WHERE id_usuario = $id_usuario LIMIT 1");
$usuario = mysqli_fetch_assoc($res);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Brisamar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/perfil.css">
</head>

<body>

    <nav class="navbar-top">
        <div class="navbar-inner">
            <a href="index.php" class="logo">
                <i class="fas fa-ship"></i> BRISAMAR
            </a>
            <div class="nav-right">
                <a href="index.php" class="btn-nav-user me-2"><i class="fas fa-arrow-left"></i> Volver al Menú</a>
                <a href="../logout.php" class="btn-nav-user btn-nav-exit"><i class="fas fa-sign-out-alt"></i> Salir</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="profile-container">
            <h2 class="text-center mb-4">Mi Perfil</h2>

            <?php if ($success): ?>
                <div class="alert alert-success bg-dark text-success border-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger bg-dark text-danger border-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form action="actualizar_perfil.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="update_profile" value="1">

                <div class="text-center mb-4">
                    <?php if (!empty($usuario['avatar']) && file_exists(__DIR__ . '/../' . $usuario['avatar'])): ?>
                        <img src="../<?php echo $usuario['avatar']; ?>" class="avatar-preview" id="avatarPreview">
                    <?php else: ?>
                        <div class="avatar-placeholder" id="avatarPlaceholder">
                            <?php echo strtoupper(substr($usuario['nombre'], 0, 1)); ?>
                        </div>
                        <img src="" class="avatar-preview d-none" id="avatarPreview">
                    <?php endif; ?>

                    <label for="avatarInput" class="btn btn-outline-light btn-sm mt-2">Cambiar Foto</label>
                    <input type="file" name="avatar" id="avatarInput" class="d-none" accept="image/*" onchange="previewImage(this)">
                </div>

                <div class="mb-3">
                    <label class="form-label text-white-50">Nombre Completo</label>
                    <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                </div>

                <div class="mb-3">
                    <div class="form-group-flex">
                        <div class="flex-1">
                            <label class="form-label text-white-50">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                        </div>
                        <div class="pt-20">
                            <?php if ($usuario['email_verificado']): ?>
                                <span class="badge-verified">
                                    <i class="fas fa-check-circle"></i> Verificado
                                </span>
                            <?php else: ?>
                                <button type="button" class="btn btn-sm btn-warning btn-sm-custom" onclick="solicitarCodigoVerificacion('email')">
                                    <i class="fas fa-envelope"></i> Verificar
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-group-flex">
                        <div class="flex-1">
                            <label class="form-label text-white-50">Teléfono</label>
                            <input type="text" name="telefono" class="form-control" value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label text-white-50">Nueva Contraseña (opcional)</label>
                    <input type="password" name="password" class="form-control" placeholder="Dejar en blanco para no cambiar">
                </div>

                <button type="submit" class="btn btn-primary w-100">Guardar Cambios</button>
            </form>
        </div>
    </div>

    <!-- MODALES DE VERIFICACIÓN -->
    <!-- Modal Solicitar Código -->
    <div class="modal fade" id="modalSolicitarCodigo" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="modalLabel"><i class="fas fa-envelope-circle-check"></i> Solicitar Código de Verificación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="modalMensaje" class="text-secondary"></p>
                    <div id="solicitudSpinner" class="text-center d-none">
                        <div class="spinner-border text-warning" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                    <div id="solicitudExito" class="alert-success-custom d-none">
                        <i class="fas fa-check-circle icon-success-large"></i>
                        <p class="text-success fw-600" id="mensajeSolicitud"></p>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-warning" id="btnSolicitar" onclick="procesarSolicitud()"><i class="fas fa-paper-plane"></i> Enviar Código</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ingresar Código -->
    <div class="modal fade" id="modalIngresarCodigo" tabindex="-1" aria-labelledby="modalLabel2" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="modalLabel2"><i class="fas fa-lock"></i> Ingresar Código de Verificación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-secondary">Ingresa el código de 6 dígitos que recibiste:</p>
                    <input type="text" id="inputCodigo" class="form-control bg-secondary text-white border-secondary input-code-custom" maxlength="6" placeholder="000000">
                    <div id="mensajeError" class="alert alert-danger d-none mt-3" role="alert"></div>
                    <div id="verificacionSpinner" class="text-center mt-3 d-none">
                        <div class="spinner-border text-warning" role="status">
                            <span class="visually-hidden">Verificando...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btnVerificar" onclick="procesarVerificacion()"><i class="fas fa-check"></i> Verificar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    
<script src="../assets/js/perfil.js"></script>
</body>

</html>