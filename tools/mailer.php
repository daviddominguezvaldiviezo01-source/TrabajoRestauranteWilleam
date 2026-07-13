<?php
/**
 * ============================================================
 * ARCHIVO: tools/mailer.php
 * ============================================================
 * Contiene funciones para generar vouchers PDF y enviarlos por email.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Cargar librerías vendor
if (file_exists(__DIR__ . '/../vendor/phpmailer/Exception.php')) {
    require_once __DIR__ . '/../vendor/phpmailer/Exception.php';
    require_once __DIR__ . '/../vendor/phpmailer/PHPMailer.php';
    require_once __DIR__ . '/../vendor/phpmailer/SMTP.php';
} elseif (file_exists(__DIR__ . '/../vendor/phpmailer/src/Exception.php')) {
    require_once __DIR__ . '/../vendor/phpmailer/src/Exception.php';
    require_once __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../vendor/phpmailer/src/SMTP.php';
}

if (!defined('FPDFONTPATH')) {
    define('FPDFONTPATH', __DIR__ . '/../vendor/fpdf/font/');
}
if (file_exists(__DIR__ . '/../vendor/fpdf/fpdf.php')) {
    require_once __DIR__ . '/../vendor/fpdf/fpdf.php';
}

require_once __DIR__ . '/../config/config.php';

/**
 * Genera el PDF del voucher de un pedido con un diseño más atractivo
 * 
 * @param array $datos_pedido
 * @param array $items
 * @return string PDF content
 */
