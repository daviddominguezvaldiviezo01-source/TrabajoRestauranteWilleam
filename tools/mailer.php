<?php

/**
 * ============================================================
 * ARCHIVO: tools/mailer.php
 * ============================================================
 * Servicio de envío de correo con voucher PDF adjunto.
 * Usa PHPMailer con Gmail SMTP y FPDF para el PDF.
 * ============================================================
 */

// Indicar a FPDF dónde están los archivos de fuentes JSON
if (!defined('FPDFONTPATH')) {
  define('FPDFONTPATH', dirname(__FILE__) . '/../vendor/fpdf/font/');
}

require_once dirname(__FILE__) . '/../vendor/phpmailer/Exception.php';
require_once dirname(__FILE__) . '/../vendor/phpmailer/PHPMailer.php';
require_once dirname(__FILE__) . '/../vendor/phpmailer/SMTP.php';
require_once dirname(__FILE__) . '/../vendor/fpdf/fpdf.php';

if (!defined('SMTP_HOST')) {
  require_once dirname(__FILE__) . '/../config/config.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as MailerException;

/**
 * Convierte texto UTF-8 a ISO-8859-1 para FPDF (que no soporta UTF-8 nativo).
 */
function _fpdf_str(string $text): string
{
  return iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $text) ?: $text;
}

/**
 * Genera el contenido PDF del voucher en memoria y lo devuelve como string.
 *
 * @param array $pedido  Datos del pedido
 * @param array $items   Productos del pedido
 * @return string        Contenido binario del PDF
 */
