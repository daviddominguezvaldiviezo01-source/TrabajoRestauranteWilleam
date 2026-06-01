<?php
/**
 * ============================================================
 * ARCHIVO: logout.php
 * ============================================================
 * DESCRIPCIÓN: Cierra la sesión del usuario de forma segura
 * Limpia todas las variables de sesión y cookies
 * ============================================================
 */

// Incluir funciones de seguridad
require_once __DIR__ . '/includes/security.php';

// Iniciar sesión para poder destruirla
iniciar_sesion_segura();

// Registrar la actividad antes de cerrar sesión
if (isset($_SESSION['usuario'])) {
    registrar_actividad('Logout - Usuario: ' . $_SESSION['nombre'], 'info');
}

// Cerrar sesión de forma segura
cerrar_sesion();

// Redirigir a página de inicio
header('Location: index.php');
exit();
?>
