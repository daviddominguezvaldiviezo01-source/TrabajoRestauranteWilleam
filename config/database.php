<?php
/**
 * ============================================================
 * ARCHIVO: config/database.php
 * ============================================================
 * DESCRIPCIÓN: Conexión segura a la base de datos
 * Utiliza MySQLi con soporte para prepared statements
 * previene SQL injection automáticamente
 * ============================================================
 */

// Incluir configuración
require_once __DIR__ . '/config.php';

// ──────────────────────────────────────────────────────────
// CREAR CONEXIÓN SEGURA A BASE DE DATOS
// ──────────────────────────────────────────────────────────

/**
 * Crear conexión mysqli con manejo de errores
 * La conexión usa MySQLi que es más seguro que mysql deprecated
 */
$conexion = @mysqli_connect(
    DB_HOST,    // Host
    DB_USER,   // Usuario
    DB_PASS, // Contraseña;
    DB_NAME, // Base de datos
);

// ──────────────────────────────────────────────────────────
// VERIFICAR CONEXIÓN
// ──────────────────────────────────────────────────────────

if (!$conexion) {
    // En desarrollo mostrar error, en producción no
    if (DEBUG_MODE) {
        die("<h2>Error de Conexión</h2>
                <p>No se puede conectar a la base de datos:</p>
                <p><strong>" . mysqli_connect_error() . "</strong></p>
                <p>Verifica que MySQL esté corriendo y los datos de conexión sean correctos.</p>");
    } else {
        // En producción, mostrar mensaje genérico y registrar error
        error_log('Error de conexión BD: ' . mysqli_connect_error());
        die("Error del servidor. Intenta más tarde.");
    }
}

// ──────────────────────────────────────────────────────────
// CONFIGURAR CODIFICACIÓN UTF-8
// ──────────────────────────────────────────────────────────

/**
 * Establecer charset UTF-8 para soportar caracteres especiales
 * como ñ, acentos, etc.
 */
if (!mysqli_set_charset($conexion, DB_CHARSET)) {
    error_log('Error al establecer charset: ' . mysqli_error($conexion));
}

// ──────────────────────────────────────────────────────────
// CONFIGURAR MODO DE ERROR
// ──────────────────────────────────────────────────────────

/**
 * Modo de reporte de errores:
 * MYSQLI_REPORT_STRICT - Lanzar excepciones en lugar de warnings
 * MYSQLI_REPORT_ALL - Reportar todos los errores
 */
mysqli_report(MYSQLI_REPORT_STRICT);

// ──────────────────────────────────────────────────────────
// LA CONEXIÓN ESTÁ LISTA
// ──────────────────────────────────────────────────────────

// $conexion está disponible globalmente en todos los archivos que incluyan este
?>