function generar_pdf_voucher($datos_pedido, $items) {
    if (!class_exists('FPDF')) {
        return '';
    }
    
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();
      // Configuración de colores (Vibrant Light Mode)
    $color_primario = [220, 38, 38]; // #DC2626 (Red Accent)
    $color_texto_oscuro = [17, 17, 17]; // #111111
    $color_texto_claro = [100, 100, 100]; // #646464
    $color_fondo = [254, 242, 242]; // #FEF2F2
    
    // Borde de la página decorativo superior (grueso)
    $pdf->SetFillColor($color_primario[0], $color_primario[1], $color_primario[2]);
    $pdf->Rect(0, 0, 210, 25, 'F');
    
    $pdf->Ln(5);
    
    // Logo o Título de la marca en blanco sobre fondo rojo
    $pdf->SetFont('Arial', 'B', 28);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 10, utf8_decode(SITE_NAME), 0, 1, 'C');
    
    // Subtítulo
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 6, utf8_decode('Voucher Oficial de Compra'), 0, 1, 'C');
    $pdf->Ln(15);
    
    // Número de Pedido destacado
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetTextColor($color_primario[0], $color_primario[1], $color_primario[2]);
    $pdf->Cell(0, 10, utf8_decode('Pedido #') . str_pad($datos_pedido['id_pedido'], 6, '0', STR_PAD_LEFT), 0, 1, 'C');
    
    // Línea separadora
    $pdf->SetDrawColor(220, 220, 220);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
    $pdf->Ln(8);
    
    // Bloque de Información del Pedido
    $pdf->SetFillColor(250, 250, 250);
    $pdf->SetDrawColor(230, 230, 230);
    
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor($color_texto_oscuro[0], $color_texto_oscuro[1], $color_texto_oscuro[2]);
    
    // Fila 1
    $pdf->Cell(35, 8, '  Cliente:', 'L,T', 0, 'L', true);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(65, 8, utf8_decode($datos_pedido['nombre']), 'T', 0, 'L', true);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(35, 8, '  Fecha:', 'T', 0, 'L', true);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(55, 8, utf8_decode($datos_pedido['fecha']), 'R,T', 1, 'L', true);
    
    // Fila 2
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(35, 8, '  Correo:', 'L', 0, 'L', true);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(65, 8, utf8_decode($datos_pedido['correo']), 0, 0, 'L', true);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(35, 8, '  Metodo Pago:', 0, 0, 'L', true);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(55, 8, utf8_decode($datos_pedido['metodo_pago_label']), 'R', 1, 'L', true);
    
    // Fila 3
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(35, 8, '  Telefono:', 'L,B', 0, 'L', true);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(65, 8, utf8_decode($datos_pedido['telefono'] ?? '-'), 'B', 0, 'L', true);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(35, 8, '  Direccion:', 'B', 0, 'L', true);
    
    // Truncar dirección si es muy larga
    $direccion = mb_strimwidth($datos_pedido['direccion'], 0, 30, '...');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(55, 8, utf8_decode($direccion), 'R,B', 1, 'L', true);
    
    $pdf->Ln(10);
    
    // Encabezado de Productos (Gris oscuro para un toque premium)
    $pdf->SetFillColor(30, 30, 30);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetDrawColor(30, 30, 30);
    $pdf->SetFont('Arial', 'B', 10);
    
    $pdf->Cell(95, 10, 'Producto', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'Cant.', 1, 0, 'C', true);
    $pdf->Cell(35, 10, 'Precio Unit.', 1, 0, 'C', true);
    $pdf->Cell(35, 10, 'Subtotal', 1, 1, 'C', true);
    
    // Items
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor($color_texto_oscuro[0], $color_texto_oscuro[1], $color_texto_oscuro[2]);
    $pdf->SetDrawColor(220, 220, 220);
    
    $subtotal_items = 0;
    
    foreach ($items as $index => $item) {
        $precio = isset($item['precio']) ? $item['precio'] : (isset($item['precio_unitario']) ? $item['precio_unitario'] : ($item['subtotal'] / $item['cantidad']));
        $subtotal_items += $item['subtotal'];
        
        $fill = ($index % 2 == 0) ? false : true;
        $pdf->SetFillColor(248, 248, 248);
        
        $pdf->Cell(95, 10, '  ' . utf8_decode($item['nombre']), 'L,R,B', 0, 'L', $fill);
        $pdf->Cell(25, 10, $item['cantidad'], 'L,R,B', 0, 'C', $fill);
        $pdf->Cell(35, 10, 'S/. ' . number_format($precio, 2), 'L,R,B', 0, 'C', $fill);
        $pdf->Cell(35, 10, 'S/. ' . number_format($item['subtotal'], 2), 'L,R,B', 1, 'R', $fill);
    }
    
    $pdf->Ln(8);
    
    // Calcular totales
    $igv = $subtotal_items * (defined('TAX_RATE') ? TAX_RATE : 0.18);
    $delivery = defined('DELIVERY_COST') ? DELIVERY_COST : 5.00;
    $total_final = $subtotal_items + $igv + $delivery;
    
    // Tabla de Totales
    $pdf->SetX(120); 
    
    // Subtotal
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(50, 8, 'Subtotal:', 0, 0, 'R');
    $pdf->SetTextColor($color_texto_oscuro[0], $color_texto_oscuro[1], $color_texto_oscuro[2]);
    $pdf->Cell(30, 8, 'S/. ' . number_format($subtotal_items, 2), 0, 1, 'R');
    
    $pdf->SetX(120);
    // IGV
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(50, 8, 'IGV (18%):', 0, 0, 'R');
    $pdf->SetTextColor($color_texto_oscuro[0], $color_texto_oscuro[1], $color_texto_oscuro[2]);
    $pdf->Cell(30, 8, 'S/. ' . number_format($igv, 2), 0, 1, 'R');
    
    $pdf->SetX(120);
    // Delivery
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(50, 8, 'Delivery:', 0, 0, 'R');
    $pdf->SetTextColor($color_texto_oscuro[0], $color_texto_oscuro[1], $color_texto_oscuro[2]);
    $pdf->Cell(30, 8, 'S/. ' . number_format($delivery, 2), 0, 1, 'R');
    
    $pdf->Ln(2);
    
    // Total Final (Fondo rojo tenue o letras rojas)
    $pdf->SetX(120);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetFillColor($color_primario[0], $color_primario[1], $color_primario[2]);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(50, 12, 'TOTAL A PAGAR:', 1, 0, 'R', true);
    $pdf->Cell(30, 12, 'S/. ' . number_format($total_final, 2), 1, 1, 'R', true);
    
    $pdf->Ln(15);
    
    // Mensaje de Agradecimiento
    $pdf->SetFont('Arial', 'I', 11);
    $pdf->SetTextColor($color_primario[0], $color_primario[1], $color_primario[2]);
    $pdf->Cell(0, 10, utf8_decode('¡Gracias por tu compra!'), 0, 1, 'C');
    
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor($color_texto_claro[0], $color_texto_claro[1], $color_texto_claro[2]);
    $pdf->Cell(0, 5, utf8_decode('Los mejores sabores del mar, directo a tu mesa.'), 0, 1, 'C');
    $pdf->Cell(0, 5, utf8_decode('Este documento es un comprobante electrónico válido.'), 0, 1, 'C');
    
    return $pdf->Output('S');
}

