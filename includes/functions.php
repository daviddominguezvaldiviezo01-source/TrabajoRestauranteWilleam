<?php
/**
 * ============================================================
 * ARCHIVO: includes/functions.php
 * ============================================================
 * DESCRIPCIÓN: Funciones generales y comunes del sistema
 * Utilidades para productos, carrito, usuarios, etc.
 * ============================================================
 */

// Incluir configuración
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// ──────────────────────────────────────────────────────────
// OBTENER PRODUCTO POR ID
// ──────────────────────────────────────────────────────────

/**
 * Obtiene los datos de un producto específico de la base de datos
 * 
 * @global mysqli $conexion - Conexión a la base de datos
 * @param int $id_producto - ID del producto
 * @return array|null Array con datos del producto o null si no existe
 * 
 * EJEMPLO:
 * $producto = obtener_producto(5);
 * if ($producto) {
 *     echo 'Nombre: ' . $producto['nombre'];
 * }
 */
function obtener_producto($id_producto) {
    global $conexion;
    
    // Validar que sea un número entero
    $id_producto = (int)$id_producto;
    
    // Usar prepared statement para prevenir SQL injection
    $stmt = mysqli_prepare($conexion, 
        "SELECT * FROM productos WHERE id_producto = ?");
    
    // Vincular parámetro
    mysqli_stmt_bind_param($stmt, "i", $id_producto);
    
    // Ejecutar
    mysqli_stmt_execute($stmt);
    
    // Obtener resultado
    $resultado = mysqli_stmt_get_result($stmt);
    $producto = mysqli_fetch_assoc($resultado);
    
    // Cerrar statement
    mysqli_stmt_close($stmt);
    
    return $producto;
}

// ──────────────────────────────────────────────────────────
// OBTENER TODOS LOS PRODUCTOS
// ──────────────────────────────────────────────────────────

/**
 * Obtiene todos los productos disponibles
 * 
 * @global mysqli $conexion - Conexión a la base de datos
 * @param bool $solo_disponibles - Si true, solo obtiene productos disponibles
 * @param int $limite - Cantidad máxima de productos a obtener
 * @return array Array de productos
 * 
 * EJEMPLO:
 * $productos = obtener_productos(true, 10);
 * foreach ($productos as $prod) {
 *     echo $prod['nombre'] . ' - S/.' . $prod['precio'];
 * }
 */
function obtener_productos($solo_disponibles = true, $limite = null) {
    global $conexion;
    
    // Construir query
    $sql = "SELECT p.*, c.nombre AS nombre_categoria 
            FROM productos p 
            LEFT JOIN categorias c ON p.id_categoria = c.id_categoria";
    
    // Si solo disponibles
    if ($solo_disponibles) {
        $sql .= " WHERE p.disponible = 1";
    }
    
    // Ordenar por ID descendente
    $sql .= " ORDER BY p.id_producto DESC";
    
    // Si hay límite
    if ($limite !== null) {
        $limite = (int)$limite;
        $sql .= " LIMIT " . $limite;
    }
    
    // Ejecutar query
    $resultado = mysqli_query($conexion, $sql);
    
    // Obtener todos los resultados como array
    $productos = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $productos[] = $fila;
    }
    
    return $productos;
}

// ──────────────────────────────────────────────────────────
// OBTENER PRODUCTOS POR CATEGORÍA
// ──────────────────────────────────────────────────────────

/**
 * Obtiene todos los productos de una categoría específica
 * 
 * @global mysqli $conexion - Conexión a la base de datos
 * @param int $id_categoria - ID de la categoría
 * @return array Array de productos en esa categoría
 * 
 * EJEMPLO:
 * $productos_bebidas = obtener_productos_por_categoria(3);
 */
