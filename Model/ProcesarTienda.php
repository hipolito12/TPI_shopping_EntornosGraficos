<?php


include_once(__DIR__ . "/conexion.php");

function saveStoreRequest(array $data) {
    $pdo = getConnection();
  
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "INSERT INTO solicitud
              (`nombre`, `email`, `contraseña`, `telefono`, `sexo`, `dni`, `cuil`, `rubro`, `nombreLocal`, `ubicacion`,`estado`)
            VALUES
              (:nombre, :email, :contrasena, :telefono, :sexo, :dni, :cuil, :rubro, :nombreLocal, :ubicacion, 0)";

    $stmt = $pdo->prepare($sql);

 
   

    $ok = $stmt->execute([
        'nombre'       => (string)$data['nombre'],
        'email'        => (string)$data['email'],
        'contrasena'   => $data['contrasena'],
        'telefono'     => ($data['telefono'] === '' ? null : (string)$data['telefono']),
        'sexo'         => ($data['sexo'] === '' ? null : (string)$data['sexo']),
        'dni'          => (string)$data['dni'],
        'cuil'         => (string)$data['cuil'],
        'rubro'        => (string)$data['rubro'],
        'nombreLocal'  => (string)$data['nombreLocal'],
        'ubicacion'    => (string)$data['ubicacion'],
    ]);

    if (!$ok) {
        return ['ok' => false, 'message' => 'No se pudo insertar la solicitud.', 'errors' => ['general' => 'Fallo al insertar.']];
    }

    $id = (int)$pdo->lastInsertId();
    return $id;
}





function getUbicaciones(): array {
    try {
        $pdo = getConnection();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT IDubicacion, nombre
                  FROM ubicacion
                 WHERE estado = 0
              ORDER BY nombre ASC";
        $stmt = $pdo->query($sql);

        
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return is_array($rows) ? $rows : [];
    } catch (Throwable $e) {
        error_log('getUbicaciones: '.$e->getMessage());
        return [];
    }
}
