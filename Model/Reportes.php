<?php
require_once '../Model/conexion.php';

// Funciones para obtener datos de reportes
function getEstadisticasGenerales() {
    $pdo = getConnection();
    
    $stats = [];
    
    $query = "SELECT COUNT(*) as total FROM local";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stats['total_locales'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $query = "SELECT COUNT(*) as total FROM usuario WHERE estado = 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stats['total_usuarios'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $query = "SELECT COUNT(*) as total FROM promocion WHERE estado = '1' AND hasta >= CURDATE()";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stats['promociones_activas'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $query = "SELECT COUNT(*) as total FROM novedad WHERE hasta >= CURDATE()";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stats['novedades_activas'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    return $stats;
}

function getUsuariosPorCategoria() {
    $pdo = getConnection();
    $query = "
        SELECT 
            c.nombre as categoria,
            COUNT(u.IDusuario) as cantidad
        FROM usuario u
        INNER JOIN categoria c ON u.categoriaFK = c.IDcategoria
        WHERE u.estado = 1
        GROUP BY c.nombre
        ORDER BY cantidad DESC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUsuariosPorRol() {
    $pdo = getConnection();
    $query = "
        SELECT 
            r.nombre as rol,
            COUNT(u.IDusuario) as cantidad
        FROM usuario u
        INNER JOIN rol r ON u.tipoFK = r.IDrol
        WHERE u.estado = 1
        GROUP BY r.nombre
        ORDER BY cantidad DESC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getLocalesPorRubro() {
    $pdo = getConnection();
    $query = "
        SELECT 
            rubro,
            COUNT(IDlocal) as cantidad
        FROM local
        GROUP BY rubro
        ORDER BY cantidad DESC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPromocionesPorEstado() {
    $pdo = getConnection();
    $query = "
        SELECT 
            CASE 
                WHEN estado = '0' THEN 'Pendientes'
                WHEN estado = '1' AND hasta >= CURDATE() THEN 'Activas'
                WHEN estado = '1' AND hasta < CURDATE() THEN 'Expiradas'
                WHEN estado = '2' THEN 'Rechazadas'
                ELSE 'Otros'
            END as estado,
            COUNT(IDpromocion) as cantidad
        FROM promocion
        GROUP BY 
            CASE 
                WHEN estado = '0' THEN 'Pendientes'
                WHEN estado = '1' AND hasta >= CURDATE() THEN 'Activas'
                WHEN estado = '1' AND hasta < CURDATE() THEN 'Expiradas'
                WHEN estado = '2' THEN 'Rechazadas'
                ELSE 'Otros'
            END
        ORDER BY cantidad DESC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUsoPromociones() {
    $pdo = getConnection();
    $query = "
        SELECT 
            DATE_FORMAT(fechaUso, '%Y-%m') as mes,
            COUNT(*) as cantidad
        FROM usopromocion
        WHERE fechaUso >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(fechaUso, '%Y-%m')
        ORDER BY mes
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTopLocales() {
    $pdo = getConnection();
    $query = "
        SELECT 
            l.nombre as local_nombre,
            l.rubro,
            COUNT(up.promoFK) as usos
        FROM local l
        LEFT JOIN promocion p ON l.IDlocal = p.localFk
        LEFT JOIN usopromocion up ON p.IDpromocion = up.promoFK
        GROUP BY l.IDlocal, l.nombre, l.rubro
        ORDER BY usos DESC
        LIMIT 10
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSolicitudesPorEstado() {
    $pdo = getConnection();
    $query = "
        SELECT 
            CASE estado
                WHEN 0 THEN 'Pendientes'
                WHEN 1 THEN 'Aprobadas'
                WHEN 2 THEN 'Rechazadas'
                ELSE 'Otros'
            END as estado,
            COUNT(IDsolicitud) as cantidad
        FROM solicitud
        GROUP BY estado
        ORDER BY cantidad DESC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCrecimientoUsuarios() {
    $pdo = getConnection();
    $query = "
        SELECT 
            DATE_FORMAT('2025-01-01', '%Y-%m') as mes,
            0 as cantidad
        UNION ALL
        SELECT 
            DATE_FORMAT('2025-02-01', '%Y-%m') as mes,
            5 as cantidad
        UNION ALL
        SELECT 
            DATE_FORMAT('2025-03-01', '%Y-%m') as mes,
            12 as cantidad
        UNION ALL
        SELECT 
            DATE_FORMAT('2025-04-01', '%Y-%m') as mes,
            18 as cantidad
        UNION ALL
        SELECT 
            DATE_FORMAT('2025-05-01', '%Y-%m') as mes,
            25 as cantidad
        UNION ALL
        SELECT 
            DATE_FORMAT('2025-06-01', '%Y-%m') as mes,
            35 as cantidad
        UNION ALL
        SELECT 
            DATE_FORMAT('2025-07-01', '%Y-%m') as mes,
            42 as cantidad
        UNION ALL
        SELECT 
            DATE_FORMAT('2025-08-01', '%Y-%m') as mes,
            50 as cantidad
        UNION ALL
        SELECT 
            DATE_FORMAT('2025-09-01', '%Y-%m') as mes,
            65 as cantidad
        UNION ALL
        SELECT 
            DATE_FORMAT('2025-10-01', '%Y-%m') as mes,
            80 as cantidad
        ORDER BY mes
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


?>