/**
 * Genera el voucher PDF y lo envia por correo al cliente
 * 
 * @param array $datos_pedido
 * @param array $items
 * @param string $correo
 * @param string $nombre
 * @return array ['ok' => true/false, 'error' => string]
 */
function enviar_voucher_email($datos_pedido, $items, $correo, $nombre) {
    if (!defined('MAIL_ENABLED') || !MAIL_ENABLED) {
        return ['ok' => false, 'error' => 'El envio de correos esta deshabilitado en config.php.'];
    }

    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return ['ok' => false, 'error' => 'La clase PHPMailer no esta disponible.'];
    }

    $pdf_content = generar_pdf_voucher($datos_pedido, $items);
    if (empty($pdf_content)) {
        return ['ok' => false, 'error' => 'No se pudo generar el PDF del voucher.'];
    }
    
    $filename = 'Voucher_Pedido_' . str_pad($datos_pedido['id_pedido'], 6, '0', STR_PAD_LEFT) . '.pdf';

    $mail = new PHPMailer(true);

    if (defined('MAIL_DEBUG') && MAIL_DEBUG) {
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function($str, $level) {
            error_log("PHPMailer debug (level {$level}): {$str}");
        };
    }

    try {
        // Configuracion SMTP
        $mail->isSMTP();
        $mail->Host       = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = defined('SMTP_USER') ? SMTP_USER : '';
        $mail->Password   = defined('SMTP_PASS') ? SMTP_PASS : '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = defined('SMTP_PORT') ? SMTP_PORT : 587;
        
        $mail->CharSet = 'UTF-8';

        // Evitar problemas de certificado en local (XAMPP)
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        // Remitente y Destinatario
        $mail_from = defined('MAIL_FROM') ? MAIL_FROM : 'admin@restaurante.com';
        $mail_from_name = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : SITE_NAME;
        $mail->setFrom($mail_from, $mail_from_name);
        $mail->addAddress($correo, $nombre);
        
        // Copia oculta al admin si existe config
        if (defined('NOTIFICATION_RECIPIENT_EMAIL_ADMIN')) {
            $mail->addBCC(NOTIFICATION_RECIPIENT_EMAIL_ADMIN);
        }

        // Adjuntos
        $mail->addStringAttachment($pdf_content, $filename);

        // Contenido del email
        $mail->isHTML(true);
        $mail->Subject = 'Su pedido en ' . SITE_NAME . ' (Voucher adjunto) #' . str_pad($datos_pedido['id_pedido'], 6, '0', STR_PAD_LEFT);
        $mail->Body    = "Hola <b>" . htmlspecialchars($nombre) . "</b>,<br><br>" .
                         "Gracias por su preferencia. Adjuntamos el voucher de su pedido en formato PDF.<br><br>" .
                         "Atentamente,<br><b>" . SITE_NAME . "</b>";

        $mail->send();
        return ['ok' => true, 'error' => ''];
    } catch (Exception $e) {
        return ['ok' => false, 'error' => $mail->ErrorInfo];
    }
}

