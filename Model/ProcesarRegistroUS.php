<?php
include_once("../Model/conexion.php");


function ExisteUsuario($username, $email) {
    $pdo = getConnection();

    $sql = "SELECT COUNT(*) FROM usuario WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'email' => $email
    ]);
   
    $count = $stmt->fetchColumn();
    return $count > 0; 
}

function EstaActivo($email):bool
{
$pdo = getConnection();
$sql = 'select usuario.estado from usuario where email= :email';
$stmt = $pdo->prepare($sql);
$stmt->execute(["email"=> $email]);

$Existencia = $stmt->fetchColumn();
return $Existencia == 0;
}

function insertarUsuario( $nombre, $email, $pwd, $tel, $sexo, $dni) {

    EstaActivo($email)? true : throw new Exception("mail ya registrado,verifique el mail");

    $tipoFK=1 ;
    $categoriaFK=1; 
    $estado=0;

    $pdo = getConnection();
    $sql = "INSERT INTO `usuario`
              (`nombreUsuario`,`email`,`clave`,`telefono`,`Sexo`,`tipoFK`,`categoriaFK`,`estado`,`DNI`)
            VALUES
              (:nombre, :email, :clave, :telefono, :sexo, :tipoFK, :categoriaFK, :estado,:DNI)";
    $stmt = $pdo->prepare($sql);
    $ok = $stmt->execute([
        ':nombre'      => $nombre,
        ':email'       => $email,
        ':clave'       => password_hash($pwd, PASSWORD_DEFAULT),
        ':telefono'    => $tel,
        ':sexo'        => $sexo,          
        ':tipoFK'      => (int)$tipoFK,   
        ':categoriaFK' => (int)$categoriaFK, 
        ':estado'      => (int)$estado ,
        ':DNI'      => $dni,
    ]);
    return $ok ? (int)$pdo->lastInsertId() : false;
}

function actualizarEstado( $email) {
    $pdo = getConnection();
    $sql = "UPDATE `usuario` SET `estado` = 1 WHERE `email` = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);

    return $stmt->rowCount() > 0;  // Si se actualizó al menos un registro
}

?>