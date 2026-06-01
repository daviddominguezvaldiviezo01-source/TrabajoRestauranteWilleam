<?php
/**
 * ============================================================
 * ARCHIVO: cliente/partials/product-card.php
 * ============================================================
 * DESCRIPCIÓN: Componente reutilizable para tarjeta de producto
 * Se incluye dentro de un loop de productos
 * 
 * VARIABLE REQUERIDA: $fila (array con datos del producto)
 * ============================================================
 */

// Validar que exista la variable $fila
if (!isset($fila) || !is_array($fila)) {
    return;
}

// Sanitizar datos del producto
$id = intval($fila['id_producto'] ?? 0);
$nombre = sanitizar($fila['nombre'] ?? 'Producto');
$descripcion = sanitizar($fila['descripcion'] ?? '');
$precio = floatval($fila['precio'] ?? 0);
$stock = intval($fila['stock'] ?? 0);
$imagen = sanitizar($fila['imagen'] ?? '');
$categoria = sanitizar($fila['nombre_categoria'] ?? 'General');
$disponible = intval($fila['disponible'] ?? 0);
$favorito = intval($fila['favorito'] ?? 0);
$estrella = intval($fila['estrella'] ?? 0);
?>

<!-- Tarjeta de producto con información completa -->
<article class="prod-card producto-item" itemscope itemtype="https://schema.org/Product">
    
    <!-- Contenedor de imagen con fallback -->
    <div class="prod-img-wrap">
        <?php if (!empty($imagen)): ?>
            <img 
                src="<?php echo $imagen; ?>" 
                alt="<?php echo $nombre; ?>"
                loading="lazy"
                itemprop="image"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
            >
            <div class="prod-img-fallback" style="display:none;">
                <i class="fas fa-utensils" aria-hidden="true"></i>
            </div>
        <?php else: ?>
            <div class="prod-img-fallback">
                <i class="fas fa-utensils" aria-hidden="true"></i>
            </div>
        <?php endif; ?>
        
        <!-- Badge de favorito o estrella -->
        <?php if ($favorito): ?>
            <span class="prod-badge">⭐ Favorito</span>
        <?php elseif ($estrella): ?>
            <span class="prod-badge">🌟 Estrella</span>
        <?php endif; ?>
    </div>
    
    <!-- Información del producto -->
    <div class="prod-body">
        <!-- Categoría -->
        <div class="prod-cat" itemprop="category">
            <?php echo $categoria; ?>
        </div>
        
        <!-- Nombre del producto -->
        <h3 class="prod-name" data-nombre="<?php echo strtolower($nombre); ?>" itemprop="name">
            <?php echo $nombre; ?>
        </h3>
        
        <!-- Descripción -->
        <p class="prod-desc" itemprop="description">
            <?php echo $descripcion; ?>
        </p>
        
        <!-- Pie: Precio y botón -->
        <div class="prod-footer">
            <!-- Precio -->
            <div class="prod-price" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                <span>S/.</span><span itemprop="price"><?php echo number_format($precio, 2); ?></span>
                <meta itemprop="priceCurrency" content="PEN">
                <meta itemprop="availability" content="<?php echo $stock > 0 ? 'InStock' : 'OutOfStock'; ?>">
            </div>
            
            <!-- Botón agregar o agotado -->
            <?php if ($stock > 0 && $disponible): ?>
                <a 
                    href="agregar_carrito.php?id=<?php echo $id; ?>" 
                    class="btn-add"
                    title="Agregar <?php echo $nombre; ?> al carrito"
                >
                    <i class="fas fa-plus" aria-hidden="true"></i>
                    <span class="btn-text">Agregar</span>
                </a>
            <?php else: ?>
                <button class="btn-add disabled" disabled aria-label="Producto agotado">
                    <i class="fas fa-ban" aria-hidden="true"></i>
                    <span class="btn-text">Agotado</span>
                </button>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Datos estructurados para SEO -->
    <meta itemprop="brand" content="<?php echo SITE_NAME; ?>">
    <meta itemprop="url" content="<?php echo SITE_URL; ?>/cliente/index.php?id=<?php echo $id; ?>">
</article>
