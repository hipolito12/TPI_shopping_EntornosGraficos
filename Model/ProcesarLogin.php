<?php
/*include_once ("funciones.php");
    session_start();

    $email=trim($_POST["mail"]);
    $password=trim($_POST["password"]);

    if(!empty($email) && !empty($password)){
        
            $consulta="SELECT 
                                U.IDusuario,
                                r.nombre AS rolNombre,
                                c.nombre AS catNombre
            
                        from usuario U
                        INNER JOIN rol r ON U.tipoFK = r.IDrol
                        INNER JOIN categoria c ON U.categoriaFK = c.IDcategoria
                        WHERE nombreUsuario='".$email."'
                        AND clave='".$password."'
                        AND estado='1'";


            $result=slql_consul($consulta);

            if(!empty($result)){
                
                $consulta_cant_promociones="SELECT COUNT(USP.usuarioFK) AS cant_prom 
                                            FROM usuario U
                                            INNER JOIN usopromocion USP ON USP.usuarioFK = U.IDusuario
                                            WHERE USP.usuarioFK = '".$result["IDusuario"]."'";

                $cant=slql_consul($consulta_cant_promociones);
                $_SESSION["IDuser"]=$result["IDusuario"];
                $_SESSION["Rol"]=$result["rolNombre"];
                $_SESSION["Categoria"]=$result["catNombre"];
                $_SESSION["CantProm"]=$cant["cant_prom"];
                
                //reemplazar por destino final luego del login
                header("location: index.php");
                exit();
            }

            //retorna a login para que inicie sesion nuevamente
            
            
    }
    //retorna a login para que inicie sesion nuevamente
    $_SESSION["MensajeError1"]=1;
    header("location: login.php");
    exit();*/


// Archivo: Controller/LoginController.php

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
