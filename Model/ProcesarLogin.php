<?php
include_once("../Model/conexion.php");

function checkCredentials($email, $password)
{
    $pdo = getConnection();

    $sql = "
   SELECT 
  u.*,
  r.IDrol        AS rol_id,
  r.nombre       AS rol_nombre,
  c.IDcategoria  AS categoria_id,
  c.nombre       AS categoria_nombre,
  c.descripcion  AS categoria_descripcion
FROM usuario AS u
JOIN rol       AS r ON r.IDrol       = u.tipoFK
JOIN categoria AS c ON c.IDcategoria = u.categoriaFK
WHERE u.email = :email  and u.estado = 1
LIMIT 1;
";
    $stmt = $pdo->prepare($sql);

    $stmt->execute(['email' => $email]);

    $user = $stmt->fetch();
   

    return $user;
}
