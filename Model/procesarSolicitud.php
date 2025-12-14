<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['IDusuario']) || $_SESSION['Rol'] != 'Administrador') {
    header("Location: ../index.php");
    exit;
}

if (isset($_POST['id_solicitud']) && isset($_POST['accion'])) {
    $pdo = getConnection();
    
    try {
        $query = "SELECT * FROM solicitud WHERE IDsolicitud = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$_POST['id_solicitud']]);
        $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$solicitud) {
            throw new Exception("Solicitud no encontrada");
        }
        
        if ($_POST['accion'] == 'aprobar') {
            $query = "SELECT COUNT(*) FROM usuario WHERE email = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$solicitud['email']]);
            $existe = $stmt->fetchColumn();
            
            if ($existe) {
                throw new Exception("Ya existe un usuario con este email");
            }
            
            $query = "INSERT INTO usuario (nombreUsuario, email, clave, telefono, Sexo, tipoFK, categoriaFK, estado, DNI) 
                      VALUES (?, ?, ?, ?, ?, 2, 1, 1, ?)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                $solicitud['nombre'],
                $solicitud['email'],
                $solicitud['contraseña'],
                $solicitud['telefono'],
                $solicitud['sexo'],
                $solicitud['dni']
            ]);
            
            $idUsuario = $pdo->lastInsertId();
            
            $query = "INSERT INTO local (nombre, rubro, usuarioFK, ubicacionFK, codigo) 
                      VALUES (?, ?, ?, ?, ?)";
            $codigo = "LOCAL_" . uniqid();
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                $solicitud['nombreLocal'],
                $solicitud['rubro'],
                $idUsuario,
                $solicitud['ubicacion'],
                $codigo
            ]);
            
            $query = "UPDATE solicitud SET estado = '1' WHERE IDsolicitud = ?";
            $mensaje = "Solicitud aprobada correctamente";
            
        } else {
            $query = "UPDATE solicitud SET estado = '2' WHERE IDsolicitud = ?";
            $mensaje = "Solicitud rechazada correctamente";
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$_POST['id_solicitud']]);
        
        $_SESSION['mensaje'] = $mensaje;
        $_SESSION['tipo_mensaje'] = "success";
        
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
    }
    
    header("Location: ../Vista/DashboardAdministrador.php");
    exit;
}
?>