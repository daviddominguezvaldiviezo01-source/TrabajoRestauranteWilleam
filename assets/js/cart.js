/**
 * ============================================================
 * ARCHIVO: assets/js/cart.js
 * ============================================================
 * DESCRIPCIÓN: Funcionalidad del carrito de compras
 * Gestión de items, búsqueda, filtrado
 * ============================================================
 */

/**
 * Filtra productos por nombre en tiempo real
 * Busca en los campos data-nombre de los productos
 * 
 * USO EN HTML:
 * <input type="text" id="searchInput" onkeyup="filtrarProductos()">
 * <div class="producto-item" data-nombre="ceviche">...</div>
 */
function filtrarProductos() {
    // Obtener valor de búsqueda
    const searchInput = document.getElementById('searchInput');
    const query = searchInput ? searchInput.value.toLowerCase() : '';
    
    // Obtener todos los productos
    const productos = document.querySelectorAll('.producto-item');
    
    // Filtrar cada producto
    productos.forEach(producto => {
        // Obtener nombre del producto
        const nombre = producto.querySelector('[data-nombre]');
        if (!nombre) return;
        
        const nombreText = nombre.getAttribute('data-nombre') || '';
        
        // Mostrar u ocultar según coincida
        if (nombreText.includes(query)) {
            producto.style.display = '';
        } else {
            producto.style.display = 'none';
        }
    });
}

/**
 * Agrega un producto al carrito (redirige a agregar_carrito.php)
 * 
 * @param {number} idProducto - ID del producto
 */
function agregarAlCarrito(idProducto) {
    window.location.href = 'agregar_carrito.php?id=' + encodeURIComponent(idProducto);
}

/**
 * Elimina un producto del carrito (redirige a eliminar_carrito.php)
 * 
 * @param {number} idProducto - ID del producto
 */
function eliminarDelCarrito(idProducto) {
    if (confirm('¿Estás seguro de que deseas eliminar este producto?')) {
        window.location.href = 'eliminar_carrito.php?id=' + encodeURIComponent(idProducto);
    }
}

/**
 * Recalcula el total del carrito dinámicamente
 * Suma todos los subtotales
 */
function recalcularTotal() {
    const items = document.querySelectorAll('.cart-table tbody tr');
    let total = 0;
    
    items.forEach(item => {
        const precioEl = item.querySelector('.price-cell');
        const cantidadEl = item.querySelector('.qty-badge');
        
        if (precioEl && cantidadEl) {
            // Obtener precio (remover S/.)
            const precio = parseFloat(
                precioEl.textContent.replace('S/.', '').replace(',', '')
            );
            
            // Obtener cantidad
            const cantidad = parseInt(cantidadEl.textContent);
            
            // Sumar al total
            total += precio * cantidad;
        }
    });
    
    // Actualizar elemento total
    const totalEl = document.querySelector('.total-price');
    if (totalEl) {
        totalEl.textContent = formatPrice(total);
    }
}

/**
 * Cambia la cantidad de un producto en el carrito
 * 
 * @param {number} idProducto - ID del producto
 * @param {number} cantidad - Nueva cantidad
 */
function cambiarCantidad(idProducto, cantidad) {
    // Validar que sea un número positivo
    cantidad = parseInt(cantidad);
    
    if (cantidad < 1) {
        eliminarDelCarrito(idProducto);
        return;
    }
    
    if (cantidad > 999) {
        showToast('Cantidad máxima: 999', 'warning');
        return;
    }
    
    // Aquí iría la lógica para actualizar en servidor
    // Por ahora solo recalculamos el total
    recalcularTotal();
}

/**
 * Limpia el carrito completamente
 */
function limpiarCarrito() {
    if (confirm('¿Estás seguro de que deseas vaciar el carrito?')) {
        window.location.href = 'eliminar_carrito.php?limpiar=1';
    }
}

/**
 * Procede al checkout
 */
function irAlCheckout() {
    window.location.href = 'checkout.php';
}

/**
 * Continúa comprando
 */
function continuarComprando() {
    window.location.href = 'index.php';
}
