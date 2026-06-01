<?php
/**
 * ============================================================
 * ARCHIVO: includes/security.php
 * ============================================================
 * DESCRIPCIÓN: Funciones de seguridad del sistema
 * Protege contra CSRF, validación de sesiones, sanitización, etc.
 * ============================================================
 */

// Incluir configuración global si no se ha cargado aún
if (!defined('HTTPS_ONLY')) {
    require_once __DIR__ . '/../config/config.php';
}

// ──────────────────────────────────────────────────────────
// INICIAR SESIÓN SEGURA
// ──────────────────────────────────────────────────────────

/**
 * Inicia sesión de manera segura con parámetros de seguridad
 * Esta función debe llamarse al inicio de cada archivo PHP
 */
function iniciar_sesion_segura() {
    // Si la sesión ya está iniciada, no hacer nada
    if (session_status() !== PHP_SESSION_ACTIVE) {
        // Configurar parámetros de seguridad de sesión
        ini_set('session.use_strict_mode', 1);           // Strict mode
        ini_set('session.use_only_cookies', 1);          // Solo cookies
        ini_set('session.cookie_httponly', 1);           // HttpOnly
        ini_set('session.cookie_secure', HTTPS_ONLY);    // Secure en HTTPS
        ini_set('session.cookie_samesite', 'Lax');       // SameSite
        ini_set('session.use_trans_sid', 0);             // No ID en URL
        
        // Iniciar la sesión
        session_start();
    }
}

// ──────────────────────────────────────────────────────────
// GENERAR TOKEN CSRF
// ──────────────────────────────────────────────────────────

/**
 * Genera un token CSRF (Cross-Site Request Forgery) único
 * y lo guarda en la sesión
 * 
 * @return string Token CSRF único
 * 
 * EJEMPLO:
 * $token = generar_token_csrf();
 * echo '<input type="hidden" name="csrf_token" value="' . $token . '">';
 */
