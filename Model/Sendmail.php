<?php
/*$to = "sitare8285@dotxan.com";  // Dirección de destino
$subject = "Asunto del mensaje";   // Asunto
$message = "Este es el cuerpo del mensaje."; // Cuerpo del mensaje
$headers = "From: labarbahipolito3@gmail.com" . "\r\n" .
           "Reply-To: labarbahipolito3@gmail.com" . "\r\n" ; // Cabeceras del correo*/

function EnviaMail($to)
{   $message = '<html><body style="font-family: Arial, sans-serif; color: #333;">
    <h2 style="color: #007bff;">¡Bienvenido a SHOPPING GENERICO!</h2>
    <p>Gracias por crear una cuenta con nosotros. Para confirmar tu registro, por favor haz clic en el siguiente botón:</p>
<a href="http://localhost/TPIShopping/Controller/ConfirmaMailController.php?mail=' . urlencode($to) . '" style="display: inline-block; padding: 12px 24px; background-color: #007bff; color: #fff; text-decoration: none; border-radius: 6px; font-weight: bold; margin: 16px 0;">Ir al sitio</a>    <p>Si tienes alguna duda contacta soporte desde nuestra web.</p>
    <hr>
    <p style="font-size: 12px; color: #888;">&copy; 2025 SHOPPING GENERICO. Todos los derechos reservados.</p>
    </body></html>';
    $subject = "Usted creó una cuenta en SHOPPING GENERICO, por favor confírmela";   
    $headers = "From: labarbahipolito3@gmail.com" . "\r\n" .
        "Reply-To: labarbahipolito3@gmail.com" . "\r\n" .
        "MIME-Version: 1.0\r\n" .
        "Content-type: text/html; charset=UTF-8\r\n";
    try {
     mail($to, $subject, $message, $headers);
         
        }
     catch (\Exception $e) {
        echo $e->getTrace();
    }
}
