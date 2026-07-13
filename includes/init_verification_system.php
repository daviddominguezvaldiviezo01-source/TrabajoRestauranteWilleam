<?php
/**
 * ============================================================
 * ARCHIVO: includes/init_verification_system.php
 * ============================================================
 * Se ejecuta una sola vez para inicializar el sistema de verificación.
 * Agrega los campos necesarios a la tabla usuarios.
 * ============================================================
 */

function init_verification_fields()
{
    global $conexion;
    
    if (!$conexion) {
        return false;
    }

    // Array de campos a verificar/crear
    $campos = [
        'email_verificado' => "ADD COLUMN email_verificado TINYINT(1) DEFAULT 0 AFTER email",
        'codigo_verificacion_email' => "ADD COLUMN codigo_verificacion_email VARCHAR(8) DEFAULT NULL AFTER email_verificado",
        'expiracion_codigo_email' => "ADD COLUMN expiracion_codigo_email DATETIME DEFAULT NULL AFTER codigo_verificacion_email",
        'telefono_verificado' => "ADD COLUMN telefono_verificado TINYINT(1) DEFAULT 0 AFTER telefono",
        'codigo_verificacion_telefono' => "ADD COLUMN codigo_verificacion_telefono VARCHAR(8) DEFAULT NULL AFTER telefono_verificado",
        'expiracion_codigo_telefono' => "ADD COLUMN expiracion_codigo_telefono DATETIME DEFAULT NULL AFTER codigo_verificacion_telefono",
    ];

    foreach ($campos as $campo => $sql_add) {
        // Verificar si el campo ya existe
        $check = mysqli_query($conexion, "SHOW COLUMNS FROM usuarios LIKE '$campo'");
        
        if (!$check || mysqli_num_rows($check) == 0) {
            // El campo no existe, agregarlo
            $full_sql = "ALTER TABLE usuarios $sql_add";
            if (!mysqli_query($conexion, $full_sql)) {
                // Silenciosamente ignorar errores (el campo podría ya existir)
                error_log("Info: Campo $campo ya existe o no se pudo agregar");
            }
        }
    }
    
    return true;
}

// Ejecutar inicialización
init_verification_fields();
?>