function generar_pdf_voucher(array $pedido, array $items): string
{
  $pdf = new FPDF('P', 'mm', 'A4');
  $pdf->AddPage();
  $pdf->SetAutoPageBreak(true, 20);

  // ── Paleta de colores ──
  $rojo_r = 200;
  $rojo_g = 16;
  $rojo_b = 46;
  $osc_r  = 26;
  $osc_g  = 26;
  $osc_b  = 26;
  $gris_r = 90;
  $gris_g = 90;
  $gris_b = 90;
  $lin_r  = 220;
  $lin_g  = 220;
  $lin_b  = 220;

  // ── ENCABEZADO con fondo rojo ──
  $pdf->SetFillColor($rojo_r, $rojo_g, $rojo_b);
  $pdf->Rect(0, 0, 210, 44, 'F');

  $pdf->SetTextColor(255, 255, 255);
  $pdf->SetFont('Arial', 'B', 22);
  $pdf->SetXY(15, 7);
  $pdf->Cell(130, 11, 'RESTAURANTE BRISAMAR', 0, 0, 'L');

  // Número de pedido — esquina derecha
  $pdf->SetFont('Arial', 'B', 13);
  $pdf->SetXY(130, 7);
  $pdf->Cell(65, 11, '# ' . str_pad($pedido['id_pedido'], 6, '0', STR_PAD_LEFT), 0, 1, 'R');

  $pdf->SetFont('Arial', '', 10);
  $pdf->SetXY(15, 20);
  $pdf->Cell(130, 7, _fpdf_str('Los mejores sabores del mar, directo a tu mesa'), 0, 0, 'L');

  $pdf->SetFont('Arial', 'B', 11);
  $pdf->SetXY(15, 30);
  $pdf->Cell(0, 7, 'COMPROBANTE DE PEDIDO', 0, 1, 'L');

  // ── BLOQUE INFO CLIENTE / PEDIDO ──
  $pdf->SetFillColor(248, 248, 248);
  $pdf->SetDrawColor($lin_r, $lin_g, $lin_b);
  $pdf->SetLineWidth(0.3);

  $box_y = 50;
  $pdf->Rect(15, $box_y, 180, 48, 'FD');

  // Columna izquierda — Datos del cliente
  $pdf->SetFont('Arial', 'B', 9);
  $pdf->SetTextColor($gris_r, $gris_g, $gris_b);
  $pdf->SetXY(20, $box_y + 4);
  $pdf->Cell(80, 5, 'DATOS DEL CLIENTE', 0, 1);

  $campos_izq = [
    ['Nombre',   $pedido['nombre'] ?? '-'],
    ['Correo',   $pedido['correo'] ?? '-'],
    ['Telefono', $pedido['telefono'] ?? '-'],
    ['Dir.',     $pedido['direccion'] ?? '-'],
  ];
  $y_col = $box_y + 11;
  foreach ($campos_izq as [$lbl, $val]) {
    $pdf->SetXY(20, $y_col);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor($gris_r, $gris_g, $gris_b);
    $pdf->Cell(22, 5, $lbl . ':', 0);
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetTextColor($osc_r, $osc_g, $osc_b);
    // Truncar texto largo
    $val_safe = _fpdf_str(mb_strimwidth($val, 0, 35, '...'));
    $pdf->Cell(73, 5, $val_safe, 0);
    $y_col += 8;
  }

  // Columna derecha — Datos del pedido
  $pdf->SetFont('Arial', 'B', 9);
  $pdf->SetTextColor($gris_r, $gris_g, $gris_b);
  $pdf->SetXY(108, $box_y + 4);
  $pdf->Cell(80, 5, 'DATOS DEL PEDIDO', 0, 1);

  $fecha_fmt = '';
  if (!empty($pedido['fecha'])) {
    try {
      $fecha_fmt = date('d/m/Y H:i', strtotime($pedido['fecha']));
    } catch (\Exception $e) {
      $fecha_fmt = $pedido['fecha'];
    }
  }

  $campos_der = [
    ['Fecha',   $fecha_fmt],
    ['Pedido',  '# ' . str_pad($pedido['id_pedido'], 6, '0', STR_PAD_LEFT)],
    ['Estado',  ucfirst($pedido['estado'] ?? 'pendiente')],
    ['Pago',    ucfirst($pedido['metodo_pago'] ?? 'efectivo')],
  ];
  $y_col = $box_y + 11;
  foreach ($campos_der as [$lbl, $val]) {
    $pdf->SetXY(108, $y_col);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor($gris_r, $gris_g, $gris_b);
    $pdf->Cell(22, 5, $lbl . ':', 0);
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetTextColor($osc_r, $osc_g, $osc_b);
    $pdf->Cell(60, 5, _fpdf_str($val), 0);
    $y_col += 8;
  }

  // ── TABLA DE PRODUCTOS ──
  $pdf->SetY(106);
  $pdf->SetLineWidth(0.2);

  // Cabecera
  $pdf->SetFillColor($rojo_r, $rojo_g, $rojo_b);
  $pdf->SetTextColor(255, 255, 255);
  $pdf->SetFont('Arial', 'B', 9);
  $pdf->SetDrawColor(180, 10, 30);
  $pdf->SetX(15);
  $pdf->Cell(82, 8, 'PRODUCTO', 1, 0, 'L', true);
  $pdf->Cell(24, 8, 'CANT.', 1, 0, 'C', true);
  $pdf->Cell(35, 8, 'P. UNIT.', 1, 0, 'R', true);
  $pdf->Cell(34, 8, 'SUBTOTAL', 1, 1, 'R', true);

  // Filas de productos
  $pdf->SetTextColor($osc_r, $osc_g, $osc_b);
  $pdf->SetFont('Arial', '', 9);
  $pdf->SetDrawColor($lin_r, $lin_g, $lin_b);
  $fill = false;
  foreach ($items as $item) {
    $bg_r = $fill ? 248 : 255;
    $pdf->SetFillColor($bg_r, $bg_r, $bg_r);
    $pdf->SetX(15);
    $nombre_safe = _fpdf_str(mb_strimwidth($item['nombre'], 0, 40, '...'));
    $pdf->Cell(82, 7, $nombre_safe, 1, 0, 'L', $fill);
    $pdf->Cell(24, 7, (string)$item['cantidad'], 1, 0, 'C', $fill);
    $pdf->Cell(35, 7, 'S/ ' . number_format((float)$item['precio'], 2), 1, 0, 'R', $fill);
    $pdf->Cell(34, 7, 'S/ ' . number_format((float)$item['subtotal'], 2), 1, 1, 'R', $fill);
    $fill = !$fill;
  }

  // ── TOTALES ──
  $total         = (float)$pedido['total'];
  $subtotal_bruto = round(($total - 5.00) / 1.18, 2);
  $igv            = round($subtotal_bruto * 0.18, 2);
  $delivery       = 5.00;

  $pdf->SetFont('Arial', '', 9);
  $pdf->SetFillColor(248, 248, 248);
  $pdf->SetTextColor($osc_r, $osc_g, $osc_b);
  $pdf->SetDrawColor($lin_r, $lin_g, $lin_b);

  $pdf->SetX(15);
  $pdf->Cell(141, 7, _fpdf_str('Subtotal (sin IGV)'), 1, 0, 'R', false);
  $pdf->Cell(34, 7, 'S/ ' . number_format($subtotal_bruto, 2), 1, 1, 'R', false);

  $pdf->SetX(15);
  $pdf->Cell(141, 7, 'IGV (18%)', 1, 0, 'R', false);
  $pdf->Cell(34, 7, 'S/ ' . number_format($igv, 2), 1, 1, 'R', false);

  $pdf->SetX(15);
  $pdf->Cell(141, 7, 'Delivery', 1, 0, 'R', false);
  $pdf->Cell(34, 7, 'S/ ' . number_format($delivery, 2), 1, 1, 'R', false);

  // Fila TOTAL — fondo rojo
  $pdf->SetFillColor($rojo_r, $rojo_g, $rojo_b);
  $pdf->SetTextColor(255, 255, 255);
  $pdf->SetFont('Arial', 'B', 11);
  $pdf->SetDrawColor(180, 10, 30);
  $pdf->SetX(15);
  $pdf->Cell(141, 10, 'TOTAL', 1, 0, 'R', true);
  $pdf->Cell(34,  10, 'S/ ' . number_format($total, 2), 1, 1, 'R', true);

  // ── PIE DE PÁGINA ──
  $pdf->SetY(-22);
  $pdf->SetDrawColor($rojo_r, $rojo_g, $rojo_b);
  $pdf->SetLineWidth(0.5);
  $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
  $pdf->Ln(2);
  $pdf->SetFont('Arial', '', 8);
  $pdf->SetTextColor($gris_r, $gris_g, $gris_b);
  $pdf->SetX(15);
  $pdf->Cell(0, 5, 'Restaurante Brisamar  |  RestaurantesBrisamar@gmail.com  |  ' . (defined('CONTACT_PHONE') ? CONTACT_PHONE : '+51 917 328 085'), 0, 1, 'C');
  $pdf->SetX(15);
  $pdf->Cell(0, 5, _fpdf_str('Gracias por su preferencia. Este comprobante es valido como constancia de pedido.'), 0, 1, 'C');

  // Devolver PDF como string (sin guardar en disco)
  return $pdf->Output('S');
}

