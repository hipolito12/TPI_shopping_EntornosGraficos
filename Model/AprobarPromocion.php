<?php

require_once '../Model/conexion.php';
function getPromocionesPendientes() {
    $pdo = getConnection();
    $query = "
        SELECT 
            p.*,
            l.nombre as local_nombre,
            l.rubro as local_rubro,
            l.codigo as local_codigo,
            u.nombre as ubicacion_nombre,
            us.nombreUsuario as comerciante_nombre
        FROM promocion p
        INNER JOIN local l ON p.localFk = l.IDlocal
        LEFT JOIN ubicacion u ON l.ubicacionFK = u.IDubicacion
        INNER JOIN usuario us ON l.usuarioFK = us.IDusuario
        WHERE p.estado = '0'
        ORDER BY p.IDpromocion DESC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPromocionesAprobadas() {
    $pdo = getConnection();
    $query = "
        SELECT 
            p.*,
            l.nombre as local_nombre,
            l.rubro as local_rubro,
            l.codigo as local_codigo,
            u.nombre as ubicacion_nombre,
            us.nombreUsuario as comerciante_nombre
        FROM promocion p
        INNER JOIN local l ON p.localFk = l.IDlocal
        LEFT JOIN ubicacion u ON l.ubicacionFK = u.IDubicacion
        INNER JOIN usuario us ON l.usuarioFK = us.IDusuario
        WHERE p.estado = '1'
        ORDER BY p.IDpromocion DESC
        LIMIT 50
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPromocionesRechazadas() {
    $pdo = getConnection();
    $query = "
        SELECT 
            p.*,
            l.nombre as local_nombre,
            l.rubro as local_rubro,
            l.codigo as local_codigo,
            u.nombre as ubicacion_nombre,
            us.nombreUsuario as comerciante_nombre
        FROM promocion p
        INNER JOIN local l ON p.localFk = l.IDlocal
        LEFT JOIN ubicacion u ON l.ubicacionFK = u.IDubicacion
        INNER JOIN usuario us ON l.usuarioFK = us.IDusuario
        WHERE p.estado = '2'
        ORDER BY p.IDpromocion DESC
        LIMIT 50
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function actualizarEstadoPromocion($idPromocion, $estado) {
    $pdo = getConnection();
    $query = "UPDATE promocion SET estado = ? WHERE IDpromocion = ?";
    $stmt = $pdo->prepare($query);
    return $stmt->execute([$estado, $idPromocion]);
}

function getEstadisticasPromociones() {
    $pdo = getConnection();
    
    $stats = [];
    
    $query = "SELECT COUNT(*) as total FROM promocion WHERE estado = '0'";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stats['pendientes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $query = "SELECT COUNT(*) as total FROM promocion WHERE estado = '1'";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stats['aprobadas_total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $query = "SELECT COUNT(*) as total FROM promocion WHERE estado = '2'";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stats['rechazadas_total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $query = "SELECT COUNT(*) as total FROM promocion WHERE estado = '1' AND hasta >= CURDATE()";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stats['activas'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Tasa de aprobación
    $total_revisadas = $stats['aprobadas_total'] + $stats['rechazadas_total'];
    $stats['tasa_aprobacion'] = $total_revisadas > 0 ? round(($stats['aprobadas_total'] / $total_revisadas) * 100, 1) : 0;
    
    return $stats;
}

function getNombreDia($numeroDia) {
    $dias = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo'
    ];
    return $dias[$numeroDia] ?? 'Día no especificado';
}

function enviarEmailAprobacionPromocion($promocion, $comercianteEmail) {
    $destinatario = $comercianteEmail;
    $asunto = "✅ Tu promoción ha sido aprobada - ShoppingGenerico";
    
    $mensaje = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #4A3BC7; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .info-box { background: white; padding: 15px; margin: 10px 0; border-left: 4px solid #4A3BC7; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>¡Tu promoción ha sido aprobada!</h1>
                <p>Ya está disponible para tus clientes</p>
            </div>
            <div class='content'>
                <p>Te informamos que la promoción de tu local ha sido <strong>aprobada</strong> y ya está visible para los clientes.</p>
                
                <div class='info-box'>
                    <h3>📋 Detalles de la promoción:</h3>
                    <p><strong>Local:</strong> {$promocion['local_nombre']}</p>
                    <p><strong>Descripción:</strong> {$promocion['descripcion']}</p>
                    <p><strong>Válida desde:</strong> " . date('d/m/Y', strtotime($promocion['desde'])) . "</p>
                    <p><strong>Válida hasta:</strong> " . date('d/m/Y', strtotime($promocion['hasta'])) . "</p>
                    <p><strong>Día de aplicación:</strong> " . getNombreDia($promocion['dia']) . "</p>
                    <p><strong>Categoría habilitada:</strong> {$promocion['categoriaHabilitada']}</p>
                </div>
                
                <p>Los clientes que cumplan con los requisitos podrán ver y utilizar esta promoción desde ahora.</p>
            </div>
            <div class='footer'>
                <p>Saludos cordiales,<br>El equipo de ShoppingGenerico</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return enviarEmail($destinatario, $asunto, $mensaje);
}

function enviarEmailRechazoPromocion($promocion, $comercianteEmail) {
    $destinatario = $comercianteEmail;
    $asunto = "❌ Tu promoción ha sido revisada - ShoppingGenerico";
    
    $mensaje = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .info-box { background: white; padding: 15px; margin: 10px 0; border-left: 4px solid #dc3545; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Actualización sobre tu promoción</h1>
            </div>
            <div class='content'>
                <p>Lamentamos informarte que después de revisar tu promoción, hemos decidido <strong>no aprobarla</strong> en este momento.</p>
                
                <div class='info-box'>
                    <h3>📋 Detalles de la promoción:</h3>
                    <p><strong>Local:</strong> {$promocion['local_nombre']}</p>
                    <p><strong>Descripción:</strong> {$promocion['descripcion']}</p>
                    <p><strong>Período solicitado:</strong> " . date('d/m/Y', strtotime($promocion['desde'])) . " al " . date('d/m/Y', strtotime($promocion['hasta'])) . "</p>
                </div>
                
                <p>Esto puede deberse a:</p>
                <ul>
                    <li>No cumplir con las políticas comerciales del shopping</li>
                    <li>Superposición con otras promociones similares</li>
                    <li>Necesidad de ajustes en los términos y condiciones</li>
                </ul>
                
                <p>Puedes crear una nueva promoción ajustando los términos según nuestras políticas.</p>
                
                <p>Agradecemos tu comprensión.</p>
            </div>
            <div class='footer'>
                <p>Saludos cordiales,<br>El equipo de ShoppingGenerico</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return enviarEmail($destinatario, $asunto, $mensaje);
}

function enviarEmail($destinatario, $asunto, $mensaje) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: no-reply@shoppinggenerico.com" . "\r\n";
    $headers .= "Reply-To: admin@shoppinggenerico.com" . "\r\n";
    
    return mail($destinatario, $asunto, $mensaje, $headers);
}


function GetPromocionesID($idPromocion){

$pdo = getConnection();
    $query = "
        SELECT p.*, l.nombre as local_nombre, u.email as comerciante_email 
        FROM promocion p
        INNER JOIN local l ON p.localFk = l.IDlocal
        INNER JOIN usuario u ON l.usuarioFK = u.IDusuario
        WHERE p.IDpromocion = ?
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$idPromocion]);
    return  $stmt->fetch(PDO::FETCH_ASSOC);

}
?>