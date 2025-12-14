<?php
require_once __DIR__ . '/../Model/ContactoModel.php';

if(isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $resultado = cerrarContacto($id);
    
    echo json_encode(['ok' => (bool)$resultado]);
}
?>