<?php
/**
 * ============================================================
 * ARCHIVO: conexion.php
 * ============================================================
 * DESCRIPCIÓN: Punto de entrada para conexión a la base de datos
 * Este archivo ahora usa la configuración centralizada
 * para mayor seguridad y mantenibilidad
 * ============================================================
 */

// Incluir configuración y conexión segura
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Nota: $conexion está disponible automáticamente después de incluir database.php
// También están disponibles todas las constantes de config.php
?>
