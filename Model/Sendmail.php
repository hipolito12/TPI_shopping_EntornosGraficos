<?php
require __DIR__ . '/PHPMailer/Exception.php';
require __DIR__ . '/PHPMailer/PHPMailer.php';
require __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Sendmail {
    /**
     * Envía un correo de confirmación a la dirección indicada.
     * Retorna true si se envía correctamente, false en caso contrario.
     */
    public function EnviaMail($to) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();

            // Leer configuración desde variables de entorno, con valores por defecto
            $mail->Host = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
            $mail->SMTPAuth = filter_var(getenv('SMTP_AUTH') ?: 'true', FILTER_VALIDATE_BOOLEAN);
            $mail->Username = getenv('SMTP_USER') ?: '';
            $mail->Password = getenv('SMTP_PASS') ?: '';

            $secureEnv = strtolower(getenv('SMTP_SECURE') ?: 'tls');
            $mail->SMTPSecure = ($secureEnv === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;

            $mail->Port = intval(getenv('SMTP_PORT') ?: 587);
            $mail->CharSet = getenv('SMTP_CHARSET') ?: 'UTF-8';

            $fromAddr = getenv('SMTP_FROM') ?: '';
            $fromName = getenv('SMTP_FROM_NAME') ?: 'Shopping UTN';
            $mail->setFrom($fromAddr, $fromName);
            $mail->addAddress($to);

            $dominio = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $linkConfirmacion = "http://$dominio/View/ConfirmarMail.php?mail=" . urlencode($to);

            $cuerpoHTML = '<html><body style="font-family: Arial, sans-serif; color: #333;">'
                . '<h2 style="color: #007bff;">¡Bienvenido a SHOPPING GENERICO!</h2>'
                . '<p>Gracias por crear una cuenta con nosotros. Para confirmar tu registro, haz clic en el enlace abajo.</p>'
                . '<a href="' . $linkConfirmacion . '" style="display:inline-block;padding:12px 24px;background-color:#007bff;color:#fff;text-decoration:none;border-radius:6px;font-weight:bold;margin:16px 0;">Ir al sitio</a>'
                . '<hr><p style="font-size:12px;color:#888;">&copy; 2025 SHOPPING GENERICO. Todos los derechos reservados.</p>'
                . '</body></html>';

            $mail->isHTML(true);
            $mail->Subject = getenv('SMTP_SUBJECT') ?: 'Confirmá tu cuenta en SHOPPING GENERICO';
            $mail->Body = $cuerpoHTML;
            $mail->AltBody = "Bienvenido. Para confirmar tu cuenta, visita: $linkConfirmacion";

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Registrar/loguear si es necesario: error_log($e->getMessage());
            return false;
        }
    }
}
