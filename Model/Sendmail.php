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
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'labarbahipolito3@gmail.com';
            $mail->Password   = 'dzgr hgxl dnci guei';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom('labarbahipolito3@gmail.com', 'Shopping UTN');
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
            $mail->Subject = 'Confirmá tu cuenta en SHOPPING GENERICO';
            $mail->Body    = $cuerpoHTML;
            $mail->AltBody = "Bienvenido. Para confirmar tu cuenta, visita: $linkConfirmacion";

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
