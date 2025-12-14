<?php
require_once '../Model/conexion.php';

function getLocalPorUsuario($idUsuario) {
    $pdo = getConnection();
    $query = "SELECT IDlocal, nombre, rubro FROM local WHERE usuarioFK = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$idUsuario]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getPromocionesPorLocal($idLocal) {
    $pdo = getConnection();
    $query = "
        SELECT 
            p.IDpromocion,
            p.descripcion,
            p.desde,
            p.hasta,
            p.categoriaHabilitada,
            p.dia,
            p.estado,
            COUNT(up.promoFK) as total_usos
        FROM promocion p
        LEFT JOIN usopromocion up ON p.IDpromocion = up.promoFK AND up.estado = 1
        WHERE p.localFk = ? AND p.estado = 1
        GROUP BY p.IDpromocion
        ORDER BY p.desde DESC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$idLocal]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSolicitudesPendientesPorLocal($idLocal) {
    $pdo = getConnection();
    $query = "
        SELECT 
            up.usuarioFk,
            up.promoFK,
            up.fechaUso,
            u.nombreUsuario,
            u.email,
            p.descripcion as promocion_descripcion
        FROM usopromocion up
        INNER JOIN promocion p ON up.promoFK = p.IDpromocion
        INNER JOIN usuario u ON up.usuarioFk = u.IDusuario
        WHERE p.localFk = ? 
          AND up.estado = 0
        ORDER BY up.fechaUso ASC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$idLocal]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getEstadisticasLocal($idLocal) {
    $pdo = getConnection();
    
    $query1 = "SELECT COUNT(*) as total FROM promocion WHERE localFk = ? AND estado = 1 AND CURDATE() BETWEEN desde AND hasta";
    $stmt1 = $pdo->prepare($query1);
    $stmt1->execute([$idLocal]);
    $activas = $stmt1->fetch(PDO::FETCH_ASSOC);
    
    $query2 = "
        SELECT COUNT(*) as total 
        FROM usopromocion up 
        INNER JOIN promocion p ON up.promoFK = p.IDpromocion 
        WHERE p.localFk = ? AND up.estado = 0
    ";
    $stmt2 = $pdo->prepare($query2);
    $stmt2->execute([$idLocal]);
    $pendientes = $stmt2->fetch(PDO::FETCH_ASSOC);
    
    $query3 = "
        SELECT COUNT(*) as total 
        FROM usopromocion up 
        INNER JOIN promocion p ON up.promoFK = p.IDpromocion 
        WHERE p.localFk = ? AND up.estado = 1
    ";
    $stmt3 = $pdo->prepare($query3);
    $stmt3->execute([$idLocal]);
    $usos = $stmt3->fetch(PDO::FETCH_ASSOC);
    
    return [
        'promociones_activas' => $activas['total'] ?? 0,
        'solicitudes_pendientes' => $pendientes['total'] ?? 0,
        'total_usos' => $usos['total'] ?? 0
    ];
}

// Eliminar promoción (baja lógica - estado = 0)
function eliminarPromocion($idPromocion, $idLocal) {
    $pdo = getConnection();
    $query = "UPDATE promocion SET estado = 0 WHERE IDpromocion = ? AND localFk = ?";
    $stmt = $pdo->prepare($query);
    return $stmt->execute([$idPromocion, $idLocal]);
}

// Aceptar o rechazar solicitud
function actualizarEstadoSolicitud($usuarioFk, $promoFK, $estado) {
    $pdo = getConnection();
    $query = "UPDATE usopromocion SET estado = ? WHERE usuarioFk = ? AND promoFK = ?";
    $stmt = $pdo->prepare($query);
    return $stmt->execute([$estado, $usuarioFk, $promoFK]);
}





?>