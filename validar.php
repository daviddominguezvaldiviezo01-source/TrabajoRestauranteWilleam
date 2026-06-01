<?php
/**
 * ============================================================
 * ARCHIVO: validar.php
 * ============================================================
 * DESCRIPCIÓN: Procesa el login de usuarios
 * Usa prepared statements para prevenir SQL injection
 * Las contraseñas están correctamente hasheadas
 * ============================================================
 */

// Iniciar sesión segura
require_once __DIR__ . '/includes/security.php';
iniciar_sesion_segura();

// Incluir configuración y funciones
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/includes/validation.php';
require_once __DIR__ . '/includes/functions.php';

// Declarar variable global para evitar errores de linter
global $conexion;

// ──────────────────────────────────────────────────────────
// PROCESAR FORMULARIO DE LOGIN
// ──────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // PASO 1: Validar que venga de un formulario (CSRF)
    /**
     * ✅ SEGURIDAD: Validar token CSRF para prevenir ataques
     * El token es generado en la página de login y debe validarse aquí
     * La función valida automáticamente $_POST['csrf_token']
     */
    if (!validar_token_csrf()) {
        $_SESSION['error_login'] = '❌ Solicitud inválida (CSRF)';
        registrar_actividad('Intento de login sin CSRF válido', 'warning');
        header('Location: cliente/login.php');
        exit();
    }
    
    // PASO 2: Validar y limpiar entrada
    $correo = isset($_POST['correo']) ? sanitizar($_POST['correo']) : '';
    $clave  = isset($_POST['clave']) ? trim($_POST['clave']) : '';
    
    // PASO 3: Validar email
    if (!validar_email($correo)) {
        $_SESSION['error_login'] = '❌ Email inválido';
        header('Location: cliente/login.php');
        exit();
    }
    
    // PASO 4: Validar contraseña
    if (empty($clave)) {
        $_SESSION['error_login'] = '❌ Contraseña requerida';
        header('Location: cliente/login.php');
        exit();
    }
    
    // ──────────────────────────────────────────────────────────
    // PASO 5: Buscar usuario usando PREPARED STATEMENT
    // ──────────────────────────────────────────────────────────
    
    /**
     * ✅ SEGURIDAD: Usar prepared statement previene SQL injection
     * El ? es un placeholder que será reemplazado de forma segura
     */
    $stmt = mysqli_prepare($conexion, 
        "SELECT id_usuario, nombre, email, password, rol 
         FROM usuarios 
         WHERE email = ?");
    
    if (!$stmt) {
        error_log('Error en query: ' . mysqli_error($conexion));
        $_SESSION['error_login'] = '❌ Error del servidor';
        header('Location: cliente/login.php');
        exit();
    }
    
    // Vincular parámetro (s = string)
    mysqli_stmt_bind_param($stmt, "s", $correo);
    
    // Ejecutar query
    mysqli_stmt_execute($stmt);
    
    // Obtener resultado
    $resultado = mysqli_stmt_get_result($stmt);
    
    // ──────────────────────────────────────────────────────────
    // PASO 6: Verificar si existe el usuario
    // ──────────────────────────────────────────────────────────
    
    if (mysqli_num_rows($resultado) > 0) {
        
        $usuario = mysqli_fetch_assoc($resultado);
        
        // ──────────────────────────────────────────────────────────
        // PASO 6B: Verificar si usuario está habilitado/activo
        // ──────────────────────────────────────────────────────────
        
        /**
         * ✅ SEGURIDAD: Verificar que la cuenta no esté deshabilitada
         * Evita que usuarios bloqueados accedan a la aplicación
         * Campo 'activo' debe ser 1 para permitir login
         */
        if (isset($usuario['activo']) && $usuario['activo'] != 1) {
            $_SESSION['error_login'] = '❌ Cuenta deshabilitada. Contacta con soporte.';
            
            registrar_actividad(
                'Intento de login en cuenta inactiva - Email: ' . $usuario['email'],
                'warning'
            );
            
            mysqli_stmt_close($stmt);
            header('Location: cliente/login.php');
            exit();
        }
        
        // ──────────────────────────────────────────────────────────
        // PASO 7: Verificar contraseña usando password_verify
        // ──────────────────────────────────────────────────────────
        
        /**
         * ✅ SEGURIDAD: password_verify compara la contraseña en texto plano
         * con el hash almacenado en BD de forma segura
         * No es posible desencriptar la contraseña desde el hash
         */
        if (password_verify($clave, $usuario['password'])) {
            
            // ──────────────────────────────────────────────────────
            // PASO 8: CONTRASEÑA CORRECTA - Crear sesión
            // ──────────────────────────────────────────────────────
            
            $_SESSION['usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['rol'];
            
            // Registrar actividad
            registrar_actividad(
                'Login exitoso - Email: ' . $usuario['email'],
                'success'
            );
            
            // Limpiar errores previos
            unset($_SESSION['error_login']);
            
            // Redirigir según rol
            if ($usuario['rol'] == 'admin') {
                header('Location: admin/dashboard.php');
            } elseif ($usuario['rol'] == 'delivery') {
                header('Location: delivery.php');
            } else {
                header('Location: cliente/index.php');
            }
            
            exit();
            
        } else {
            
            // ──────────────────────────────────────────────────────
            // CONTRASEÑA INCORRECTA
            // ──────────────────────────────────────────────────────
            
            $_SESSION['error_login'] = '❌ Email o contraseña incorrectos';
            
            // Registrar intento fallido
            registrar_actividad(
                'Intento de login fallido - Email: ' . $usuario['email'],
                'warning'
            );
        }
        
    } else {
        
        // ──────────────────────────────────────────────────────────
        // USUARIO NO EXISTE
        // ──────────────────────────────────────────────────────────
        
        $_SESSION['error_login'] = '❌ Email o contraseña incorrectos';
        
        // Registrar intento con email inexistente
        registrar_actividad(
            'Intento de login con email inexistente: ' . $correo,
            'warning'
        );
    }
    
    // Cerrar statement
    mysqli_stmt_close($stmt);
    
    // Redirigir al formulario de login
    header('Location: cliente/login.php');
    exit();
}

// ──────────────────────────────────────────────────────────
// Si no es POST, redirigir
// ──────────────────────────────────────────────────────────

header('Location: cliente/login.php');
exit();
?>
