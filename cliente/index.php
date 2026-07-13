<?php
session_start();
require_once dirname(dirname(__FILE__)) . '/conexion.php';
require_once dirname(dirname(__FILE__)) . '/includes/security.php';

// Validar que el usuario no sea admin o delivery (no pueden acceder a cliente)
if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['admin', 'delivery'])) {
    session_destroy();
    $_SESSION = [];
    header('Location: login.php');
    exit();
}

mysqli_query($conexion, "CREATE TABLE IF NOT EXISTS anuncios (
    id_anuncio INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(120) DEFAULT NULL,
    descripcion VARCHAR(255) DEFAULT NULL,
    enlace VARCHAR(255) DEFAULT NULL,
    imagen VARCHAR(255) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

$id_categoria = 0;
$categoriaSeleccionada = '';

if (isset($_GET['categoria']) && $_GET['categoria'] != "") {
    $id_categoria = intval($_GET['categoria']);
    $sql = "SELECT p.*, c.nombre AS nombre_categoria
            FROM productos p
            LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
            WHERE p.id_categoria = $id_categoria AND p.disponible = 1
            ORDER BY p.id_producto DESC";
    $categoriaRes = mysqli_query($conexion, "SELECT nombre FROM categorias WHERE id_categoria = $id_categoria LIMIT 1");
    if ($categoriaRes && mysqli_num_rows($categoriaRes) > 0) {
        $categoriaSeleccionada = mysqli_fetch_assoc($categoriaRes)['nombre'];
    }
} else {
    $sql = "SELECT p.*, c.nombre AS nombre_categoria
            FROM productos p
            LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
            WHERE p.disponible = 1
            ORDER BY p.id_producto DESC";
}

$resultado       = mysqli_query($conexion, $sql);
$resCat          = mysqli_query($conexion, "SELECT * FROM categorias ORDER BY nombre");
$totalCarrito    = isset($_SESSION['carrito']) ? array_sum($_SESSION['carrito']) : 0;
$resultadoFavoritos = mysqli_query($conexion, "SELECT p.*, c.nombre AS nombre_categoria FROM productos p LEFT JOIN categorias c ON p.id_categoria = c.id_categoria WHERE p.favorito = 1 AND p.disponible = 1 LIMIT 8");
$resultadoEstrellas = mysqli_query($conexion, "SELECT p.*, c.nombre AS nombre_categoria FROM productos p LEFT JOIN categorias c ON p.id_categoria = c.id_categoria WHERE p.estrella = 1 AND p.disponible = 1 LIMIT 8");
function imagen_valida_promocion(string $rutaImagen): bool
{
    $rutaImagen = trim($rutaImagen);
    if ($rutaImagen === '') {
        return false;
    }

    if (filter_var($rutaImagen, FILTER_VALIDATE_URL)) {
        $headers = @get_headers($rutaImagen, 1);
        if (!$headers || strpos($headers[0], '200') === false) {
            return false;
        }
        $contentType = is_array($headers['Content-Type']) ? end($headers['Content-Type']) : $headers['Content-Type'];
        return is_string($contentType) && stripos($contentType, 'image/') === 0;
    }

    $rutaServidor = __DIR__ . '/../' . ltrim($rutaImagen, '/\\');
    return is_file($rutaServidor) && @getimagesize($rutaServidor) !== false;
}

$resultadoAnuncios = mysqli_query($conexion, "SELECT * FROM anuncios WHERE activo = 1 ORDER BY creado_en DESC LIMIT 5");
$anuncios = [];
while ($anuncio = mysqli_fetch_assoc($resultadoAnuncios)) {
    if (imagen_valida_promocion($anuncio['imagen'])) {
        $anuncios[] = $anuncio;
    }
}

// Detectar imagen del hero
// Base URL dinámico (evita hardcode del nombre de carpeta)
$baseUrl = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');

// Detectar imagen del hero
$heroImage = '';
$heroDir = __DIR__ . '/../images/hero/';
if (is_dir($heroDir)) {
    $files = scandir($heroDir);
    foreach ($files as $file) {
        if (in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif'])) {
            $heroImage = $baseUrl . '/images/hero/' . $file;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brisamar - Menú</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/index.css">
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar-top">
        <div class="navbar-inner">
            <a href="index.php" class="logo">
                <i class="fas fa-fire icon-fire"></i> Brisamar
            </a>

            <div class="nav-right">
                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Buscar en el menú..." onkeyup="filtrarProductos()">
                </div>

                <a href="carrito.php" class="btn-cart">
                    <i class="fas fa-shopping-bag"></i> Carrito
                    <?php if ($totalCarrito > 0): ?>
                        <div class="cart-badge"><?php echo $totalCarrito; ?></div>
                    <?php endif; ?>
                </a>

                <?php if (isset($_SESSION['usuario'])): ?>
                    <a href="perfil.php" class="btn-nav-user">
                        <?php if (!empty($_SESSION['avatar'])): ?>
                            <img src="../<?php echo $_SESSION['avatar']; ?>" class="avatar-img" alt="Avatar">
                        <?php else: ?>
                            <i class="fas fa-user-circle"></i>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($_SESSION['nombre']); ?>
                    </a>
                    <a href="../logout.php" class="btn-nav-user btn-nav-exit">
                        <i class="fas fa-sign-out-alt"></i> Salir
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn-nav-user">
                        <i class="fas fa-sign-in-alt"></i> Ingresar
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- HERO -->
    <div class="hero-banner" style="<?php if (!empty($heroImage)): ?>background-image: url('<?php echo htmlspecialchars($heroImage); ?>');<?php endif; ?>">
        <div class="hero-overlay">
            <h1>🔥 Bienvenido a Brisamar</h1>
            <p>Los mejores sabores del mar, directo a tu mesa</p>
            <a href="#menu" class="btn-hero">Ver Menú Completo</a>
        </div>
    </div>

    <?php if (count($anuncios) > 0): ?>
        <section class="promociones-section">
            <h2 class="promociones-title">PROMOCIONES</h2>
            <div class="promo-carousel">
                <div class="promo-track">
                    <?php foreach ($anuncios as $anuncio): ?>
                        <article class="promo-card">
                            <?php $promoSrc = htmlspecialchars($baseUrl . '/' . ltrim($anuncio['imagen'], '/')); ?>
                            <?php if (!empty($anuncio['enlace'])): ?>
                                <a href="<?php echo htmlspecialchars($anuncio['enlace']); ?>">
                                    <img src="<?php echo $promoSrc; ?>" alt="Promoción" onerror="handlePromoImageError(this)">
                                </a>
                            <?php else: ?>
                                <img src="<?php echo $promoSrc; ?>" alt="Promoción" onerror="handlePromoImageError(this)">
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="promo-dots">
                <?php foreach ($anuncios as $index => $anuncio): ?>
                    <span class="promo-dot<?php echo $index === 0 ? ' active' : ''; ?>" data-index="<?php echo $index; ?>"></span>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- TABS CATEGORÍAS -->
    <div class="cats-bar">
        <div class="cats-inner">
            <a href="index.php" class="cat-tab <?php echo ($id_categoria == 0) ? 'active' : ''; ?>">
                Todos
            </a>
            <?php
            $resCat2 = mysqli_query($conexion, "SELECT * FROM categorias ORDER BY nombre");
            while ($cat = mysqli_fetch_assoc($resCat2)):
            ?>
                <a href="?categoria=<?php echo $cat['id_categoria']; ?>"
                    class="cat-tab <?php echo ($id_categoria == $cat['id_categoria']) ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($cat['nombre']); ?>
                </a>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- TOAST -->
    <?php if (isset($_SESSION['mensaje'])): ?>
        <?php $toastType = $_SESSION['mensaje_tipo'] ?? 'success'; ?>
        <div class="toast-msg <?php echo $toastType; ?>" id="toastMsg">
            <div class="toast-icon">
                <i class="fas <?php echo $toastType === 'error' ? 'fa-triangle-exclamation' : 'fa-check-circle'; ?>"></i>
            </div>
            <div class="toast-content">
                <span class="toast-title"><?php echo $toastType === 'error' ? 'Error' : '¡Agregado!'; ?></span>
                <span class="toast-text"><?php echo htmlspecialchars($_SESSION['mensaje']);
                                            unset($_SESSION['mensaje'], $_SESSION['mensaje_tipo']); ?></span>
            </div>
        </div>

    <?php endif; ?>

    <!-- CONTENIDO -->
    <div class="page-wrap" id="menu">

        <!-- FAVORITOS -->
        <?php if ($id_categoria == 0 && mysqli_num_rows($resultadoFavoritos) > 0): ?>
            <div class="sec-header">
                <h2>⭐ Favoritos</h2>
                <div class="sec-divider"></div>
            </div>
            <div class="products-grid">
                <?php while ($fila = mysqli_fetch_assoc($resultadoFavoritos)): ?>
                    <div class="prod-card">
                        <div class="prod-img-wrap">
                            <?php if (!empty($fila['imagen'])): ?>
                                <?php $imgSrcFav = preg_match('#^https?://#i', $fila['imagen']) ? $fila['imagen'] : ('../' . ltrim($fila['imagen'], '/')); ?>
                                <img src="<?php echo htmlspecialchars($imgSrcFav); ?>"
                                    alt="<?php echo htmlspecialchars($fila['nombre']); ?>"
                                    onerror="handleProductImageError(this)">
                                <div class="prod-img-fallback d-none"><i class="fas fa-utensils"></i></div>
                            <?php else: ?>
                                <div class="prod-img-fallback"><i class="fas fa-utensils"></i></div>
                            <?php endif; ?>
                            <span class="prod-badge">⭐ Favorito</span>
                        </div>
                        <div class="prod-body">
                            <div class="prod-cat"><?php echo htmlspecialchars($fila['nombre_categoria'] ?? 'General'); ?></div>
                            <div class="prod-name"><?php echo htmlspecialchars($fila['nombre']); ?></div>
                            <div class="prod-desc"><?php echo htmlspecialchars($fila['descripcion'] ?? ''); ?></div>
                            <div class="prod-footer">
                                <div class="prod-price"><span>S/</span><?php echo number_format($fila['precio'], 2); ?></div>
                                <?php if ($fila['stock'] > 0): ?>
                                    <a href="agregar_carrito.php?id=<?php echo $fila['id_producto']; ?>" class="btn-add">
                                        <i class="fas fa-plus"></i> Agregar
                                    </a>
                                <?php else: ?>
                                    <span class="btn-add disabled"><i class="fas fa-ban"></i> Agotado</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

        <!-- ESTRELLAS -->
        <?php if ($id_categoria == 0 && mysqli_num_rows($resultadoEstrellas) > 0): ?>
            <div class="sec-header">
                <h2>🌟 Nuestras Estrellas</h2>
                <div class="sec-divider"></div>
            </div>
            <div class="products-grid">
                <?php while ($fila = mysqli_fetch_assoc($resultadoEstrellas)): ?>
                    <div class="prod-card">
                        <div class="prod-img-wrap">
                            <?php if (!empty($fila['imagen'])): ?>
                                <?php $imgSrcEst = preg_match('#^https?://#i', $fila['imagen']) ? $fila['imagen'] : ('../' . ltrim($fila['imagen'], '/')); ?>
                                <img src="<?php echo htmlspecialchars($imgSrcEst); ?>"
                                    alt="<?php echo htmlspecialchars($fila['nombre']); ?>"
                                    onerror="handleProductImageError(this)">
                                <div class="prod-img-fallback d-none"><i class="fas fa-utensils"></i></div>
                            <?php else: ?>
                                <div class="prod-img-fallback"><i class="fas fa-utensils"></i></div>
                            <?php endif; ?>
                            <span class="prod-badge">🌟 Estrella</span>
                        </div>
                        <div class="prod-body">
                            <div class="prod-cat"><?php echo htmlspecialchars($fila['nombre_categoria'] ?? 'General'); ?></div>
                            <div class="prod-name"><?php echo htmlspecialchars($fila['nombre']); ?></div>
                            <div class="prod-desc"><?php echo htmlspecialchars($fila['descripcion'] ?? ''); ?></div>
                            <div class="prod-footer">
                                <div class="prod-price"><span>S/</span><?php echo number_format($fila['precio'], 2); ?></div>
                                <?php if ($fila['stock'] > 0): ?>
                                    <a href="agregar_carrito.php?id=<?php echo $fila['id_producto']; ?>" class="btn-add">
                                        <i class="fas fa-plus"></i> Agregar
                                    </a>
                                <?php else: ?>
                                    <span class="btn-add disabled"><i class="fas fa-ban"></i> Agotado</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

        <!-- MENÚ COMPLETO -->
        <div class="sec-header">
            <h2>
                🍽️ <?php echo $id_categoria > 0 ? htmlspecialchars($categoriaSeleccionada) : 'Menú Completo'; ?>
            </h2>
            <div class="sec-divider"></div>
        </div>

        <div class="products-grid" id="productosGrid">
            <?php if (mysqli_num_rows($resultado) > 0): ?>
                <?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
                    <div class="prod-card producto-item">
                        <div class="prod-img-wrap">
                            <?php if (!empty($fila['imagen'])): ?>
                                <?php $imgSrcMenu = preg_match('#^https?://#i', $fila['imagen']) ? $fila['imagen'] : ('../' . ltrim($fila['imagen'], '/')); ?>
                                <img src="<?php echo htmlspecialchars($imgSrcMenu); ?>"
                                    alt="<?php echo htmlspecialchars($fila['nombre']); ?>"
                                    onerror="handleProductImageError(this)">
                                <div class="prod-img-fallback d-none"><i class="fas fa-utensils"></i></div>
                            <?php else: ?>
                                <div class="prod-img-fallback"><i class="fas fa-utensils"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="prod-body">
                            <div class="prod-cat"><?php echo htmlspecialchars($fila['nombre_categoria'] ?? 'General'); ?></div>
                            <div class="prod-name" data-nombre="<?php echo strtolower(htmlspecialchars($fila['nombre'])); ?>">
                                <?php echo htmlspecialchars($fila['nombre']); ?>
                            </div>
                            <div class="prod-desc"><?php echo htmlspecialchars($fila['descripcion'] ?? ''); ?></div>
                            <div class="prod-footer">
                                <div class="prod-price"><span>S/</span><?php echo number_format($fila['precio'], 2); ?></div>
                                <?php if ($fila['stock'] > 0): ?>
                                    <a href="agregar_carrito.php?id=<?php echo $fila['id_producto']; ?>" class="btn-add">
                                        <i class="fas fa-plus"></i> Agregar
                                    </a>
                                <?php else: ?>
                                    <span class="btn-add disabled"><i class="fas fa-ban"></i> Agotado</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-bowl-food"></i>
                    <h3>No hay productos en esta categoría</h3>
                    <a href="index.php" class="btn-hero">Ver Todos</a>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- FOOTER -->
    <footer>
        <div class="footer-inner">
            <div>
                <h5>🔥 Brisamar</h5>
                <p class="footer-text">Los mejores sabores del mar con ingredientes frescos y atención de calidad.</p>
            </div>
            <div>
                <h5>Contacto</h5>
                <a href="tel:+51917328085"><i class="fas fa-phone-alt me-1"></i> +51 917 328 085</a>
                <a href="mailto:[EMAIL_ADDRESS]"><i class="fas fa-envelope me-1"></i> RestaurantesBrisamar@gmail.com</a>
                <a href="#"><i class="fas fa-map-marker-alt me-1"></i> Bajada de la piscina</a>
            </div>
            <div>
                <h5>Legal</h5>
                <a href="terminos.php">Términos y Condiciones</a>
                <a href="#">Política de Privacidad</a>
                <a href="#">Libro de Reclamaciones</a>
            </div>
            <div>
                <h5>Síguenos</h5>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-whatsapp"></i></a>
                    <a href="#"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; 2026 | Brisamar - Todos los derechos reservados.
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="../assets/js/index.js"></script>
</body>

</html>