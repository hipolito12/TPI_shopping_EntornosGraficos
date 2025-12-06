<?php
// Model/EventosModel.php
include_once(__DIR__ . '/conexion.php');

class EventosModel {
    
    public function getEventosDestacados($limite = 3) {
        $pdo = getConnection();
        
        $query = "
            SELECT 
                IDnovedad as id,
                cabecera as titulo,
                descripcion,
                cuerpo as contenido,
                desde,
                hasta,
                usuarioHabilitado as categoria
            FROM novedad 
            WHERE hasta >= CURDATE() 
            AND desde <= CURDATE()
            ORDER BY desde ASC, IDnovedad DESC 
            LIMIT :limite
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTotalEventosActivos() {
        $pdo = getConnection();
        
        $query = "
            SELECT COUNT(*) as total 
            FROM novedad 
            WHERE hasta >= CURDATE() 
            AND desde <= CURDATE()
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
}
?>