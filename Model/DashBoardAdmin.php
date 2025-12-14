<?php

require_once '../Model/conexion.php';

function getEstadisticasGenerales() {
    $pdo = getConnection();
    
    $stats = [];
    
    $query = "SELECT COUNT(*) as total FROM local";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stats['total_locales'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $query = "SELECT COUNT(*) as total FROM solicitud";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stats['solicitudes_pendientes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $query = "SELECT COUNT(*) as total FROM promocion WHERE estado = 0";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stats['promociones_pendientes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $query = "SELECT COUNT(*) as total FROM novedad WHERE hasta >= CURDATE()";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stats['novedades_activas'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $query = "SELECT COUNT(*) as total FROM usopromocion WHERE fechaUso >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stats['usos_30_dias'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    return $stats;
}

function getLocales() {
    $pdo = getConnection();
    $query = "
        SELECT l.*, u.nombreUsuario as dueño, ub.nombre as ubicacion_nombre 
        FROM local l 
        LEFT JOIN usuario u ON l.usuarioFK = u.IDusuario 
        LEFT JOIN ubicacion ub ON l.ubicacionFK = ub.IDubicacion 
        ORDER BY l.nombre
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSolicitudesPendientes() {
    $pdo = getConnection();
    $query = "SELECT * FROM solicitud ORDER BY IDsolicitud DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPromocionesPendientes() {
    $pdo = getConnection();
    $query = "
        SELECT p.*, l.nombre as local_nombre, l.rubro as local_rubro 
        FROM promocion p 
        INNER JOIN local l ON p.localFk = l.IDlocal 
        WHERE p.estado = 0 
        ORDER BY p.desde ASC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getNovedadesActivas() {
    $pdo = getConnection();
    $query = "SELECT * FROM novedad WHERE hasta >= CURDATE() ORDER BY desde DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener reporte de uso de descuentos
function getReporteUsos($filtros = []) {
    $pdo = getConnection();
    
    $whereConditions = ["1=1"];
    $params = [];
    
    // Filtro por fecha desde
    if (!empty($filtros['fecha_desde'])) {
        $whereConditions[] = "up.fechaUso >= ?";
        $params[] = $filtros['fecha_desde'];
    }
    
    // Filtro por fecha hasta
    if (!empty($filtros['fecha_hasta'])) {
        $whereConditions[] = "up.fechaUso <= ?";
        $params[] = $filtros['fecha_hasta'];
    }
    
    // Filtro por local
    if (!empty($filtros['local_id'])) {
        $whereConditions[] = "p.localFk = ?";
        $params[] = $filtros['local_id'];
    }
    
    $whereClause = implode(" AND ", $whereConditions);
    
    $query = "
        SELECT 
            up.fechaUso,
            up.estado,
            u.nombreUsuario,
            u.DNI,
            c.nombre as categoria_usuario,
            p.descripcion as promocion_descripcion,
            p.categoriaHabilitada,
            l.nombre as local_nombre,
            l.rubro as local_rubro
        FROM usopromocion up
        INNER JOIN promocion p ON up.promoFK = p.IDpromocion
        INNER JOIN usuario u ON up.usuarioFk = u.IDusuario
        INNER JOIN categoria c ON u.categoriaFK = c.IDcategoria
        INNER JOIN local l ON p.localFk = l.IDlocal
        WHERE $whereClause
        ORDER BY up.fechaUso DESC
        LIMIT 100
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllLocales() {
    $pdo = getConnection();
    $query = "SELECT IDlocal, nombre FROM local ORDER BY nombre";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$estadisticas = getEstadisticasGenerales();
$locales = getLocales();
$solicitudesPendientes = getSolicitudesPendientes();
$promocionesPendientes = getPromocionesPendientes();
$novedadesActivas = getNovedadesActivas();
$todosLocales = getAllLocales();

// Procesar filtros para reportes
$filtrosReporte = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filtro_reporte'])) {
    $filtrosReporte = [
        'fecha_desde' => $_POST['fecha_desde'] ?? '',
        'fecha_hasta' => $_POST['fecha_hasta'] ?? '',
        'local_id' => $_POST['local_id'] ?? ''
    ];
} else {
    // Por defecto, último mes
    $filtrosReporte = [
        'fecha_desde' => date('Y-m-d', strtotime('-1 month')),
        'fecha_hasta' => date('Y-m-d'),
        'local_id' => ''
    ];
}
?>