<?php
require_once __DIR__ . '/conexion.php';

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
    $asunto = "✅ Tu solicitud ha sido aprobada - ShoppingGenerico";
    
    $mensajeAccion = ($credenciales['accion'] == 'actualizado') 
        ? "Tu cuenta existente ha sido actualizada a comerciante." 
        : "Se ha creado una nueva cuenta de comerciante para ti.";
    
    $mensaje = "
    <html>
    <body>
        <h2>¡Felicidades, {$solicitud['nombre']}!</h2>
        <p>Tu solicitud ha sido aprobada.</p>
        <p>{$mensajeAccion}</p>
        <h3>Tus Credenciales:</h3>
        <p>Email: {$credenciales['email']}</p>
        <p>Contraseña: (La que ingresaste en la solicitud)</p>
        <h3>Tu Local:</h3>
        <p>Nombre: {$solicitud['nombreLocal']}</p>
        <p>Código: {$localCodigo}</p>
        <p><a href='http://localhost/ShoppingGenerico/login.php'>Iniciar Sesión</a></p>
    </body>
    </html>
    ";
    
    return enviarEmail($destinatario, $asunto, $mensaje);
}

function enviarEmailRechazo($solicitud) {
    $destinatario = $solicitud['email'];
    $asunto = "❌ Actualización sobre tu solicitud";
    $mensaje = "Hola {$solicitud['nombre']}, lamentamos informar que tu solicitud no ha sido aprobada.";
    return enviarEmail($destinatario, $asunto, $mensaje);
}

function enviarEmail($destinatario, $asunto, $mensaje) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: no-reply@shoppinggenerico.com" . "\r\n";
    return mail($destinatario, $asunto, $mensaje, $headers);
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