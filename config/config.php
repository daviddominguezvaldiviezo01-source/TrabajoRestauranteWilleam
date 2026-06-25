<?php
/**
 * ============================================================
 * ARCHIVO: config/config.php
 * ============================================================
 * DESCRIPCIÓN: Constantes y configuración global del sistema
 * Este archivo centraliza todas las configuraciones para fácil
 * mantenimiento. Cualquier cambio se hace aquí.
 * ============================================================
 */

// ──────────────────────────────────────────────────────────
// INFORMACIÓN DEL SITIO
// ──────────────────────────────────────────────────────────

/** @const string SITE_NAME - Nombre del restaurante */
define('SITE_NAME', 'Brisamar');

/** @const string SITE_DESCRIPTION - Descripción breve del sitio */
define('SITE_DESCRIPTION', 'Los mejores sabores del mar, directo a tu mesa');

/** @const string SITE_URL - URL base del sitio (sin trailing slash) */
define('SITE_URL', 'http://localhost/RESTAURANTE2');

/** @const string SITE_KEYWORDS - Palabras clave para SEO */
define('SITE_KEYWORDS', 'restaurante, comida, seafood, mariscos, delivery, ceviche, brisamar');

// ──────────────────────────────────────────────────────────
// INFORMACIÓN DE CONTACTO
// ──────────────────────────────────────────────────────────

/** @const string CONTACT_PHONE - Número de teléfono */
define('CONTACT_PHONE', '+51 999 999 999');

/** @const string CONTACT_EMAIL - Email de contacto */
define('CONTACT_EMAIL', 'info@brisamar.com');

// ──────────────────────────────────────────────────────────
// CONFIGURACIÓN DE EMAILJS (para envío de notificaciones por email)
// Rellena estos valores con los datos que te proporciona EmailJS
// Servicio: https://www.emailjs.com/ — crea un servicio, template y obtén tu user ID
// ──────────────────────────────────────────────────────────
/** @const string EMAILJS_USER_ID - User ID provisto por EmailJS */
define('EMAILJS_USER_ID', 'tu_emailjs_user_id');

/** @const string EMAILJS_SERVICE_ID - Service ID configurado en EmailJS */
define('EMAILJS_SERVICE_ID', 'tu_emailjs_service_id');

/** @const string EMAILJS_TEMPLATE_ID - Template ID configurado en EmailJS */
define('EMAILJS_TEMPLATE_ID', 'tu_emailjs_template_id');

/** @const string NOTIFICATION_RECIPIENT_EMAIL - Email destinatario de las notificaciones */
define('NOTIFICATION_RECIPIENT_EMAIL', CONTACT_EMAIL);

// ──────────────────────────────────────────────────────────
// CONFIGURACIÓN MAIL SERVER (server-side fallback)
// Si quieres envío server-side con SMTP, configura estos valores
// ──────────────────────────────────────────────────────────
/** @const bool MAIL_ENABLED - Habilita envío server-side con mail() */
define('MAIL_ENABLED', false);

/** @const string MAIL_FROM - Dirección FROM usada en emails */
define('MAIL_FROM', CONTACT_EMAIL);

/** @const string MAIL_FROM_NAME - Nombre remitente */
define('MAIL_FROM_NAME', SITE_NAME);

/** @const string SMTP_HOST - Host SMTP si usas SMTP (opcional) */
define('SMTP_HOST', 'smtp.example.com');

/** @const string SMTP_USER - Usuario SMTP (opcional) */
define('SMTP_USER', 'user@example.com');

/** @const string SMTP_PASS - Password SMTP (opcional) */
define('SMTP_PASS', 'secret');

/** @const int SMTP_PORT - Puerto SMTP (opcional) */
define('SMTP_PORT', 587);


/** @const string CONTACT_ADDRESS - Dirección física */
define('CONTACT_ADDRESS', 'Calle Principal 123');

