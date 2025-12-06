<?php
require_once __DIR__ . '/conexion.php';



function registrarContacto($asunto, $cuerpo) {
    try {
        $pdo = getConnection();
        $query = "INSERT INTO contacto (asunto, cuerpo, estado) VALUES (?, ?, 0)";
        $stmt = $pdo->prepare($query);
        return $stmt->execute([$asunto, $cuerpo]);
    } catch (Exception $e) {
        return false;
    }
}

// --- AGREGAR DESDE AQUÍ ---

function getTodosLosContactos() {
    $pdo = getConnection();
    // Ordenamos por Estado (0 pendientes primero) y luego por ID descendente (nuevos primero)
    $query = "SELECT * FROM contacto ORDER BY estado ASC, id DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function cerrarContacto($id) {
    try {
        $pdo = getConnection();
        // Estado 1 = Cerrado/Resuelto
        $query = "UPDATE contacto SET estado = 1 WHERE id = ?";
        $stmt = $pdo->prepare($query);
        return $stmt->execute([$id]);
    } catch (Exception $e) {
        return false;
    }
}
?>
