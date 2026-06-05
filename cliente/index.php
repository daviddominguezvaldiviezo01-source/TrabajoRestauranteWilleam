<?php
session_start();
require_once dirname(dirname(__FILE__)) . '/conexion.php';

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
$resultadoAnuncios = mysqli_query($conexion, "SELECT * FROM anuncios WHERE activo = 1 ORDER BY creado_en DESC LIMIT 5");
$anuncios = [];
while ($anuncio = mysqli_fetch_assoc($resultadoAnuncios)) {
    $anuncios[] = $anuncio;
}

// Detectar imagen del hero
$heroImage = '';
$heroDir = __DIR__ . '/../images/hero/';
if (is_dir($heroDir)) {
    $files = scandir($heroDir);
    foreach ($files as $file) {
        if (in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif'])) {
            $heroImage = '/RESTAURANTE2/images/hero/' . $file;
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
<style>
* { margin:0; padding:0; box-sizing:border-box; }

body {
    background: #111;
    font-family: 'Segoe UI', sans-serif;
    color: #fff;
    overflow-x: hidden;
}

/* ── NAVBAR ── */
.navbar-top {
    background: #c8102e;
    padding: 0 40px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: sticky;
    top: 0;
    z-index: 200;
    box-shadow: 0 2px 12px rgba(0,0,0,.5);
}

.navbar-inner {
    max-width: 1300px;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.logo {
    font-size: 24px;
    font-weight: 900;
    color: #fff;
    text-decoration: none;
    letter-spacing: 1px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.logo img {
    height: 38px;
    object-fit: contain;
}

.nav-right {
    display: flex;
    align-items: center;
    gap: 14px;
}

.search-wrap {
    position: relative;
}

.search-wrap input {
    background: rgba(255,255,255,.15);
    border: 1.5px solid rgba(255,255,255,.3);
    border-radius: 30px;
    color: #fff;
    padding: 8px 18px 8px 38px;
    font-size: 14px;
    width: 220px;
    transition: .3s;
}

.search-wrap input::placeholder { color: rgba(255,255,255,.6); }
.search-wrap input:focus { outline: none; background: rgba(255,255,255,.25); width: 260px; }
.search-wrap i {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    color: rgba(255,255,255,.7);
    font-size: 13px;
}

@media (max-width: 768px) {
    .navbar-inner {
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 10px;
    }
    .nav-right {
        width: 100%;
        justify-content: flex-end;
        gap: 10px;
    }
    .search-wrap input {
        width: 100%;
        max-width: 260px;
    }
}

.btn-cart {
    background: #fff;
    color: #c8102e;
    border: none;
    border-radius: 30px;
    padding: 8px 20px;
    font-weight: 700;
    font-size: 14px;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 7px;
    position: relative;
    transition: .2s;
}
.btn-cart:hover { background: #f0f0f0; color: #c8102e; }

.cart-badge {
    position: absolute;
    top: -7px; right: -7px;
    background: #111;
    color: #fff;
    width: 22px; height: 22px;
    border-radius: 50%;
    font-size: 11px;
    font-weight: 900;
    display: flex; align-items: center; justify-content: center;
}

.btn-nav-user {
    color: #fff;
    border: 1.5px solid rgba(255,255,255,.5);
    border-radius: 30px;
    padding: 7px 16px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: .2s;
}
.btn-nav-user:hover { background: rgba(255,255,255,.15); color: #fff; }
.btn-nav-exit { border-color: rgba(255,100,100,.6); color: #ff8080; }
.btn-nav-exit:hover { background: rgba(200,16,46,.3); color: #fff; }

/* ── HERO BANNER ── */
.hero-banner {
    position: relative;
    height: 380px;
    overflow: hidden;
    background: #1a0a0a;
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
}
.hero-slide {
    position: absolute;
    inset: 0;
    opacity: 0;
    transition: opacity .8s ease;
    display: grid;
}
.hero-slide.active {
    opacity: 1;
    z-index: 1;
}
.hero-banner img {
    display: none;
}
.hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, rgba(0,0,0,.4) 0%, rgba(17,17,17,.8) 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 20px;
    z-index: 2;
}
.hero-overlay h1 {
    font-size: 3.2rem;
    font-weight: 900;
    color: #fff;
    text-shadow: 0 2px 10px rgba(0,0,0,.6);
    margin-bottom: 10px;
}
.hero-overlay p {
    font-size: 1.1rem;
    color: rgba(255,255,255,.8);
    margin-bottom: 24px;
}
.btn-hero {
    background: #c8102e;
    color: #fff;
    padding: 13px 36px;
    border-radius: 30px;
    font-weight: 700;
    font-size: 15px;
    text-decoration: none;
    transition: .2s;
    border: none;
}
.btn-hero:hover { background: #a50d26; color: #fff; transform: translateY(-2px); }

/* ── RESPONSIVIDAD HERO BANNER ── */
@media (max-width: 768px) {
    .hero-banner {
        height: 300px;
        background-attachment: scroll;
    }
    .hero-overlay h1 {
        font-size: 2.2rem;
        margin-bottom: 8px;
    }
    .hero-overlay p {
        font-size: 0.95rem;
        margin-bottom: 16px;
    }
    .btn-hero {
        padding: 11px 28px;
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    .hero-banner {
        height: 240px;
    }
    .hero-overlay {
        padding: 16px;
    }
    .hero-overlay h1 {
        font-size: 1.6rem;
        margin-bottom: 6px;
    }
    .hero-overlay p {
        font-size: 0.85rem;
        margin-bottom: 12px;
    }
    .btn-hero {
        padding: 9px 20px;
        font-size: 12px;
    }
}

@media (max-width: 320px) {
    .hero-banner {
        height: 200px;
    }
    .hero-overlay h1 {
        font-size: 1.3rem;
    }
    .hero-overlay p {
        font-size: 0.75rem;
    }
}

/* ── TABS DE CATEGORÍAS ── */
.cats-bar {
    background: #1a1a1a;
    border-bottom: 1px solid #2a2a2a;
    /* No sticky en la versión de cliente: mantener justo debajo del contenido superior */
    position: static;
    z-index: 100;
    overflow-x: auto;
    scrollbar-width: none;
    padding: 6px 0;
}

.cats-bar::-webkit-scrollbar { display: none; }

.promociones-section {
    padding: 18px 20px 12px; /* menos espacio inferior para acercar la barra de categorías */
    width: 100%;
    background: #0d0d0d;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.promociones-title {
    font-size: 2.2rem;
    font-weight: 900;
    color: #fff;
    margin-bottom: 28px;
    letter-spacing: 1px;
}

/* ── RESPONSIVIDAD SECCIÓN PROMOCIONES ── */
@media (max-width: 768px) {
    .promociones-section {
        padding: 16px 16px 10px;
    }
}

@media (max-width: 480px) {
    .promociones-section {
        padding: 12px 0 8px;
    }
    .promociones-title {
        padding: 0 20px;
    }
}
.promo-carousel {
    position: relative;
    overflow: hidden;
    width: 100%;
    max-width: none;
    padding: 0 18px;
}
.promo-track {
    display: flex;
    gap: 16px;
    justify-content: center;
    transition: transform .8s ease;
    will-change: transform;
}
.promo-card {
    flex: 0 0 min(46%, 420px);
    width: min(46%, 420px);
    max-width: 100%;
    flex-shrink: 0;
    position: relative;
    aspect-ratio: 4 / 3;
    overflow: hidden;
    border-radius: 16px;
    box-shadow: 0 8px 25px rgba(0,0,0,.3);
    min-height: 260px;
    max-height: 360px;
}
.promo-card a {
    display: block;
    width: 100%;
    height: 100%;
}
.promo-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform .4s ease;
}
.promo-card:hover img {
    transform: scale(1.05);
}
.promo-dots {
    display: flex;
    justify-content: center;
    gap: 10px;
    padding: 24px 0 0;
}
.promo-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255,255,255,.2);
    cursor: pointer;
    transition: transform .2s, background .2s;
}
.promo-dot.active {
    background: #c8102e;
    transform: scale(1.3);
}

/* ── RESPONSIVIDAD CARRUSEL Y PROMOCIONES ── */
@media (min-width: 1200px) {
    .promo-card {
        max-height: 55vh;
    }
}

@media (min-width: 768px) and (max-width: 1199px) {
    .promo-card {
        min-height: 320px;
        max-height: 39vh;
    }
    .promociones-title {
        font-size: 2rem;
    }
    .promo-dots {
        gap: 8px;
    }
}

@media (max-width: 767px) {
    .promo-card {
        min-width: 100%;
        max-width: 100%;
        min-height: 280px;
        max-height: 45vh;
    }
    .promociones-title {
        font-size: 1.5rem;
        margin-bottom: 20px;
    }
    .promo-track {
        gap: 12px;
    }
    .promo-dots {
        gap: 6px;
        padding: 16px 0 0;
    }
    .promo-dot {
        width: 10px;
        height: 10px;
    }
}

@media (max-width: 480px) {
    .promo-card {
        min-height: 220px;
        max-height: 40vh;
        border-radius: 8px;
    }
    .promociones-title {
        font-size: 1.2rem;
        margin-bottom: 16px;
    }
    .promo-track {
        gap: 8px;
    }
    .promo-carousel {
        margin: 0 -20px;
        width: calc(100% + 40px);
    }
}
.cats-bar::-webkit-scrollbar { display: none; }

.cats-inner {
    display: flex;
    gap: 0;
    max-width: 1300px;
    margin: 0 auto;
    padding: 0 20px;
}

.cat-tab {
    color: rgba(255,255,255,.55);
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    padding: 16px 22px;
    white-space: nowrap;
    border-bottom: 3px solid transparent;
    transition: .2s;
    letter-spacing: .3px;
}
.cat-tab:hover { color: #fff; }
.cat-tab.active {
    color: #fff;
    border-bottom-color: #c8102e;
}

/* ── RESPONSIVIDAD CATEGORÍAS ── */
@media (max-width: 768px) {
    .cats-inner {
        padding: 0 16px;
    }
    .cat-tab {
        padding: 14px 18px;
        font-size: 13px;
    }
}

@media (max-width: 480px) {
    .cats-inner {
        padding: 0 12px;
    }
    .cat-tab {
        padding: 12px 14px;
        font-size: 12px;
    }
}

/* ── CONTENIDO PRINCIPAL ── */
.page-wrap {
    max-width: 1300px;
    margin: 0 auto;
    padding: 40px 20px 60px;
}

/* ── SECCIÓN TÍTULO ── */
.sec-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 28px;
}
.sec-header h2 {
    font-size: 1.5rem;
    font-weight: 800;
    color: #fff;
}
.sec-divider {
    flex: 1;
    height: 1px;
    background: #2a2a2a;
}

/* ── GRID DE PRODUCTOS ── */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 50px;
}

/* ── RESPONSIVIDAD CONTENIDO PRINCIPAL ── */
@media (max-width: 1024px) {
    .page-wrap {
        padding: 35px 20px 50px;
    }
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 18px;
        margin-bottom: 40px;
    }
}

@media (max-width: 768px) {
    .page-wrap {
        padding: 30px 16px 45px;
    }
    .sec-header h2 {
        font-size: 1.3rem;
    }
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 14px;
        margin-bottom: 35px;
    }
    .prod-img-wrap {
        height: 160px;
    }
    .prod-body {
        padding: 12px;
    }
    .prod-name {
        font-size: 0.95rem;
    }
    .prod-desc {
        font-size: 0.75rem;
    }
}

@media (max-width: 480px) {
    .page-wrap {
        padding: 20px 12px 35px;
    }
    .sec-header {
        margin-bottom: 20px;
    }
    .sec-header h2 {
        font-size: 1.1rem;
    }
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 10px;
        margin-bottom: 30px;
    }
    .prod-card {
        border-radius: 8px;
    }
    .prod-img-wrap {
        height: 140px;
    }
    .prod-body {
        padding: 10px;
    }
    .prod-cat {
        font-size: 9px;
        margin-bottom: 4px;
    }
    .prod-name {
        font-size: 0.85rem;
        margin-bottom: 4px;
    }
    .prod-desc {
        font-size: 0.7rem;
        margin-bottom: 8px;
        -webkit-line-clamp: 1;
        line-clamp: 1;
    }
    .prod-price {
        font-size: 1rem;
    }
    .btn-add {
        padding: 6px 10px;
        font-size: 11px;
    }
}

/* ── TARJETA ── */
.prod-card {
    background: #1e1e1e;
    border-radius: 14px;
    overflow: hidden;
    transition: transform .25s, box-shadow .25s;
    display: flex;
    flex-direction: column;
    border: 1px solid #2a2a2a;
}
.prod-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(0,0,0,.5);
    border-color: #3a3a3a;
}

.prod-img-wrap {
    position: relative;
    height: 200px;
    background: #2a2a2a;
    overflow: hidden;
}
.prod-img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform .4s;
}
.prod-card:hover .prod-img-wrap img { transform: scale(1.06); }

.prod-img-fallback {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #2a2a2a;
    color: rgba(255,255,255,.3);
    font-size: 3rem;
}

.prod-badge {
    position: absolute;
    top: 10px; left: 10px;
    background: #c8102e;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
    letter-spacing: .5px;
}

.prod-body {
    padding: 16px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.prod-cat {
    font-size: 11px;
    font-weight: 700;
    color: #c8102e;
    text-transform: uppercase;
    letter-spacing: .8px;
    margin-bottom: 6px;
}

.prod-name {
    font-size: 1rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 6px;
    line-height: 1.3;
}

.prod-desc {
    font-size: 0.82rem;
    color: rgba(255,255,255,.45);
    margin-bottom: 14px;
    flex: 1;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.prod-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.prod-price {
    font-size: 1.3rem;
    font-weight: 900;
    color: #fff;
}
.prod-price span {
    font-size: 0.85rem;
    font-weight: 600;
    color: rgba(255,255,255,.5);
    margin-right: 2px;
}

.btn-add {
    background: #c8102e;
    color: #fff;
    border: none;
    border-radius: 30px;
    padding: 9px 18px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: .2s;
    white-space: nowrap;
}
.btn-add:hover { background: #a50d26; color: #fff; }
.btn-add:disabled, .btn-add.disabled {
    background: #333;
    color: rgba(255,255,255,.3);
    cursor: not-allowed;
}

/* ── EMPTY STATE ── */
.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 80px 20px;
    color: rgba(255,255,255,.3);
}
.empty-state i { font-size: 4rem; margin-bottom: 16px; display: block; }
.empty-state h3 { font-size: 1.2rem; margin-bottom: 16px; }

/* ── TOAST ── */
.toast-msg {
    position: fixed;
    top: 90px; right: 20px;
    background: rgba(200, 16, 46, 0.98);
    border-left: 4px solid #ff2a4f;
    color: #fff;
    padding: 16px 20px;
    border-radius: 16px;
    box-shadow: 0 18px 45px rgba(0,0,0,.35);
    display: flex;
    align-items: center;
    gap: 14px;
    font-size: 14px;
    z-index: 9999;
    max-width: 360px;
    width: min(100%, 360px);
    overflow: hidden;
    backdrop-filter: blur(8px);
    animation: slideIn .28s ease;
}
.toast-msg .toast-icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(255,255,255,.12);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.toast-msg .toast-icon i {
    font-size: 18px;
    color: #fff;
}
.toast-msg .toast-content {
    display: flex;
    flex-direction: column;
    gap: 3px;
    line-height: 1.3;
}
.toast-msg .toast-title {
    font-weight: 800;
    font-size: 15px;
}
.toast-msg .toast-text {
    opacity: .92;
    font-size: 13px;
}
.toast-msg.error { border-left-color: #ff6b6b; background: rgba(192, 23, 50, 0.98); }
.toast-msg.error .toast-icon { background: rgba(255,255,255,.12); }
.toast-msg.error .toast-icon i { color: #ffe5e5; }
.toast-msg.success { border-left-color: #ff2a4f; background: rgba(200, 16, 46, 0.98); }
.toast-msg.success .toast-icon i { color: #fff; }
@keyframes slideIn { from { opacity:0; transform:translateX(24px); } to { opacity:1; transform:translateX(0); } }

/* ── FOOTER ── */
footer {
    background: #0d0d0d;
    border-top: 1px solid #222;
    padding: 40px 20px 20px;
    color: rgba(255,255,255,.5);
}
.footer-inner {
    max-width: 1300px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 30px;
    margin-bottom: 30px;
}
.footer-inner h5 { color: #fff; font-weight: 700; margin-bottom: 12px; font-size: 14px; }
.footer-inner a { color: rgba(255,255,255,.45); text-decoration: none; font-size: 13px; display: block; margin-bottom: 8px; transition: .2s; }
.footer-inner a:hover { color: #c8102e; }
.footer-bottom {
    max-width: 1300px;
    margin: 0 auto;
    border-top: 1px solid #222;
    padding-top: 18px;
    text-align: center;
    font-size: 13px;
}

/* ── RESPONSIVE ── */
@media (max-width: 768px) {
    .navbar-top { padding: 0 16px; }
    .navbar-inner { gap: 10px; flex-wrap: wrap; }
    .hero-overlay h1 { font-size: 2rem; }
    .search-wrap input { width: 140px; }
    .products-grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 14px; }
    .prod-img-wrap { height: 150px; }
}
</style>
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
            <input type="text" id="searchInput" placeholder="Buscar en el menú..." onkeyup="filtrarProductos()">
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
<div class="hero-banner" style="<?php if(!empty($heroImage)): ?>background-image: url('<?php echo htmlspecialchars($heroImage); ?>');<?php endif; ?>">
    <div class="hero-overlay">
        <h1>🔥 Bienvenido a Brisamar</h1>
        <p>Los mejores sabores del mar, directo a tu mesa</p>
        <a href="#menu" class="btn-hero">Ver Menú Completo</a>
    </div>
</div>

<?php if(count($anuncios) > 0): ?>
<section class="promociones-section">
    <h2 class="promociones-title">PROMOCIONES</h2>
    <div class="promo-carousel">
        <div class="promo-track">
            <?php foreach($anuncios as $anuncio): ?>
            <article class="promo-card">
                <?php if(!empty($anuncio['enlace'])): ?>
                    <a href="<?php echo htmlspecialchars($anuncio['enlace']); ?>">
                        <img src="/RESTAURANTE2/<?php echo htmlspecialchars($anuncio['imagen']); ?>" alt="Promoción">
                    </a>
                <?php else: ?>
                    <img src="/RESTAURANTE2/<?php echo htmlspecialchars($anuncio['imagen']); ?>" alt="Promoción">
                <?php endif; ?>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="promo-dots">
        <?php foreach($anuncios as $index => $anuncio): ?>
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
        while($cat = mysqli_fetch_assoc($resCat2)):
        ?>
        <a href="?categoria=<?php echo $cat['id_categoria']; ?>"
           class="cat-tab <?php echo ($id_categoria == $cat['id_categoria']) ? 'active' : ''; ?>">
            <?php echo htmlspecialchars($cat['nombre']); ?>
        </a>
        <?php endwhile; ?>
    </div>
</div>

<!-- TOAST -->
<?php if(isset($_SESSION['mensaje'])): ?>
<?php $toastType = $_SESSION['mensaje_tipo'] ?? 'success'; ?>
<div class="toast-msg <?php echo $toastType; ?>" id="toastMsg">
    <div class="toast-icon">
        <i class="fas <?php echo $toastType === 'error' ? 'fa-triangle-exclamation' : 'fa-check-circle'; ?>"></i>
    </div>
    <div class="toast-content">
        <span class="toast-title"><?php echo $toastType === 'error' ? 'Error' : '¡Agregado!'; ?></span>
        <span class="toast-text"><?php echo htmlspecialchars($_SESSION['mensaje']); unset($_SESSION['mensaje'], $_SESSION['mensaje_tipo']); ?></span>
    </div>
</div>
<script>setTimeout(()=>{ const t=document.getElementById('toastMsg'); if(t) t.style.display='none'; }, 3000);</script>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function filtrarProductos() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('.producto-item').forEach(el => {
        const n = el.querySelector('[data-nombre]')?.getAttribute('data-nombre') || '';
        el.style.display = n.includes(q) ? '' : 'none';
    });
}

(function() {
    const slides = document.querySelectorAll('.hero-slide');
    if (!slides.length || slides.length === 1) return;
    let activeIndex = 0;
    setInterval(() => {
        slides[activeIndex].classList.remove('active');
        activeIndex = (activeIndex + 1) % slides.length;
        slides[activeIndex].classList.add('active');
    }, 5000);
})();

(function() {
    const track = document.querySelector('.promo-track');
    const dots = document.querySelectorAll('.promo-dot');
    const cards = document.querySelectorAll('.promo-card');
    if (!track || !dots.length || !cards.length) return;

    let activeIndex = 0;
    const totalCards = cards.length;
    let autoScrollTimer = null;

    const updatePosition = () => {
        const cardWidth = 50; // Cada card es 50% del viewport
        const gapOffset = (16 / window.innerWidth) * 100; // Convertir gap a porcentaje
        const offset = activeIndex * (cardWidth + (gapOffset / totalCards));
        track.style.transform = `translateX(-${offset}%)`;
        dots.forEach((dot, index) => dot.classList.toggle('active', index === activeIndex));
    };

    const startAutoScroll = () => {
        autoScrollTimer = setInterval(() => {
            activeIndex = (activeIndex + 1) % totalCards;
            updatePosition();
        }, 4500);
    };

    dots.forEach(dot => {
        dot.addEventListener('click', () => {
            activeIndex = Number(dot.dataset.index);
            updatePosition();
            clearInterval(autoScrollTimer);
            startAutoScroll();
        });
    });

    startAutoScroll();

    const carousel = document.querySelector('.promo-carousel');
    carousel.addEventListener('mouseenter', () => clearInterval(autoScrollTimer));
    carousel.addEventListener('mouseleave', () => startAutoScroll());

    window.addEventListener('resize', updatePosition);
    updatePosition();
})();
</script>
</body>
</html>
