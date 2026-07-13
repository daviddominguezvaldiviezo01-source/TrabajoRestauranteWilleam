<?php
header('Content-Type: text/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$today = date('Y-m-d');
?>
<!-- ============================================================
     ARCHIVO: sitemap.xml (generado dinámicamente)
     ============================================================
     DESCRIPCIÓN: Mapa del sitio para SEO
     Ayuda a los motores de búsqueda a indexar las páginas
     https://www.sitemaps.org/
     ============================================================
-->

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:mobile="http://www.google.com/schemas/sitemap-mobile/1.0">
    
    <!-- ──────────────────────────────────────────────────────
         PÁGINA PRINCIPAL
         ────────────────────────────────────────────────────── -->
    
    <url>
        <loc>http://localhost/RESTAURANTE2/cliente/index.php</loc>
        <lastmod><?php echo $today; ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
        <mobile:mobile/>
    </url>
    
    <!-- ──────────────────────────────────────────────────────
         PÁGINA DE LOGIN/REGISTRO
         ────────────────────────────────────────────────────── -->
    
    <url>
        <loc>http://localhost/RESTAURANTE2/cliente/login.php</loc>
        <lastmod><?php echo $today; ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
        <mobile:mobile/>
    </url>
    
    <!-- ──────────────────────────────────────────────────────
         PÁGINA DE CARRITO
         ────────────────────────────────────────────────────── -->
    
    <url>
        <loc>http://localhost/RESTAURANTE2/cliente/carrito.php</loc>
        <lastmod><?php echo $today; ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
        <mobile:mobile/>
    </url>
    
    <!-- ──────────────────────────────────────────────────────
         PÁGINA DE CHECKOUT
         ────────────────────────────────────────────────────── -->
    
    <url>
        <loc>http://localhost/RESTAURANTE2/cliente/checkout.php</loc>
        <lastmod><?php echo $today; ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.85</priority>
        <mobile:mobile/>
    </url>
    
    <!-- ──────────────────────────────────────────────────────
         NOTA: En producción, agregar URLs dinámicas de:
         - Cada categoría (/cliente/index.php?categoria=X)
         - Cada producto (si tienes páginas individuales)
         
         Puedes generar automáticamente con PHP usando
         la información de la base de datos
         ────────────────────────────────────────────────────── -->

</urlset>