// ──────────────────────────────────────────────────────────
// CONFIGURACIÓN DE BASE DE DATOS
// ──────────────────────────────────────────────────────────

/** @const string DB_HOST - Servidor de base de datos */
define('DB_HOST', 'localhost');

/** @const string DB_USER - Usuario de base de datos */
define('DB_USER', 'root');

/** @const string DB_PASS - Contraseña de base de datos */
define('DB_PASS', '');

/** @const string DB_NAME - Nombre de la base de datos */
define('DB_NAME', 'restaurant_bd');

/** @const string DB_CHARSET - Codificación de caracteres */
define('DB_CHARSET', 'utf8mb4');

// ──────────────────────────────────────────────────────────
// CONFIGURACIÓN DE SEGURIDAD
// ──────────────────────────────────────────────────────────

/** @const string SECRET_KEY - Clave secreta para tokens y encriptación */
define('SECRET_KEY', 'tu_clave_secreta_super_segura_aqui_2024');

/** @const int SESSION_TIMEOUT - Tiempo de sesión en segundos (30 minutos) */
define('SESSION_TIMEOUT', 1800);

/** @const bool HTTPS_ONLY - Forzar HTTPS en producción */
define('HTTPS_ONLY', false); // Cambiar a true en producción

/** @const bool DEBUG_MODE - Mostrar errores (false en producción) */
define('DEBUG_MODE', true); // Cambiar a false en producción

// ──────────────────────────────────────────────────────────
// CONFIGURACIÓN DE RUTAS
// ──────────────────────────────────────────────────────────

/** @const string ROOT_PATH - Ruta raíz del proyecto (sin trailing slash) */
define('ROOT_PATH', dirname(dirname(__FILE__)));

/** @const string INCLUDES_PATH - Ruta a carpeta includes */
define('INCLUDES_PATH', ROOT_PATH . '/includes');

/** @const string CONFIG_PATH - Ruta a carpeta config */
define('CONFIG_PATH', ROOT_PATH . '/config');

/** @const string ASSETS_PATH - Ruta a carpeta assets */
define('ASSETS_PATH', ROOT_PATH . '/assets');

// ──────────────────────────────────────────────────────────
// CONFIGURACIÓN DE PAGOS
// ──────────────────────────────────────────────────────────

/** @const float DELIVERY_COST - Costo de envío */
define('DELIVERY_COST', 5.00);

/** @const float TAX_RATE - Porcentaje de impuestos */
define('TAX_RATE', 0.18);

// ──────────────────────────────────────────────────────────
// CONFIGURACIÓN DE PAGINACIÓN
// ──────────────────────────────────────────────────────────

/** @const int ITEMS_PER_PAGE - Elementos por página */
define('ITEMS_PER_PAGE', 12);

// ──────────────────────────────────────────────────────────
// ROLES DE USUARIO
// ──────────────────────────────────────────────────────────

/** @const array ROLES - Roles disponibles en el sistema */
define('ROLES', [
    'cliente' => 'Cliente',
    'admin' => 'Administrador',
    'delivery' => 'Repartidor'
]);

// ──────────────────────────────────────────────────────────
// MENSAJES DEL SISTEMA
// ──────────────────────────────────────────────────────────

/** @const array MESSAGES - Mensajes predefinidos del sistema */
define('MESSAGES', [
    'success_add_cart' => '✅ Producto agregado al carrito',
    'success_login' => '✅ Sesión iniciada correctamente',
    'success_register' => '✅ Cuenta creada exitosamente',
    'error_invalid_credentials' => '❌ Email o contraseña incorrectos',
    'error_email_exists' => '❌ El email ya está registrado',
    'error_weak_password' => '❌ Contraseña debe tener mínimo 6 caracteres',
    'error_server' => '❌ Error del servidor. Intenta más tarde',
    'error_empty_fields' => '❌ Por favor completa todos los campos',
    'error_passwords_mismatch' => '❌ Las contraseñas no coinciden'
]);
?>
