<?php
/**
 * ============================================================
 * ARCHIVO: cliente/verificar_codigo.php
 * ============================================================
 * Verifica el código de EMAIL ingresado por el cliente.
 * Recibe: POST con 'codigo' (6 dígitos)
 * ============================================================
 */

session_start();
require_once dirname(__FILE__) . '/../conexion.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Validar sesión
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'cliente') {
        echo json_encode(['ok' => false, 'error' => 'No autenticado']);
        exit();
    }

    if (!isset($_SESSION['usuario'])) {
        echo json_encode(['ok' => false, 'error' => 'ID de usuario no en sesión']);
        exit();
    }

    $id_usuario = intval($_SESSION['usuario']);
    $codigo_ingresado = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';

    if (empty($codigo_ingresado) || strlen($codigo_ingresado) != 6 || !ctype_digit($codigo_ingresado)) {
        echo json_encode(['ok' => false, 'error' => 'Código inválido (debe ser 6 dígitos)']);
        exit();
    }

    // Obtener código guardado
    $query = "SELECT codigo_verificacion_email, expiracion_codigo_email FROM usuarios WHERE id_usuario = ? LIMIT 1";
    $stmt = mysqli_prepare($conexion, $query);
    if (!$stmt) {
        echo json_encode(['ok' => false, 'error' => 'Error en preparación de consulta: ' . mysqli_error($conexion)]);
        exit();
    }

    mysqli_stmt_bind_param($stmt, 'i', $id_usuario);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    
    if (!$row || empty($row['codigo_verificacion_email'])) {
        echo json_encode(['ok' => false, 'error' => 'No hay código solicitado. Solicita uno primero.']);
        exit();
    }
    
    // Verificar expiración
    if (strtotime($row['expiracion_codigo_email']) < time()) {
        echo json_encode(['ok' => false, 'error' => 'El código ha expirado. Solicita uno nuevo.']);
        exit();
    }
    
    // Verificar código
    if ($row['codigo_verificacion_email'] !== $codigo_ingresado) {
        echo json_encode(['ok' => false, 'error' => 'Código incorrecto']);
        exit();
    }
    
    // Marcar como verificado
    $query_update = "UPDATE usuarios SET email_verificado = 1, codigo_verificacion_email = NULL, expiracion_codigo_email = NULL WHERE id_usuario = ?";
    $stmt_update = mysqli_prepare($conexion, $query_update);
    if (!$stmt_update) {
        echo json_encode(['ok' => false, 'error' => 'Error en preparación de UPDATE: ' . mysqli_error($conexion)]);
        exit();
    }

    mysqli_stmt_bind_param($stmt_update, 'i', $id_usuario);
    if (!mysqli_stmt_execute($stmt_update)) {
        mysqli_stmt_close($stmt_update);
        echo json_encode(['ok' => false, 'error' => 'Error al actualizar verificación: ' . mysqli_error($conexion)]);
        exit();
    }
    mysqli_stmt_close($stmt_update);
    
    echo json_encode(['ok' => true, 'message' => '✓ ¡Correo verificado exitosamente!']);

} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => 'Excepción: ' . $e->getMessage()]);
}
?>