/**
 * Envía el voucher PDF al correo del cliente vía Gmail SMTP (PHPMailer).
 *
 * @param array  $pedido         Datos del pedido
 * @param array  $items          Productos del pedido
 * @param string $correo_cliente Email del destinatario
 * @param string $nombre_cliente Nombre del cliente para el saludo
 * @return array ['ok' => bool, 'error' => string|null]
 */
function enviar_voucher_email(array $pedido, array $items, string $correo_cliente, string $nombre_cliente): array
{
  try {
    // 1. Generar PDF
    $pdf_content = generar_pdf_voucher($pedido, $items);

    // 2. Configurar PHPMailer
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = (int) SMTP_PORT;
    $mail->CharSet    = PHPMailer::CHARSET_UTF8;
    $mail->Encoding   = PHPMailer::ENCODING_BASE64;

    // Timeout generoso para evitar errores de conexión
    $mail->Timeout = 30;

    // 3. Remitente y destinatario
    $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
    $mail->addAddress($correo_cliente, $nombre_cliente);

    // Copia oculta al restaurante (no mostrar al cliente)
    if (defined('NOTIFICATION_RECIPIENT_EMAIL_ADMIN') && NOTIFICATION_RECIPIENT_EMAIL_ADMIN !== $correo_cliente) {
      $mail->addBCC(NOTIFICATION_RECIPIENT_EMAIL_ADMIN, 'Restaurante Brisamar Admin');
    }

    // 4. Adjuntar PDF en memoria
    $nombre_archivo = 'Voucher_Brisamar_' . str_pad($pedido['id_pedido'], 6, '0', STR_PAD_LEFT) . '.pdf';
    $mail->addStringAttachment($pdf_content, $nombre_archivo, PHPMailer::ENCODING_BASE64, 'application/pdf');

    // 5. Asunto
    $mail->Subject = '🎉 Tu pedido #' . str_pad($pedido['id_pedido'], 6, '0', STR_PAD_LEFT) . ' ha sido confirmado - Restaurante Brisamar';

    // 6. Construir tabla HTML de productos
    $items_html = '';
    foreach ($items as $it) {
      $items_html .= sprintf(
        '<tr>
                    <td style="padding:10px 14px;border-bottom:1px solid #2a2a2a;color:#fff;">%s</td>
                    <td style="padding:10px 14px;border-bottom:1px solid #2a2a2a;color:#fff;text-align:center;">%d</td>
                    <td style="padding:10px 14px;border-bottom:1px solid #2a2a2a;color:#fff;text-align:right;">S/ %s</td>
                    <td style="padding:10px 14px;border-bottom:1px solid #2a2a2a;color:#c8102e;text-align:right;font-weight:700;">S/ %s</td>
                </tr>',
        htmlspecialchars((string)$it['nombre']),
        (int)$it['cantidad'],
        number_format((float)$it['precio'], 2),
        number_format((float)$it['subtotal'], 2)
      );
    }

    $fecha_legible = !empty($pedido['fecha'])
      ? date('d/m/Y H:i', strtotime($pedido['fecha']))
      : date('d/m/Y H:i');

    $nombre_esc    = htmlspecialchars($nombre_cliente);
    $id_fmt        = str_pad((int)$pedido['id_pedido'], 6, '0', STR_PAD_LEFT);
    $total_fmt     = number_format((float)$pedido['total'], 2);
    $pago_lbl      = ucfirst($pedido['metodo_pago_label'] ?? $pedido['metodo_pago'] ?? 'efectivo');
    $direccion_esc = htmlspecialchars($pedido['direccion'] ?? '');

    // 7. Cuerpo HTML del correo
    $mail->isHTML(true);
    $mail->Body = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Pedido Confirmado - Restaurante Brisamar</title>
</head>
<body style="margin:0;padding:0;background:#0d0d0d;font-family:'Segoe UI',Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#0d0d0d;padding:30px 0;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#1a1a1a;border-radius:16px;overflow:hidden;border:1px solid #2a2a2a;">

        <!-- Header -->
        <tr>
          <td style="background:linear-gradient(135deg,#c8102e 0%,#8b0000 100%);padding:36px 32px;text-align:center;">
            <div style="font-size:32px;margin-bottom:8px;">🦞</div>
            <h1 style="margin:0;color:#fff;font-size:24px;font-weight:900;letter-spacing:1px;">RESTAURANTE BRISAMAR</h1>
            <p style="margin:8px 0 0;color:rgba(255,255,255,.7);font-size:13px;">Los mejores sabores del mar, directo a tu mesa</p>
          </td>
        </tr>

        <!-- Confirmación -->
        <tr>
          <td style="padding:28px 32px 16px;">
            <div style="background:#111;border:1px solid #2a2a2a;border-radius:12px;padding:20px 24px;text-align:center;">
              <div style="font-size:44px;margin-bottom:8px;">✅</div>
              <h2 style="margin:0;color:#fff;font-size:20px;">¡Pedido Confirmado!</h2>
              <p style="margin:10px 0 0;color:rgba(255,255,255,.55);font-size:13px;">
                Hola <strong style="color:#fff;">{$nombre_esc}</strong>, tu pedido ha sido registrado exitosamente.
              </p>
            </div>
          </td>
        </tr>

        <!-- Número de pedido -->
        <tr>
          <td style="padding:0 32px 20px;">
            <div style="background:#111;border:2px solid #c8102e;border-radius:12px;padding:16px;text-align:center;">
              <div style="color:rgba(255,255,255,.4);font-size:11px;font-weight:600;letter-spacing:2px;text-transform:uppercase;">Número de Pedido</div>
              <div style="color:#fff;font-size:30px;font-weight:900;letter-spacing:3px;margin-top:6px;"># {$id_fmt}</div>
              <div style="color:rgba(255,255,255,.35);font-size:12px;margin-top:4px;">{$fecha_legible}</div>
            </div>
          </td>
        </tr>

        <!-- Tabla de productos -->
        <tr>
          <td style="padding:0 32px 8px;">
            <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #2a2a2a;border-radius:10px;overflow:hidden;">
              <tr style="background:#c8102e;">
                <th style="padding:10px 14px;color:#fff;font-size:11px;text-align:left;font-weight:700;">PRODUCTO</th>
                <th style="padding:10px 14px;color:#fff;font-size:11px;text-align:center;font-weight:700;">CANT.</th>
                <th style="padding:10px 14px;color:#fff;font-size:11px;text-align:right;font-weight:700;">P. UNIT.</th>
                <th style="padding:10px 14px;color:#fff;font-size:11px;text-align:right;font-weight:700;">SUBTOTAL</th>
              </tr>
              {$items_html}
            </table>
          </td>
        </tr>

        <!-- Resumen -->
        <tr>
          <td style="padding:12px 32px 24px;">
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td style="padding:7px 0;color:rgba(255,255,255,.5);font-size:13px;">Método de pago</td>
                <td style="padding:7px 0;color:#fff;font-size:13px;text-align:right;font-weight:600;">{$pago_lbl}</td>
              </tr>
              <tr>
                <td style="padding:7px 0;color:rgba(255,255,255,.5);font-size:13px;">Dirección de entrega</td>
                <td style="padding:7px 0;color:#fff;font-size:13px;text-align:right;font-weight:600;">{$direccion_esc}</td>
              </tr>
              <tr style="border-top:1px solid #2a2a2a;">
                <td style="padding:14px 0 4px;color:#fff;font-size:15px;font-weight:900;">TOTAL PAGADO</td>
                <td style="padding:14px 0 4px;color:#c8102e;font-size:22px;font-weight:900;text-align:right;">S/ {$total_fmt}</td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Nota adjunto -->
        <tr>
          <td style="padding:0 32px 28px;">
            <div style="background:rgba(200,16,46,.1);border:1px solid rgba(200,16,46,.3);border-radius:10px;padding:14px 18px;text-align:center;">
              <p style="margin:0;color:rgba(255,255,255,.75);font-size:12px;">
                📎 <strong style="color:#fff;">Tu voucher PDF está adjunto</strong> a este correo.
                Guárdalo como constancia de tu pedido.
              </p>
            </div>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="background:#111;padding:20px 32px;text-align:center;border-top:1px solid #2a2a2a;">
            <p style="margin:0;color:rgba(255,255,255,.3);font-size:11px;">
              Restaurante Brisamar &bull; RestaurantesBrisamar@gmail.com &bull; +51 917 328 085
            </p>
            <p style="margin:6px 0 0;color:rgba(255,255,255,.2);font-size:10px;">
              Este correo fue generado automáticamente. Por favor no respondas directamente.
            </p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;

    // Versión texto plano (fallback)
    $mail->AltBody = "Pedido #{$id_fmt} confirmado - Restaurante Brisamar\n"
      . "Cliente: {$nombre_cliente}\n"
      . "Total: S/ {$total_fmt}\n"
      . "Tu voucher PDF está adjunto a este correo.";

    $mail->send();
    return ['ok' => true, 'error' => null];
  } catch (MailerException $e) {
    return ['ok' => false, 'error' => isset($mail) ? $mail->ErrorInfo : $e->getMessage()];
  } catch (\Exception $e) {
    return ['ok' => false, 'error' => $e->getMessage()];
  }
}
