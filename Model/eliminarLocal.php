<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['IDusuario']) || $_SESSION['Rol'] != 'Administrador') {
    header("Location: ../index.php");
    exit;
}

if (isset($_POST['id_local'])) {
    $pdo = getConnection();
    
    try {
        // Primero eliminar las promociones asociadas al local
        $query = "DELETE FROM promocion WHERE localFk = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$_POST['id_local']]);
        
        // Luego eliminar el local
        $query = "DELETE FROM local WHERE IDlocal = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$_POST['id_local']]);
        
        $_SESSION['mensaje'] = "Local eliminado correctamente";
        $_SESSION['tipo_mensaje'] = "success";
        
    } catch (PDOException $e) {
        $_SESSION['mensaje'] = "Error al eliminar el local: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
    }
    
    header("Location: ../Vista/DashboardAdministrador.php");
    exit;
}
?>