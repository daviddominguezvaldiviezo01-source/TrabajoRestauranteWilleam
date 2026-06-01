<?php
session_start();

// Verificar que la conexión exista y cargarla de forma segura
$conexionFile = __DIR__ . '/../conexion.php';
if (!file_exists($conexionFile)) {
    die('Error crítico: No se encontró el archivo de conexión.');
}
require_once $conexionFile;

// Evitar que usuarios con rol 'delivery' vean el menú cliente
include(__DIR__ . '/guard_delivery.php');

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

$resultado = mysqli_query($conexion, $sql);

$categorias = [];
$categoriaQuery = mysqli_query($conexion, "SELECT id_categoria, nombre FROM categorias ORDER BY nombre");
if ($categoriaQuery) {
    while ($cat = mysqli_fetch_assoc($categoriaQuery)) {
        $categorias[] = $cat;
    }
    mysqli_free_result($categoriaQuery);
}

$totalCarrito = isset($_SESSION['carrito']) ? array_sum($_SESSION['carrito']) : 0;
$resultadoFavoritos = mysqli_query($conexion, "SELECT p.id_producto, p.nombre, p.descripcion, p.precio, p.stock, p.imagen, p.id_categoria, c.nombre AS nombre_categoria FROM productos p LEFT JOIN categorias c ON p.id_categoria = c.id_categoria WHERE p.favorito = 1 AND p.disponible = 1 LIMIT 8");
$resultadoEstrellas = mysqli_query($conexion, "SELECT p.id_producto, p.nombre, p.descripcion, p.precio, p.stock, p.imagen, p.id_categoria, c.nombre AS nombre_categoria FROM productos p LEFT JOIN categorias c ON p.id_categoria = c.id_categoria WHERE p.estrella = 1 AND p.disponible = 1 LIMIT 8");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Brisamar - Menú</title>
<link rel="stylesheet" href="../assets/css/index.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar-top">
<div class="navbar-inner">
    <a href="index.php" class="logo">
        <i class="fas fa-fire" style="color:#ffcc00;"></i> Brisamar
    </a>

    <div class="nav-right">
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Buscar en el menú...">
        </div>

        <a href="carrito.php" class="btn-cart">
            <i class="fas fa-shopping-bag"></i> Carrito
            <?php if($totalCarrito > 0): ?>
                <div class="cart-badge"><?php echo $totalCarrito; ?></div>
            <?php endif; ?>
        </a>

        <?php if(isset($_SESSION['usuario'])): ?>
            <a href="../admin/dashboard.php" class="btn-nav-user">
                <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['nombre']); ?>
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
<div class="hero-banner">
    <img src="../images/restaurante.jpg" alt="Brisamar"
         onerror="this.style.display='none'">
    <div class="hero-overlay">
        <h1>🔥 Bienvenido a Brisamar</h1>
        <p>Los mejores sabores del mar, directo a tu mesa</p>
        <a href="#menu" class="btn-hero">Ver Menú Completo</a>
    </div>
</div>

<!-- TABS CATEGORÍAS -->
<div class="cats-bar">
    <div class="cats-inner">
        <a href="index.php" class="cat-tab <?php echo ($id_categoria == 0) ? 'active' : ''; ?>">
            Todos
        </a>
        <?php foreach ($categorias as $cat): ?>
        <a href="?categoria=<?php echo $cat['id_categoria']; ?>"
           class="cat-tab <?php echo ($id_categoria == $cat['id_categoria']) ? 'active' : ''; ?>">
            <?php echo htmlspecialchars($cat['nombre']); ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- TOAST -->
<?php if(isset($_SESSION['mensaje'])): ?>
<div class="toast-msg" id="toastMsg">
    <i class="fas fa-check-circle" style="color:#c8102e;"></i>
    <span><?php echo $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></span>
</div>
<?php endif; ?>

<!-- CONTENIDO -->
<div class="page-wrap" id="menu">

    <!-- FAVORITOS -->
    <?php if($id_categoria == 0 && mysqli_num_rows($resultadoFavoritos) > 0): ?>
    <div class="sec-header">
        <h2>⭐ Favoritos</h2>
        <div class="sec-divider"></div>
    </div>
    <div class="products-grid">
        <?php while($fila = mysqli_fetch_assoc($resultadoFavoritos)): ?>
        <div class="prod-card">
            <div class="prod-img-wrap">
                <?php if(!empty($fila['imagen'])): ?>
                    <img src="<?php echo htmlspecialchars($fila['imagen']); ?>"
                         alt="<?php echo htmlspecialchars($fila['nombre']); ?>"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="prod-img-fallback" style="display:none;"><i class="fas fa-utensils"></i></div>
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
                    <div class="prod-price"><span>S/</span><?php echo number_format($fila['precio'],2); ?></div>
                    <?php if($fila['stock'] > 0): ?>
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
    <?php if($id_categoria == 0 && mysqli_num_rows($resultadoEstrellas) > 0): ?>
    <div class="sec-header">
        <h2>🌟 Nuestras Estrellas</h2>
        <div class="sec-divider"></div>
    </div>
    <div class="products-grid">
        <?php while($fila = mysqli_fetch_assoc($resultadoEstrellas)): ?>
        <div class="prod-card">
            <div class="prod-img-wrap">
                <?php if(!empty($fila['imagen'])): ?>
                    <img src="<?php echo htmlspecialchars($fila['imagen']); ?>"
                         alt="<?php echo htmlspecialchars($fila['nombre']); ?>"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="prod-img-fallback" style="display:none;"><i class="fas fa-utensils"></i></div>
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
                    <div class="prod-price"><span>S/</span><?php echo number_format($fila['precio'],2); ?></div>
                    <?php if($fila['stock'] > 0): ?>
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
        <?php if(mysqli_num_rows($resultado) > 0): ?>
            <?php while($fila = mysqli_fetch_assoc($resultado)): ?>
            <div class="prod-card producto-item">
                <div class="prod-img-wrap">
                    <?php if(!empty($fila['imagen'])): ?>
                        <img src="<?php echo htmlspecialchars($fila['imagen']); ?>"
                             alt="<?php echo htmlspecialchars($fila['nombre']); ?>"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="prod-img-fallback" style="display:none;"><i class="fas fa-utensils"></i></div>
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
                        <div class="prod-price"><span>S/</span><?php echo number_format($fila['precio'],2); ?></div>
                        <?php if($fila['stock'] > 0): ?>
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
            <p style="font-size:13px;">Los mejores sabores del mar con ingredientes frescos y atención de calidad.</p>
        </div>
        <div>
            <h5>Contacto</h5>
            <a href="tel:+51999999999"><i class="fas fa-phone-alt me-1"></i> +51 999 999 999</a>
            <a href="mailto:info@brisamar.com"><i class="fas fa-envelope me-1"></i> info@brisamar.com</a>
            <a href="#"><i class="fas fa-map-marker-alt me-1"></i> Calle Principal 123</a>
        </div>
        <div>
            <h5>Legal</h5>
            <a href="#">Términos y Condiciones</a>
            <a href="#">Política de Privacidad</a>
            <a href="#">Libro de Reclamaciones</a>
        </div>
        <div>
            <h5>Síguenos</h5>
            <div style="display:flex;gap:14px;font-size:1.4rem;">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-whatsapp"></i></a>
                <a href="#"><i class="fab fa-tiktok"></i></a>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        &copy; 2024 Brisamar. Todos los derechos reservados.
    </div>
</footer>

<script src="../assets/js/index.js" defer></script>
</body>
</html>