function obtener_productos_por_categoria($id_categoria) {
    global $conexion;
    
    // Validar que sea un número entero
    $id_categoria = (int)$id_categoria;
    
    // Usar prepared statement
    $stmt = mysqli_prepare($conexion,
        "SELECT p.*, c.nombre AS nombre_categoria 
         FROM productos p 
         LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
         WHERE p.id_categoria = ? AND p.disponible = 1 
         ORDER BY p.nombre");
    
    // Vincular parámetro
    mysqli_stmt_bind_param($stmt, "i", $id_categoria);
    
    // Ejecutar
    mysqli_stmt_execute($stmt);
    
    // Obtener resultados
    $resultado = mysqli_stmt_get_result($stmt);
    $productos = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    
    // Cerrar statement
    mysqli_stmt_close($stmt);
    
    return $productos;
}

// ──────────────────────────────────────────────────────────
// OBTENER CATEGORÍAS
// ──────────────────────────────────────────────────────────

/**
 * Obtiene todas las categorías disponibles
 * 
 * @global mysqli $conexion - Conexión a la base de datos
 * @return array Array de categorías
 * 
 * EJEMPLO:
 * $categorias = obtener_categorias();
 */
function obtener_categorias() {
    global $conexion;
    
    $sql = "SELECT * FROM categorias ORDER BY nombre ASC";
    
    $resultado = mysqli_query($conexion, $sql);
    
    $categorias = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $categorias[] = $fila;
    }
    
    return $categorias;
}

// ──────────────────────────────────────────────────────────
// OBTENER USUARIO POR ID
// ──────────────────────────────────────────────────────────

/**
 * Obtiene los datos completos de un usuario
 * 
 * @global mysqli $conexion - Conexión a la base de datos
 * @param int $id_usuario - ID del usuario
 * @return array|null Datos del usuario o null
 * 
 * EJEMPLO:
 * $usuario = obtener_usuario(1);
 * if ($usuario) {
 *     echo 'Nombre: ' . $usuario['nombre'];
 * }
 */
function obtener_usuario($id_usuario) {
    global $conexion;
    
    $id_usuario = (int)$id_usuario;
    
    $stmt = mysqli_prepare($conexion,
        "SELECT * FROM usuarios WHERE id_usuario = ?");
    
    mysqli_stmt_bind_param($stmt, "i", $id_usuario);
    mysqli_stmt_execute($stmt);
    
    $resultado = mysqli_stmt_get_result($stmt);
    $usuario = mysqli_fetch_assoc($resultado);
    
    mysqli_stmt_close($stmt);
    
    return $usuario;
}

// ──────────────────────────────────────────────────────────
// OBTENER USUARIO POR EMAIL
// ──────────────────────────────────────────────────────────

/**
 * Busca un usuario por su email
 * 
 * @global mysqli $conexion - Conexión a la base de datos
 * @param string $email - Email del usuario
 * @return array|null Datos del usuario o null
 * 
 * EJEMPLO:
 * $usuario = obtener_usuario_por_email('juan@example.com');
 */
function obtener_usuario_por_email($email) {
    global $conexion;
    
    $email = sanitizar($email);
    
    $stmt = mysqli_prepare($conexion,
        "SELECT * FROM usuarios WHERE email = ?");
    
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    
    $resultado = mysqli_stmt_get_result($stmt);
    $usuario = mysqli_fetch_assoc($resultado);
    
    mysqli_stmt_close($stmt);
    
    return $usuario;
}

// ──────────────────────────────────────────────────────────
// OBTENER PEDIDOS ASIGNADOS PARA DELIVERY
// ──────────────────────────────────────────────────────────

/**
 * Obtiene los pedidos asignados a un repartidor.
 *
 * @global mysqli $conexion - Conexión a la base de datos
 * @param int $id_repartidor - ID del repartidor
 * @param string $estado - Filtro de estado: 'ir a recoger', 'en camino', 'entregado', 'cancelado' o 'all'
 * @return array Lista de pedidos asignados
 */
