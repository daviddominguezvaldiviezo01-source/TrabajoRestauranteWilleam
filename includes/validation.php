<?php
/**
 * ============================================================
 * ARCHIVO: includes/validation.php
 * ============================================================
 * DESCRIPCIÓN: Funciones de validación de datos del formulario
 * Valida todos los inputs del usuario antes de procesarlos
 * ============================================================
 */

// ──────────────────────────────────────────────────────────
// VALIDAR NÚMERO ENTERO
// ──────────────────────────────────────────────────────────

/**
 * Valida que un valor sea un número entero válido
 * 
 * @param mixed $valor - Valor a validar
 * @param int $minimo - Valor mínimo permitido (opcional)
 * @param int $maximo - Valor máximo permitido (opcional)
 * @return int|false Número validado o false si no es válido
 * 
 * EJEMPLO:
 * $id = validar_entero($_GET['id']);
 * if ($id === false) {
 *     die('❌ ID inválido');
 * }
 */
function validar_entero($valor, $minimo = null, $maximo = null) {
    // Remover espacios en blanco
    $valor = trim($valor);
    
    // Verificar si es un número entero
    if (!is_numeric($valor) || strpos($valor, '.') !== false) {
        return false;
    }
    
    // Convertir a entero
    $valor = (int)$valor;
    
    // Verificar rango mínimo
    if ($minimo !== null && $valor < $minimo) {
        return false;
    }
    
    // Verificar rango máximo
    if ($maximo !== null && $valor > $maximo) {
        return false;
    }
    
    return $valor;
}

// ──────────────────────────────────────────────────────────
// VALIDAR NÚMERO DECIMAL
// ──────────────────────────────────────────────────────────

/**
 * Valida que un valor sea un número decimal válido
 * 
 * @param mixed $valor - Valor a validar
 * @param int $minimo - Valor mínimo permitido (opcional)
 * @param int $decimales - Cantidad de decimales permitidos
 * @return float|false Número validado o false si no es válido
 * 
 * EJEMPLO:
 * $precio = validar_decimal($_POST['precio'], 0, 2);
 * if ($precio === false) {
 *     die('❌ Precio inválido');
 * }
 */
function validar_decimal($valor, $minimo = 0, $decimales = 2) {
    // Remover espacios
    $valor = trim($valor);
    
    // Validar que sea numérico
    if (!is_numeric($valor)) {
        return false;
    }
    
    // Convertir a float
    $valor = (float)$valor;
    
    // Verificar mínimo
    if ($valor < $minimo) {
        return false;
    }
    
    // Redondear a decimales permitidos
    $valor = round($valor, $decimales);
    
    return $valor;
}

// ──────────────────────────────────────────────────────────
// VALIDAR CADENA DE TEXTO
// ──────────────────────────────────────────────────────────

/**
 * Valida que un valor sea texto válido
 * 
 * @param string $valor - Valor a validar
 * @param int $minimo - Longitud mínima
 * @param int $maximo - Longitud máxima
 * @return string|false Texto validado o false si no es válido
 * 
 * EJEMPLO:
 * $nombre = validar_texto($_POST['nombre'], 2, 100);
 * if ($nombre === false) {
 *     die('❌ El nombre debe tener entre 2 y 100 caracteres');
 * }
 */
function validar_texto($valor, $minimo = 1, $maximo = 255) {
    // Remover espacios al inicio y final
    $valor = trim($valor);
    
    // Verificar que no esté vacío
    if (empty($valor)) {
        return false;
    }
    
    // Obtener longitud
    $longitud = strlen($valor);
    
    // Verificar mínimo
    if ($longitud < $minimo) {
        return false;
    }
    
    // Verificar máximo
    if ($longitud > $maximo) {
        return false;
    }
    
    return $valor;
}

// ──────────────────────────────────────────────────────────
// VALIDAR TELÉFONO
// ──────────────────────────────────────────────────────────

/**
 * Valida que un número de teléfono sea válido
 * Acepta formatos: 999999999, +51999999999, (51)999999999, etc.
 * 
 * @param string $telefono - Teléfono a validar
 * @return string|false Teléfono validado (solo números) o false
 * 
 * EJEMPLO:
 * $tel = validar_telefono($_POST['telefono']);
 * if ($tel === false) {
 *     die('❌ Teléfono inválido');
 * }
 */
function validar_telefono($telefono) {
    // Remover espacios
    $telefono = trim($telefono);
    
    // Remover caracteres especiales dejando solo números
    $telefono = preg_replace('/\D/', '', $telefono);
    
    // Verificar que tenga entre 7 y 15 dígitos
    if (strlen($telefono) < 7 || strlen($telefono) > 15) {
        return false;
    }
    
    return $telefono;
}

// ──────────────────────────────────────────────────────────
// VALIDAR DIRECCIÓN
// ──────────────────────────────────────────────────────────

/**
 * Valida que una dirección sea válida
 * 
 * @param string $direccion - Dirección a validar
 * @return string|false Dirección validada o false
 * 
 * EJEMPLO:
 * $dir = validar_direccion($_POST['direccion']);
 * if ($dir === false) {
 *     die('❌ Dirección inválida');
 * }
 */
