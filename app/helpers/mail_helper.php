<?php
/**
 * Helper de Correo: envío de correos vía SMTP usando PHPMailer vendoreado
 * manualmente en app/libs/PHPMailer (proyecto sin Composer). Configuración
 * en config/config.php (SMTP_HOST/PORT/USER/PASS/ENCRYPTION/FROM_*).
 */

/** Content-ID con el que se incrusta el logo; la plantilla lo referencia como `cid:`. */
const SIGTUR_CORREO_LOGO_CID = 'logo_imatur';

/**
 * Envía un correo HTML. Devuelve false y registra el error en el log del
 * servidor si falla (nunca expone detalles SMTP al usuario final).
 *
 * Si el cuerpo usa el logo (`cid:logo_imatur`), se adjunta incrustado: el
 * sistema es on-premise, así que una URL a URL_ROOT no se vería desde el
 * buzón del destinatario. $textoPlano es la versión sin HTML (AltBody).
 */
function sigtur_enviar_correo(string $para, string $asunto, string $cuerpoHtml, string $textoPlano = ''): bool {
    require_once APP_ROOT . '/app/libs/PHPMailer/Exception.php';
    require_once APP_ROOT . '/app/libs/PHPMailer/SMTP.php';
    require_once APP_ROOT . '/app/libs/PHPMailer/PHPMailer.php';

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->Port       = SMTP_PORT;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->CharSet    = 'UTF-8';
        // Remitente: el correo institucional oficial (mismo que aparece en
        // oficios/constancias, ConfigSistema) si está configurado; si no,
        // el placeholder de config.php.
        $remitente = trim((string) ConfigSistema::get('correo_institucion'));
        $mail->setFrom($remitente !== '' ? $remitente : SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($para);
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $cuerpoHtml;
        if ($textoPlano !== '') $mail->AltBody = $textoPlano;

        $logo = APP_ROOT . '/public/assets/images/logo_correo.png';
        if (strpos($cuerpoHtml, 'cid:' . SIGTUR_CORREO_LOGO_CID) !== false && is_file($logo)) {
            $mail->addEmbeddedImage($logo, SIGTUR_CORREO_LOGO_CID, 'logo_imatur.png', 'base64', 'image/png');
        }

        $mail->send();
        return true;
    } catch (\Throwable $e) {
        error_log('[SIGTUR] Error enviando correo: ' . $e->getMessage());
        return false;
    }
}

/**
 * Envuelve un contenido en la plantilla institucional: logo, tarjeta blanca
 * y pie con el nombre del instituto. Tablas y estilos en línea a propósito
 * — es lo único que Gmail y Outlook respetan. $contenidoHtml ya debe venir
 * escapado; $preencabezado es el texto que el buzón muestra junto al asunto.
 *
 * $boton = ['texto' => ..., 'url' => ...] agrega un botón de llamada a la acción.
 * $notaHtml (ya escapado) va debajo del botón, separado por una línea, en letra menor.
 */
function sigtur_plantilla_correo(string $titulo, string $contenidoHtml, ?array $boton = null,
                                 string $preencabezado = '', string $notaHtml = ''): string {
    $e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    $fuente = "font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;";

    $htmlBoton = '';
    if ($boton) {
        $htmlBoton = '
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:28px auto 8px;">
              <tr><td align="center" bgcolor="#2247db" style="border-radius:8px;">
                <a href="' . $e($boton['url']) . '" target="_blank"
                   style="display:inline-block;padding:13px 30px;' . $fuente . 'font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:8px;">'
                   . $e($boton['texto']) . '</a>
              </td></tr>
            </table>';
    }

    $htmlNota = $notaHtml === '' ? '' :
        '<div style="margin:24px 0 0;padding-top:18px;border-top:1px solid #e2e8f0;font-size:13px;line-height:1.6;color:#64748b;">'
        . $notaHtml . '</div>';

    $direccion = trim((string) ConfigSistema::get('direccion_institucion'));
    $rif       = trim((string) ConfigSistema::get('rif_institucional'));
    $pie       = 'Instituto Municipal Autónomo de Turismo (IMATUR-SUCRE)'
               . ($rif !== '' ? ' · RIF ' . $e($rif) : '')
               . ($direccion !== '' ? '<br>' . $e($direccion) : '');

    return '<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . $e($titulo) . '</title></head>
<body style="margin:0;padding:0;background:#eef2f7;">
  <div style="display:none;max-height:0;overflow:hidden;opacity:0;">' . $e($preencabezado) . '</div>
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#eef2f7;">
    <tr><td align="center" style="padding:32px 16px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:560px;">
        <tr><td align="center" style="padding-bottom:20px;">
          <img src="cid:' . SIGTUR_CORREO_LOGO_CID . '" width="88" alt="IMATUR" style="display:block;border:0;width:88px;height:auto;">
        </td></tr>
        <tr><td style="background:#ffffff;border-radius:12px;border-top:4px solid #2247db;padding:36px 36px 30px;' . $fuente . 'color:#1e293b;font-size:15px;line-height:1.6;">
          <h1 style="margin:0 0 18px;font-size:21px;font-weight:700;color:#141d47;">' . $e($titulo) . '</h1>
          ' . $contenidoHtml . $htmlBoton . $htmlNota . '
        </td></tr>
        <tr><td align="center" style="padding:22px 12px 0;' . $fuente . 'font-size:12px;line-height:1.6;color:#64748b;">
          <strong style="color:#475569;">SIGTUR-IMATUR</strong> · Sistema de Gestión<br>' . $pie . '<br>
          <span style="color:#94a3b8;">Este es un mensaje automático; por favor no lo respondas.</span>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body></html>';
}