function obtener_pedidos_delivery($id_repartidor, $estado = 'ir a recoger') {
    global $conexion;

    $id_repartidor = (int) $id_repartidor;
    $estados_validos = ['ir a recoger', 'en camino', 'entregado', 'cancelado', 'all'];
    if (!in_array($estado, $estados_validos, true)) {
        $estado = 'ir a recoger';
    }

    // Verificar si existen columnas de cliente invitado en la tabla pedidos
    $has_guest_columns = false;
    $resCols = mysqli_query($conexion, "SHOW COLUMNS FROM pedidos LIKE 'nombre_cliente'");
    if ($resCols && mysqli_num_rows($resCols) > 0) {
        $has_guest_columns = true;
    }

    if ($has_guest_columns) {
        $sql = "SELECT p.*, COALESCE(u.nombre, p.nombre_cliente) AS cliente, COALESCE(u.email, p.email_cliente) AS email, COALESCE(u.telefono, p.telefono_cliente) AS telefono,\n"
             . "            d.direccion, d.referencia, pg.metodo, pg.estado AS estado_pago\n"
             . "     FROM pedidos p\n"
             . "     LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario\n"
             . "     LEFT JOIN direcciones d ON p.id_direccion = d.id_direccion\n"
             . "     LEFT JOIN pagos pg ON p.id_pedido = pg.id_pedido\n"
             . "     WHERE p.id_repartidor = ?";
    } else {
        $sql = "SELECT p.*, u.nombre AS cliente, u.email, u.telefono,\n"
             . "            d.direccion, d.referencia, pg.metodo, pg.estado AS estado_pago\n"
             . "     FROM pedidos p\n"
             . "     LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario\n"
             . "     LEFT JOIN direcciones d ON p.id_direccion = d.id_direccion\n"
             . "     LEFT JOIN pagos pg ON p.id_pedido = pg.id_pedido\n"
             . "     WHERE p.id_repartidor = ?";
    }

    if ($estado !== 'all') {
        $sql .= " AND p.estado = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, 'is', $id_repartidor, $estado);
    } else {
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id_repartidor);
    }

    if (!$stmt) {
        return [];
    }

    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $pedidos = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    return $pedidos;
}

// ──────────────────────────────────────────────────────────
// OBTENER PEDIDOS DE UN CLIENTE
// ──────────────────────────────────────────────────────────

/**
 * Obtiene los pedidos realizados por un cliente.
 *
 * @global mysqli $conexion - Conexión a la base de datos
 * @param int $id_usuario - ID del usuario cliente
 * @return array Lista de pedidos del cliente
 */
