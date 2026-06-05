<?php
session_start();

include("conexion.php");

$correo = trim($_POST['correo'] ?? '');
$clave = trim($_POST['clave'] ?? '');

if ($correo === '' || $clave === '') {
    header("Location: index.php?error=1");
    exit();
}

$stmt = mysqli_prepare($conexion, "SELECT * FROM usuarios WHERE correo = ? OR email = ?");
mysqli_stmt_bind_param($stmt, "ss", $correo, $correo);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($resultado) > 0) {
    $fila = mysqli_fetch_assoc($resultado);
    $hash = $fila['clave'] ?? $fila['password'] ?? '';
    $valid = false;

    if ($hash !== '' && password_verify($clave, $hash)) {
        $valid = true;
    } elseif ($hash !== '' && hash_equals($hash, $clave)) {
        $valid = true;
        $nuevoHash = password_hash($clave, PASSWORD_DEFAULT);
        $updateStmt = mysqli_prepare($conexion, "UPDATE usuarios SET clave = ? WHERE id_usuario = ?");
        if ($updateStmt) {
            mysqli_stmt_bind_param($updateStmt, "si", $nuevoHash, $fila['id_usuario']);
            mysqli_stmt_execute($updateStmt);
            mysqli_stmt_close($updateStmt);
        }
    }

    if ($valid) {
        $_SESSION['usuario'] = $fila['nombre'];
        header("Location: dashboard.php");
        exit();
    }
}

header("Location: index.php?error=1");
exit();
?>