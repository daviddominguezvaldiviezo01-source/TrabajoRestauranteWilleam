<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "restaurant_bd";
$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) { echo "DBERR: " . mysqli_connect_error(); exit(1); }
mysqli_set_charset($conn, "utf8mb4");
$password = password_hash("Delivery1234", PASSWORD_DEFAULT);
$email = "delivery@restaurante.test";
$stmt = mysqli_prepare($conn, "UPDATE usuarios SET password=?, rol='delivery' WHERE email=?");
mysqli_stmt_bind_param($stmt, "ss", $password, $email);
mysqli_stmt_execute($stmt);
echo mysqli_stmt_affected_rows($stmt);
?>
