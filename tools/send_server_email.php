<?php
require_once __DIR__ . '/../config/config.php';

/**
 * Envía un email usando la función mail() como fallback.
 * Devuelve array con keys: success (bool), message (string).
 */
function send_server_email($to, $subject, $bodyHtml, $bodyText = '') {
    $from = MAIL_FROM;
    $fromName = MAIL_FROM_NAME;

    $headers = [];
    $headers[] = 'From: ' . $fromName . ' <' . $from . '>';
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=utf-8';

    $success = false;
    $message = '';
    try {
        $success = mail($to, $subject, $bodyHtml, implode("\r\n", $headers));
        $message = $success ? 'mail() returned true' : 'mail() returned false';
    } catch (Exception $e) {
        $success = false;
        $message = 'Exception: ' . $e->getMessage();
    }

    return ['success' => $success, 'message' => $message];
}

?>