function obtener_pedidos_cliente($id_usuario) {
    global $conexion;

    $id_usuario = (int) $id_usuario;
    $stmt = mysqli_prepare($conexion,
        "SELECT p.*, d.direccion, d.referencia, pg.metodo, pg.estado AS estado_pago
         FROM pedidos p
         LEFT JOIN direcciones d ON p.id_direccion = d.id_direccion
         LEFT JOIN pagos pg ON p.id_pedido = pg.id_pedido
         WHERE p.id_usuario = ?
         ORDER BY p.id_pedido DESC");

    if (!$stmt) {
        return [];
    }

    mysqli_stmt_bind_param($stmt, 'i', $id_usuario);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $pedidos = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    return $pedidos;
}

// ──────────────────────────────────────────────────────────
// OBTENER ITEMS DE UN PEDIDO
// ──────────────────────────────────────────────────────────

/**
 * Obtiene los artículos detallados de un pedido.
 *
 * @global mysqli $conexion - Conexión a la base de datos
 * @param int $id_pedido - ID del pedido
 * @return array Lista de productos en el pedido
 */
function obtener_items_pedido($id_pedido) {
    global $conexion;

    $id_pedido = (int) $id_pedido;
    $stmt = mysqli_prepare($conexion,
        "SELECT dp.cantidad, dp.subtotal, pr.nombre, pr.imagen 
         FROM detalle_pedido dp 
         LEFT JOIN productos pr ON dp.id_producto = pr.id_producto 
         WHERE dp.id_pedido = ?");

    if (!$stmt) {
        return [];
    }

    mysqli_stmt_bind_param($stmt, 'i', $id_pedido);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $items = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    return $items;
}

// ──────────────────────────────────────────────────────────
// CALCULAR TOTAL DEL CARRITO
// ──────────────────────────────────────────────────────────

/**
 * Calcula el total del carrito incluyendo impuestos y envío
 * 
 * @global mysqli $conexion - Conexión a la base de datos
 * @return array Contiene: subtotal, impuesto, envio, total
 * 
 * EJEMPLO:
 * $totales = calcular_total_carrito();
 * echo 'Total: S/.' . $totales['total'];
 */
function calcular_total_carrito() {
    global $conexion;
    
    $subtotal = 0;
    
    // Recorrer items del carrito
    if (isset($_SESSION['carrito']) && count($_SESSION['carrito']) > 0) {
        foreach ($_SESSION['carrito'] as $id_producto => $cantidad) {
            $producto = obtener_producto($id_producto);
            if ($producto) {
                $subtotal += $producto['precio'] * $cantidad;
            }
        }
    }
    
    // Calcular impuesto
    $impuesto = $subtotal * TAX_RATE;
    
    // Agregar envío
    $total = $subtotal + $impuesto + DELIVERY_COST;
    
    return [
        'subtotal' => round($subtotal, 2),
        'impuesto' => round($impuesto, 2),
        'envio' => DELIVERY_COST,
        'total' => round($total, 2)
    ];
}

// ──────────────────────────────────────────────────────────
// CONTAR ITEMS EN CARRITO
// ──────────────────────────────────────────────────────────

/**
 * Cuenta la cantidad total de artículos en el carrito
 * 
 * @return int Cantidad total de artículos
 * 
 * EJEMPLO:
 * $cantidad = contar_items_carrito();
 * echo 'Tienes ' . $cantidad . ' artículos en el carrito';
 */
function contar_items_carrito() {
    if (!isset($_SESSION['carrito'])) {
        return 0;
    }
    
    return array_sum($_SESSION['carrito']);
}

// ──────────────────────────────────────────────────────────
// FORMATEAR PRECIO
// ──────────────────────────────────────────────────────────

/**
 * Formatea un precio para mostrar en la página
 * 
 * @param float $precio - Precio a formatear
 * @return string Precio formateado (ej: "S/.29.90")
 * 
 * EJEMPLO:
 * echo formatear_precio(29.9);  // Salida: S/.29.90
 */
function formatear_precio($precio) {
    return 'S/.' . number_format($precio, 2, '.', ',');
}

// ──────────────────────────────────────────────────────────
// FORMATEAR FECHA
// ──────────────────────────────────────────────────────────

/**
 * Formatea una fecha del formato YYYY-MM-DD a día/mes/año
 * 
 * @param string $fecha - Fecha en formato YYYY-MM-DD
 * @param string $formato - Formato de salida (php date format)
 * @return string Fecha formateada
 * 
 * EJEMPLO:
 * echo formatear_fecha('2024-06-01');  // Salida: 01/06/2024
 */
function formatear_fecha($fecha, $formato = 'd/m/Y') {
    return date($formato, strtotime($fecha));
}

// ──────────────────────────────────────────────────────────
// ESTABLECER MENSAJE A SESIÓN
// ──────────────────────────────────────────────────────────

/**
 * Guarda un mensaje en la sesión para mostrar en la siguiente página
 * 
 * @param string $mensaje - Mensaje a mostrar
 * @param string $tipo - Tipo: 'success', 'error', 'warning', 'info'
 * 
 * EJEMPLO:
 * establecer_mensaje('✅ Producto agregado al carrito', 'success');
 * header('Location: carrito.php');
 */
function establecer_mensaje($mensaje, $tipo = 'info') {
    $_SESSION['mensaje'] = $mensaje;
    $_SESSION['tipo_mensaje'] = $tipo;
}

// ──────────────────────────────────────────────────────────
// OBTENER Y LIMPIAR MENSAJE
// ──────────────────────────────────────────────────────────

/**
 * Obtiene el mensaje guardado en sesión y lo elimina
 * 
 * @return array|null Array con 'mensaje' y 'tipo', o null si no hay
 * 
 * EJEMPLO:
 * $msg = obtener_mensaje();
 * if ($msg) {
 *     echo '<div class="alert alert-' . $msg['tipo'] . '">';
 *     echo $msg['mensaje'];
 *     echo '</div>';
 * }
 */
function obtener_mensaje() {
    if (!isset($_SESSION['mensaje'])) {
        return null;
    }
    
    $mensaje = [
        'mensaje' => $_SESSION['mensaje'],
        'tipo' => $_SESSION['tipo_mensaje'] ?? 'info'
    ];
    
    // Limpiar sesión
    unset($_SESSION['mensaje']);
    unset($_SESSION['tipo_mensaje']);
    
    return $mensaje;
}

// ──────────────────────────────────────────────────────────
// REDIRIGIR A URL
// ──────────────────────────────────────────────────────────

/**
 * Redirige a una URL con validación de seguridad
 * 
 * @param string $url - URL destino
 * @param int $tiempo - Tiempo de espera en segundos (0 = inmediato)
 * 
 * EJEMPLO:
 * redirigir('index.php');
 * redirigir('https://example.com', 2);
 */
function redirigir($url, $tiempo = 0) {
    // Asegurar que es una URL válida
    if (!filter_var($url, FILTER_VALIDATE_URL) && strpos($url, '/') !== 0) {
        $url = SITE_URL . '/' . $url;
    }
    
    if ($tiempo == 0) {
        header('Location: ' . $url);
    } else {
        header('Refresh: ' . $tiempo . '; url=' . $url);
    }
    exit();
}

?>