function validar_direccion($direccion) {
    // Validar usando función de texto
    return validar_texto($direccion, 5, 255);
}

// ──────────────────────────────────────────────────────────
// VALIDAR CANTIDAD/STOCK
// ──────────────────────────────────────────────────────────

/**
 * Valida que un valor de cantidad sea válido
 * 
 * @param mixed $cantidad - Cantidad a validar
 * @param int $minimo - Cantidad mínima (por defecto 1)
 * @param int $maximo - Cantidad máxima (por defecto 999)
 * @return int|false Cantidad validada o false
 * 
 * EJEMPLO:
 * $qty = validar_cantidad($_POST['cantidad']);
 * if ($qty === false) {
 *     die('❌ Cantidad inválida');
 * }
 */
function validar_cantidad($cantidad, $minimo = 1, $maximo = 999) {
    return validar_entero($cantidad, $minimo, $maximo);
}

// ──────────────────────────────────────────────────────────
// VALIDAR URL
// ──────────────────────────────────────────────────────────

/**
 * Valida que una URL sea válida
 * 
 * @param string $url - URL a validar
 * @return string|false URL validada o false
 * 
 * EJEMPLO:
 * $imagen_url = validar_url($_POST['imagen']);
 * if ($imagen_url === false) {
 *     die('❌ URL de imagen inválida');
 * }
 */
function validar_url($url) {
    // Usar función de PHP para validar URL
    if (filter_var($url, FILTER_VALIDATE_URL) === false) {
        return false;
    }
    
    return $url;
}

// ──────────────────────────────────────────────────────────
// VALIDAR ENUM (Valores permitidos)
// ──────────────────────────────────────────────────────────

/**
 * Valida que un valor esté dentro de una lista permitida
 * 
 * @param string $valor - Valor a validar
 * @param array $opciones_permitidas - Array de valores válidos
 * @return string|false Valor validado o false
 * 
 * EJEMPLO:
 * $estado = validar_enum($_POST['estado'], ['activo', 'inactivo', 'pendiente']);
 * if ($estado === false) {
 *     die('❌ Estado inválido');
 * }
 */
function validar_enum($valor, $opciones_permitidas) {
    // Remover espacios
    $valor = trim($valor);
    
    // Verificar si está en las opciones permitidas
    if (!in_array($valor, $opciones_permitidas, true)) {
        return false;
    }
    
    return $valor;
}

// ──────────────────────────────────────────────────────────
// VALIDAR FECHA
// ──────────────────────────────────────────────────────────

/**
 * Valida que una fecha sea válida en formato YYYY-MM-DD
 * 
 * @param string $fecha - Fecha a validar
 * @return string|false Fecha validada o false
 * 
 * EJEMPLO:
 * $fecha = validar_fecha($_POST['fecha_nacimiento']);
 * if ($fecha === false) {
 *     die('❌ Fecha inválida');
 * }
 */
function validar_fecha($fecha) {
    // Remover espacios
    $fecha = trim($fecha);
    
    // Verificar formato YYYY-MM-DD
    $partes = explode('-', $fecha);
    
    if (count($partes) !== 3) {
        return false;
    }
    
    // Validar que sean números
    if (!is_numeric($partes[0]) || !is_numeric($partes[1]) || !is_numeric($partes[2])) {
        return false;
    }
    
    // Validar que sea una fecha válida
    if (!checkdate($partes[1], $partes[2], $partes[0])) {
        return false;
    }
    
    return $fecha;
}

// ──────────────────────────────────────────────────────────
// VALIDAR SELECCIONAR (Dropdown)
// ──────────────────────────────────────────────────────────

/**
 * Valida que se haya seleccionado una opción en un dropdown
 * 
 * @param mixed $valor - Valor del dropdown
 * @return string|false Valor validado o false
 * 
 * EJEMPLO:
 * $categoria = validar_select($_POST['categoria']);
 * if ($categoria === false) {
 *     die('❌ Debes seleccionar una categoría');
 * }
 */
function validar_select($valor) {
    // Convertir a string y remover espacios
    $valor = (string)$valor;
    $valor = trim($valor);
    
    // Verificar que no esté vacío
    if (empty($valor)) {
        return false;
    }
    
    return $valor;
}

// ──────────────────────────────────────────────────────────
// VALIDAR CHECKBOX (Booleano)
// ──────────────────────────────────────────────────────────

/**
 * Valida si un checkbox está marcado
 * 
 * @param string $nombre_campo - Nombre del campo del checkbox
 * @return bool true si está marcado, false si no
 * 
 * EJEMPLO:
 * $acepta_terminos = validar_checkbox('terminos_aceptados');
 * if (!$acepta_terminos) {
 *     die('❌ Debes aceptar los términos');
 * }
 */
function validar_checkbox($nombre_campo) {
    return isset($_POST[$nombre_campo]) && $_POST[$nombre_campo] !== '';
}

?>