/**
 * Genera el correo HTML y lo envia con el código de verificación
 * 
 * @param string $correo
 * @param string $nombre
 * @param string $codigo
 * @return array ['ok' => true/false, 'error' => string]
 */
function enviar_codigo_verificacion_email($correo, $nombre, $codigo) {
    if (!defined('MAIL_ENABLED') || !MAIL_ENABLED) {
        return ['ok' => false, 'error' => 'El envio de correos esta deshabilitado.'];
    }

    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return ['ok' => false, 'error' => 'La clase PHPMailer no esta disponible.'];
    }

    $mail = new PHPMailer(true);

    try {
        // Configuracion SMTP
        $mail->isSMTP();
        $mail->Host       = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = defined('SMTP_USER') ? SMTP_USER : '';
        $mail->Password   = defined('SMTP_PASS') ? SMTP_PASS : '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = defined('SMTP_PORT') ? SMTP_PORT : 587;
        $mail->CharSet    = 'UTF-8';

        // Evitar problemas de certificado en local (XAMPP)
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        $mail_from = defined('MAIL_FROM') ? MAIL_FROM : 'admin@restaurante.com';
        $mail_from_name = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : SITE_NAME;
        
        $mail->setFrom($mail_from, $mail_from_name);
        $mail->addAddress($correo, $nombre);

        $mail->isHTML(true);
        $mail->Subject = 'Tu Código de Verificación - ' . SITE_NAME;
        
        // Diseño HTML Llamativo
        $html = "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Código de Verificación</title>
            <style>
                body {
                    background-color: #111111;
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    color: #ffffff;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: #1a1a1a;
                    padding: 30px;
                    border-radius: 8px;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.5);
                    border-top: 5px solid #c8102e;
                }
                .logo {
                    text-align: center;
                    font-size: 28px;
                    font-weight: bold;
                    color: #c8102e;
                    margin-bottom: 20px;
                    letter-spacing: 2px;
                }
                .content {
                    background-color: #222222;
                    padding: 25px;
                    border-radius: 6px;
                    text-align: center;
                }
                .title {
                    font-size: 22px;
                    margin-top: 0;
                    color: #ffffff;
                }
                .text {
                    color: #aaaaaa;
                    font-size: 16px;
                    line-height: 1.5;
                }
                .code-box {
                    background-color: #111111;
                    border: 2px dashed #c8102e;
                    color: #ffffff;
                    font-size: 32px;
                    font-weight: bold;
                    letter-spacing: 8px;
                    padding: 15px 10px;
                    margin: 30px auto;
                    width: 250px;
                    border-radius: 8px;
                }
                .footer {
                    margin-top: 30px;
                    text-align: center;
                    color: #777777;
                    font-size: 12px;
                }
            </style>
        </head>
        <body>
            <div style='background-color:#111; padding:40px 10px;'>
                <div class='container'>
                    <div class='logo'>" . strtoupper(SITE_NAME) . "</div>
                    <div class='content'>
                        <h2 class='title'>¡Hola " . htmlspecialchars($nombre) . "!</h2>
                        <p class='text'>Has solicitado un código de verificación para tu cuenta. Utiliza el siguiente código para completar el proceso:</p>
                        
                        <div class='code-box'>" . htmlspecialchars($codigo) . "</div>
                        
                        <p class='text'>Este código expirará en 15 minutos. Si no solicitaste este código, por favor ignora este mensaje.</p>
                    </div>
                    <div class='footer'>
                        <p>&copy; " . date('Y') . " " . SITE_NAME . ". Todos los derechos reservados.</p>
                        <p>Los mejores sabores del mar, directo a tu mesa.</p>
                    </div>
                </div>
            </div>
        </body>
        </html>";

        $mail->Body = $html;
        $mail->AltBody = "Hola $nombre, tu código de verificación es: $codigo . Este código expirará en 15 minutos.";

        $mail->send();
        return ['ok' => true, 'error' => ''];
    } catch (Exception $e) {
        return ['ok' => false, 'error' => $mail->ErrorInfo];
    }
}

