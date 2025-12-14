<?php
include_once("../Model/ProcesarRegistroUS.php");
include_once("../Model/Sendmail.php");

function error($mensaje) {
    throw new Exception($mensaje);
}

header('Content-Type: application/json; charset=utf-8');

try {
    if($_SERVER["REQUEST_METHOD"] == "POST") {

        $nombre = trim($_POST['nombre'] ?? "");
        $apellido = trim($_POST['apellido'] ?? "");
        $email = filter_var(trim($_POST['email'] ?? ""), FILTER_VALIDATE_EMAIL);
        $email2 = filter_var(trim($_POST["email2"] ?? ""), FILTER_VALIDATE_EMAIL);
        $pwd = trim($_POST["password"] ?? "");
        $pwd2 = trim($_POST["password2"] ?? "");
        $dni = trim($_POST["dni"] ?? "");
        $sexo = trim($_POST["sexo"] ?? "");
        $tel = trim($_POST["telefono"] ?? "");

        $nombreCompleto = $nombre . " " . $apellido;

        $regex = [
            'nombre'    => '/^[A-Za-zÁÉÍÓÚÜÄËÏÖÑáéíóúüäëïöñ\' ]{2,50}$/',
            'email'     => '/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/',
            'pass'      => '/^(?=.*[A-Z])(?=.*\d).{8,}$/',
            'dni'       => '/^\d{8}$/',
            'telefono'  => '/^\d{7,20}$/',
            'sexo'      => '/^(Femenino|Masculino|Otro|Prefiero\sno\sdicirlo)$/'
        ];
          
     if (!preg_match($regex['nombre'], $nombreCompleto)) {
            error('Solo se permiten letras, espacios y tildes, de 2 a 50 caracteres.');
        }

        if ($email !== $email2 || $email == false || $email2 == false) {
            error('El email no es válido o no coinciden.');
        }

       if (ExisteUsuario(username: $nombreCompleto, email: $email) == true) {
            error('El email ya está registrado.');
        }


        if ($pwd != $pwd2 || !preg_match($regex['pass'], $pwd) || !preg_match($regex['pass'], $pwd2)) {
            error('Las contraseñas no coinciden o no cumplen los requisitos.');
        }

        if (!preg_match($regex["sexo"], $sexo)) {
            error("Sexo no válido.");
        }

        if (!preg_match($regex["telefono"], $tel)) {
            error("El teléfono debe tener entre 7 y 20 dígitos.");
        }

        if (!preg_match($regex["dni"], $dni)) {
            error("DNI inválido.");
        }

         $id=insertarUsuario($nombreCompleto,$email,$pwd,$tel,$sexo,$dni);
        // Email de prueba (temporal)
        $sendmail = new Sendmail();
        $enviado = $sendmail->EnviaMail($email);

        if ($enviado) {
            echo json_encode([
                'ok' => true,
                'redirect' => "../View/MailSent.php",
                'message' => 'Datos validados correctamente.'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'ok' => false,
                'message' => 'Error al enviar correo. Intenta nuevamente.'
            ], JSON_UNESCAPED_UNICODE);
        }
      
    }
} catch (Exception $e) {
    echo json_encode([
        'ok' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