function generar_token_csrf() {
    // Si no existe un token CSRF, generarlo
    if (!isset($_SESSION['csrf_token']) || empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

// ──────────────────────────────────────────────────────────
// VALIDAR TOKEN CSRF
// ──────────────────────────────────────────────────────────

/**
 * Valida que el token CSRF del formulario sea válido
 * Debe llamarse antes de procesar datos POST
 * 
 * @return bool true si el token es válido, false si no
 * 
 * EJEMPLO:
 * if ($_POST && !validar_token_csrf()) {
 *     die('❌ Error de seguridad: Token CSRF inválido');
 * }
 */
function validar_token_csrf() {
    // Verificar que exista el token en sesión y en POST
    if (!isset($_SESSION['csrf_token']) || 
        !isset($_POST['csrf_token']) || 
        empty($_SESSION['csrf_token']) || 
        empty($_POST['csrf_token'])) {
        return false;
    }
    
    // Comparar tokens de forma segura (previene timing attacks)
    return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

// ──────────────────────────────────────────────────────────
// SANITIZAR ENTRADA (XSS Prevention)
// ──────────────────────────────────────────────────────────

/**
 * Convierte caracteres especiales a entidades HTML
 * Previene ataques XSS (Cross-Site Scripting)
 * 
 * @param string $data - Datos a sanitizar
 * @return string Datos sanitizados
 * 
 * EJEMPLO:
 * $nombre_seguro = sanitizar($nombre_del_usuario);
 * echo '<h1>' . $nombre_seguro . '</h1>';
 */
function sanitizar($data) {
    if (is_array($data)) {
        // Si es array, sanitizar cada elemento
        return array_map('sanitizar', $data);
    }
    
    // Convertir caracteres especiales a entidades HTML
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// ──────────────────────────────────────────────────────────
// VALIDAR EMAIL
// ──────────────────────────────────────────────────────────

/**
 * Valida que una cadena sea un email válido
 * 
 * @param string $email - Email a validar
 * @return bool true si es válido, false si no
 * 
 * EJEMPLO:
 * if (validar_email($correo)) {
 *     // Procesar email válido
 * }
 */
function validar_email($email) {
    // Trimear espacios
    $email = trim($email);
    
    // Usar validación de PHP
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    // Verificar que no sea muy largo
    if (strlen($email) > 254) {
        return false;
    }
    
    return true;
}

// ──────────────────────────────────────────────────────────
// VALIDAR CONTRASEÑA
// ──────────────────────────────────────────────────────────

/**
 * Valida que una contraseña cumpla con requisitos mínimos
 * Requisitos:
 * - Mínimo 6 caracteres
 * 
 * @param string $password - Contraseña a validar
 * @return bool true si es válida, false si no
 * 
 * EJEMPLO:
 * if (validar_password($clave)) {
 *     // Procesar contraseña válida
 * }
 */
function validar_password($password) {
    // Verificar longitud mínima
    if (strlen($password) < 6) {
        return false;
    }
    
    // Verificar que no esté vacía
    if (empty(trim($password))) {
        return false;
    }
    
    return true;
}

// ──────────────────────────────────────────────────────────
// ENCRIPTAR CONTRASEÑA (Password Hashing)
// ──────────────────────────────────────────────────────────

/**
 * Encripta una contraseña usando bcrypt (algoritmo de PHP)
 * Es una vía de una sola dirección, no se puede desencriptar
 * 
 * @param string $password - Contraseña en texto plano
 * @return string Contraseña encriptada
 * 
 * EJEMPLO:
 * $hash = encriptar_password($clave);
 * // Guardar $hash en la base de datos
 */
function encriptar_password($password) {
    // PASSWORD_DEFAULT usa bcrypt con algoritmo automático
    return password_hash($password, PASSWORD_DEFAULT);
}

// ──────────────────────────────────────────────────────────
// VERIFICAR CONTRASEÑA ENCRIPTADA
// ──────────────────────────────────────────────────────────

/**
 * Verifica que una contraseña en texto plano coincida con su hash
 * 
 * @param string $password - Contraseña en texto plano
 * @param string $hash - Hash almacenado en BD
 * @return bool true si coinciden, false si no
 * 
 * EJEMPLO:
 * $usuario = mysqli_fetch_assoc($resultado);
 * if (verificar_password($clave_ingresada, $usuario['password'])) {
 *     // Contraseña correcta, iniciar sesión
 * }
 */
function verificar_password($password, $hash) {
    return password_verify($password, $hash);
}

// ──────────────────────────────────────────────────────────
// VALIDAR SESIÓN Y ROL
// ──────────────────────────────────────────────────────────

/**
 * Verifica que el usuario tenga una sesión activa
 * 
 * @return bool true si existe sesión activa, false si no
 * 
 * EJEMPLO:
 * if (!validar_sesion()) {
 *     header('Location: login.php');
 *     exit();
 * }
 */
function validar_sesion() {
    return isset($_SESSION['usuario']) && !empty($_SESSION['usuario']);
}

// ──────────────────────────────────────────────────────────
// VALIDAR ROL
// ──────────────────────────────────────────────────────────

/**
 * Verifica que el usuario tenga un rol específico
 * 
 * @param string|array $rol_requerido - Rol o array de roles permitidos
 * @return bool true si tiene el rol, false si no
 * 
 * EJEMPLO:
 * if (!validar_rol('admin')) {
 *     header('Location: index.php');
 *     exit();
 * }
 * 
 * // O con múltiples roles
 * if (!validar_rol(['admin', 'delivery'])) {
 *     // No es admin ni delivery
 * }
 */
function validar_rol($rol_requerido) {
    // Verificar que exista sesión
    if (!validar_sesion()) {
        return false;
    }
    
    // Si es un array de roles
    if (is_array($rol_requerido)) {
        return in_array($_SESSION['rol'] ?? '', $rol_requerido);
    }
    
    // Si es un rol único
    return ($_SESSION['rol'] ?? '') === $rol_requerido;
}

// ──────────────────────────────────────────────────────────
// CERRAR SESIÓN SEGURA
// ──────────────────────────────────────────────────────────

/**
 * Cierra la sesión de forma segura, limpiando todos los datos
 * 
 * EJEMPLO:
 * cerrar_sesion();
 * header('Location: index.php');
 * exit();
 */
function cerrar_sesion() {
    // Limpiar todas las variables de sesión
    $_SESSION = [];
    
    // Destruir la cookie de sesión
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    // Destruir la sesión
    session_destroy();
}

// ──────────────────────────────────────────────────────────
// REGISTRAR ACTIVIDAD
// ──────────────────────────────────────────────────────────

/**
 * Registra actividades importantes en un archivo log para auditoría
 * 
 * @param string $actividad - Descripción de la actividad
 * @param string $tipo - Tipo: 'info', 'warning', 'error', 'success'
 * 
 * EJEMPLO:
 * registrar_actividad('Usuario ' . $_SESSION['usuario'] . ' inició sesión', 'info');
 * registrar_actividad('Intento de login fallido: ' . $_POST['email'], 'warning');
 */
function registrar_actividad($actividad, $tipo = 'info') {
    $fecha = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $usuario = $_SESSION['usuario'] ?? 'Anónimo';
    
    $mensaje = "[$fecha] [$tipo] Usuario: $usuario | IP: $ip | Actividad: $actividad\n";
    
    // Guardar en archivo de log
    $log_file = ROOT_PATH . '/logs/actividades.log';
    
    // Crear carpeta logs si no existe
    if (!is_dir(dirname($log_file))) {
        mkdir(dirname($log_file), 0755, true);
    }
    
    // Escribir en archivo
    error_log($mensaje, 3, $log_file);
}

?>