/**
 * Envia el código de recuperación de contraseña por email usando PHPMailer y la plantilla oficial
 */
function enviar_codigo_recuperacion_password(string $correo, string $codigo, string $nombre) {
    if (!defined('MAIL_ENABLED') || !MAIL_ENABLED) {
        return ['ok' => false, 'error' => 'Servicio de correo desactivado en config.php'];
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = defined('SMTP_USER') ? SMTP_USER : '';
        $mail->Password   = defined('SMTP_PASS') ? SMTP_PASS : '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = defined('SMTP_PORT') ? SMTP_PORT : 587;
        $mail->CharSet    = 'UTF-8';

        // Evitar problemas de certificado en local (XAMPP)
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        $mail_from = defined('MAIL_FROM') ? MAIL_FROM : 'admin@restaurante.com';
        $mail_from_name = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : SITE_NAME;
        
        $mail->setFrom($mail_from, $mail_from_name);
        $mail->addAddress($correo, $nombre);

        $mail->isHTML(true);
        $mail->Subject = 'Recuperación de Contraseña - ' . SITE_NAME;

        $html = "<!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body {
                    font-family: 'Segoe UI', Arial, sans-serif;
                    background-color: #111111;
                    color: #ffffff;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: #1a1a1a;
                    padding: 30px;
                    border-radius: 8px;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.5);
                    border-top: 5px solid #dc2626;
                }
                .logo {
                    text-align: center;
                    font-size: 28px;
                    font-weight: bold;
                    color: #dc2626;
                    margin-bottom: 20px;
                    letter-spacing: 2px;
                }
                .content {
                    background-color: #222222;
                    padding: 25px;
                    border-radius: 6px;
                    text-align: center;
                }
                .title {
                    font-size: 22px;
                    margin-top: 0;
                    color: #ffffff;
                }
                .text {
                    color: #aaaaaa;
                    font-size: 16px;
                    line-height: 1.5;
                }
                .code-box {
                    background-color: #111111;
                    border: 2px dashed #dc2626;
                    color: #ffffff;
                    font-size: 32px;
                    font-weight: bold;
                    letter-spacing: 8px;
                    padding: 15px 10px;
                    margin: 30px auto;
                    width: 250px;
                    border-radius: 8px;
                }
                .footer {
                    margin-top: 30px;
                    text-align: center;
                    color: #777777;
                    font-size: 12px;
                }
            </style>
        </head>
        <body>
            <div style='background-color:#111; padding:40px 10px;'>
                <div class='container'>
                    <div class='logo'>" . strtoupper(SITE_NAME) . "</div>
                    <div class='content'>
                        <h2 class='title'>¡Hola " . htmlspecialchars($nombre) . "!</h2>
                        <p class='text'>Has solicitado restablecer tu contraseña. Utiliza el siguiente código para completar el proceso:</p>
                        
                        <div class='code-box'>" . htmlspecialchars($codigo) . "</div>
                        
                        <p class='text'>Este código expirará en 15 minutos. Si no solicitaste restablecer tu contraseña, puedes ignorar este mensaje de forma segura.</p>
                    </div>
                    <div class='footer'>
                        <p>&copy; " . date('Y') . " " . SITE_NAME . ". Todos los derechos reservados.</p>
                        <p>Los mejores sabores del mar, directo a tu mesa.</p>
                    </div>
                </div>
            </div>
        </body>
        </html>";

        $mail->Body = $html;
        $mail->AltBody = "Hola $nombre, tu código de recuperación de contraseña es: $codigo . Este código expirará en 15 minutos. Si no fuiste tú, ignora este mensaje.";

        $mail->send();
        return ['ok' => true, 'error' => ''];
    } catch (Exception $e) {
        return ['ok' => false, 'error' => $mail->ErrorInfo];
    }
}
