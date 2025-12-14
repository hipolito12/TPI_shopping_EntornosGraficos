<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
function getSolicitudesPendientes() {
    $pdo = getConnection();
    $query = "
        SELECT 
            s.*,
            u.nombre as ubicacion_nombre,
            u.Descripcion as ubicacion_descripcion
        FROM solicitud s
        LEFT JOIN ubicacion u ON s.ubicacion = u.IDubicacion
        WHERE s.estado = 0
        ORDER BY s.IDsolicitud DESC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSolicitudesAprobadas() {
    $pdo = getConnection();
    $query = "
        SELECT 
            s.*,
            u.nombre as ubicacion_nombre
        FROM solicitud s
        LEFT JOIN ubicacion u ON s.ubicacion = u.IDubicacion
        WHERE s.estado = 1
        ORDER BY s.IDsolicitud DESC
        LIMIT 50
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSolicitudesRechazadas() {
    $pdo = getConnection();
    $query = "
        SELECT 
            s.*,
            u.nombre as ubicacion_nombre
        FROM solicitud s
        LEFT JOIN ubicacion u ON s.ubicacion = u.IDubicacion
        WHERE s.estado = 2
        ORDER BY s.IDsolicitud DESC
        LIMIT 50
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Acepta $pdo para mantener la transacción
function crearOActualizarUsuarioComerciante($datosSolicitud, $pdo) {
    
    // Verificar si el usuario existe por DNI
    $queryCheck = "SELECT IDusuario, tipoFK, nombreUsuario, email FROM usuario WHERE DNI = ?";
    $stmtCheck = $pdo->prepare($queryCheck);
    $stmtCheck->execute([$datosSolicitud['dni']]);
    $usuarioExistente = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    
    if ($usuarioExistente) {
        // Usuario existe -> Actualizar a Comerciante (Rol 2)
        $queryUpdate = "UPDATE usuario SET tipoFK = 2 WHERE IDusuario = ?";
        $stmtUpdate = $pdo->prepare($queryUpdate);
        $stmtUpdate->execute([$usuarioExistente['IDusuario']]);
        
        return [
            'id' => $usuarioExistente['IDusuario'],
            'accion' => 'actualizado',
            'email' => $usuarioExistente['email'],
            'nombreUsuario' => $usuarioExistente['nombreUsuario']
        ];
    } else {
        // Usuario NUEVO -> Crear con Rol 2 y Hash
        
        // 1. Encriptar contraseña
        $claveHash = password_hash($datosSolicitud['contraseña'], PASSWORD_DEFAULT);
        
        // 2. Insertar (tipoFK = 2 es Comerciante)
        $query = "
            INSERT INTO usuario 
            (nombreUsuario, email, clave, telefono, Sexo, tipoFK, categoriaFK, estado, DNI) 
            VALUES (?, ?, ?, ?, ?, 2, 1, 1, ?)
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            $datosSolicitud['nombre'],
            $datosSolicitud['email'],
            $claveHash,
            $datosSolicitud['telefono'],
            $datosSolicitud['sexo'],
            $datosSolicitud['dni']
        ]);
        
        return [
            'id' => $pdo->lastInsertId(),
            'accion' => 'creado',
            'email' => $datosSolicitud['email'],
            'nombreUsuario' => $datosSolicitud['nombre']
        ];
    }
}

function crearLocalDesdeSolicitud($datosSolicitud, $usuarioId, $pdo) {
    // Generar código único
    $codigo = 'LOCAL_' . strtoupper(uniqid());
    
    $query = "
        INSERT INTO local 
        (nombre, rubro, usuarioFK, ubicacionFK, codigo) 
        VALUES (?, ?, ?, ?, ?)
    ";
    $stmt = $pdo->prepare($query);
    $resultado = $stmt->execute([
        $datosSolicitud['nombreLocal'],
        $datosSolicitud['rubro'],
        $usuarioId,
        $datosSolicitud['ubicacion'],
        $codigo
    ]);
    
    if ($resultado) {
        return $codigo;
    }
    return false;
}

function actualizarEstadoSolicitud($idSolicitud, $estado, $pdo) {
    $query = "UPDATE solicitud SET estado = ? WHERE IDsolicitud = ?";
    $stmt = $pdo->prepare($query);
    return $stmt->execute([$estado, $idSolicitud]);
}

function enviarEmailAprobacion($solicitud, $localCodigo, $credenciales) {
    $destinatario = $solicitud['email'];
    $asunto = "✅ Tu solicitud ha sido aprobada - Shopping UTN";
    
    // Detectamos el dominio automáticamente (localhost o tu web en Infinity)
    $dominio = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    $mensajeAccion = ($credenciales['accion'] == 'actualizado') 
        ? "Tu cuenta existente ha sido actualizada a comerciante." 
        : "Se ha creado una nueva cuenta de comerciante para ti.";
    
    $mensaje = "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <h2 style='color: #4A3BC7;'>¡Felicidades, {$solicitud['nombre']}!</h2>
        <p>Nos alegra informarte que <strong>tu solicitud ha sido aprobada</strong>.</p>
        <p>{$mensajeAccion}</p>
        
        <div style='background-color: #f8f9fa; padding: 15px; border-left: 4px solid #4A3BC7; margin: 20px 0;'>
            <h3 style='margin-top: 0;'>Tus Credenciales:</h3>
            <p><strong>Email:</strong> {$credenciales['email']}</p>
            <p><strong>Contraseña:</strong> (La que ingresaste en el formulario de solicitud)</p>
            
            <h3 style='margin-bottom: 5px;'>Tu Local:</h3>
            <p><strong>Nombre:</strong> {$solicitud['nombreLocal']}</p>
            <p><strong>Código:</strong> {$localCodigo}</p>
        </div>

        <p style='text-align: center;'>
            <a href='http://{$dominio}/TPIShopping/View/login.php' 
               style='background-color: #4A3BC7; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>
               Iniciar Sesión
            </a>
        </p>
    </body>
    </html>
    ";
    
    return enviarEmail($destinatario, $asunto, $mensaje);
}

function enviarEmailRechazo($solicitud) {
    $destinatario = $solicitud['email'];
    $asunto = "❌ Actualización sobre tu solicitud - Shopping UTN";
    
    $mensaje = "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <h3>Hola {$solicitud['nombre']},</h3>
        <p>Gracias por tu interés en formar parte de Shopping UTN.</p>
        <p style='color: #dc3545;'>Lamentamos informar que tu solicitud no ha sido aprobada en esta ocasión.</p>
        <p>Si tienes dudas, puedes ponerte en contacto con nosotros a través de la sección de soporte.</p>
    </body>
    </html>";
    
    return enviarEmail($destinatario, $asunto, $mensaje);
}

// --- FUNCIÓN ADAPTADA PARA USAR PHPMAILER ---
function enviarEmail($destinatario, $asunto, $mensaje) {
    $mail = new PHPMailer(true);

    try {
        // Configuración del Servidor (Datos de tu sendmail.ini)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'labarbahipolito3@gmail.com';
        $mail->Password   = 'dzgr hgxl dnci guei';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // Remitente y Destinatario
        $mail->setFrom('labarbahipolito3@gmail.com', 'Shopping UTN');
        $mail->addAddress($destinatario);

        // Contenido
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $mensaje;

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Registrar el error para diagnóstico
        error_log('Error al enviar correo: ' . $e->getMessage());
        return false;
    }
}
function getEstadisticasSolicitudes() {
    $pdo = getConnection();
    $stats = [];
    
    $stats['pendientes'] = $pdo->query("SELECT COUNT(*) FROM solicitud WHERE estado = 0")->fetchColumn();
    $stats['aprobadas_total'] = $pdo->query("SELECT COUNT(*) FROM solicitud WHERE estado = 1")->fetchColumn();
    $stats['rechazadas_total'] = $pdo->query("SELECT COUNT(*) FROM solicitud WHERE estado = 2")->fetchColumn();
    
    $total = $stats['aprobadas_total'] + $stats['rechazadas_total'];
    $stats['tasa_aprobacion'] = $total > 0 ? round(($stats['aprobadas_total'] / $total) * 100, 1) : 0;
    
    return $stats;
}
?>