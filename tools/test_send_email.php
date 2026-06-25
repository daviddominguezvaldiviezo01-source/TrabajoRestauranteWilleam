<?php
require_once __DIR__ . '/send_server_email.php';
require_once __DIR__ . '/../config/config.php';

echo "Testing server-side email to: " . NOTIFICATION_RECIPIENT_EMAIL . "\n";
$res = send_server_email(NOTIFICATION_RECIPIENT_EMAIL, 'Prueba de notificación - Restaurante', '<p>Este es un correo de prueba desde el servidor.</p>', 'Texto plano');
echo "Result: " . ($res['success'] ? 'OK' : 'FAIL') . "\n";
echo "Message: " . $res['message'] . "\n";

?>
