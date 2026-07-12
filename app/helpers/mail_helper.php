<?php
/**
 * Helper de Correo: envío de correos vía SMTP usando PHPMailer vendoreado
 * manualmente en app/libs/PHPMailer (proyecto sin Composer). Configuración
 * en config/config.php (SMTP_HOST/PORT/USER/PASS/ENCRYPTION/FROM_*).
 */

/**
 * Envía un correo HTML. Devuelve false y registra el error en el log del
 * servidor si falla (nunca expone detalles SMTP al usuario final).
 */
function sigtur_enviar_correo(string $para, string $asunto, string $cuerpoHtml): bool {
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
        $mail->send();
        return true;
    } catch (\Throwable $e) {
        error_log('[SIGTUR] Error enviando correo: ' . $e->getMessage());
        return false;
    }
}
