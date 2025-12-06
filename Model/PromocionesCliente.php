<?php
require_once 'conexion.php';

class PromocionesModel {
    private $pdo;

    public function __construct() {
        $this->pdo = getConnection();
    }

    // Obtener promociones usadas por un usuario
    public function getPromocionesUsadas($usuario_id) {
        $query = "
            SELECT promoFK 
            FROM usopromocion 
            WHERE usuarioFk = ? AND estado IN (0, 1)
        ";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Obtener promociones disponibles con filtros
    public function getPromocionesDisponibles($usuario_id, $categoria_usuario, $codigo_busqueda = null) {
        // 1. MEJORA: Definir niveles en minúsculas para evitar errores de mayúsculas/minúsculas
        $niveles_categoria = [
            'inicial' => 1,
            'medium' => 2,
            'premium' => 3,
        ];
        
        // Convertimos la categoría del usuario a minúsculas
        $cat_usuario_lower = strtolower($categoria_usuario);
        $nivel_usuario = $niveles_categoria[$cat_usuario_lower] ?? 1;

        // Obtener promociones ya usadas para excluirlas
        $promociones_usadas = $this->getPromocionesUsadas($usuario_id);

        // 2. Consulta Base
        $query = "
            SELECT 
                l.IDlocal as codigo_local,
                l.nombre as nombre_local,
                l.rubro as rubro_local,
                l.codigo as codigo_real_local, 
                p.IDpromocion as id_promocion,
                p.descripcion as descripcion_promo,
                p.desde as fecha_desde,
                p.hasta as fecha_hasta,
                p.categoriaHabilitada as categoria_requerida,
                p.dia as dia_promo,
                u.nombre as ubicacion_nombre
            FROM local l
            INNER JOIN promocion p ON l.IDlocal = p.localFk
            LEFT JOIN ubicacion u ON l.ubicacionFK = u.IDubicacion
            WHERE p.estado = 1
              AND CURDATE() BETWEEN p.desde AND p.hasta
        ";

        $params = [];

        // 3. CORRECCIÓN IMPORTANTE: Búsqueda por NOMBRE o CÓDIGO
        if (!empty($codigo_busqueda)) {
            // El usuario busca texto, puede ser el nombre o el código
            $query .= " AND (l.nombre LIKE ? OR l.codigo LIKE ?) ";
            $term = '%' . $codigo_busqueda . '%';
            $params[] = $term;
            $params[] = $term;
        }

        $query .= " ORDER BY l.nombre, p.desde";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $promociones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 4. Filtrado lógico (Nivel y Uso)
        $promociones_filtradas = [];
        foreach ($promociones as $promo) {
            // Normalizamos la categoría de la promoción también
            $cat_promo_lower = strtolower($promo['categoria_requerida']);
            $nivel_promo = $niveles_categoria[$cat_promo_lower] ?? 1;
            
            // Verificamos nivel y que no haya sido usada
            if ($nivel_usuario >= $nivel_promo && !in_array($promo['id_promocion'], $promociones_usadas)) {
                $promociones_filtradas[] = $promo;
            }
        }

        // Agrupar por local (Usando IDlocal como clave)
        $locales_con_promociones = [];
        foreach ($promociones_filtradas as $promo) {
            $local_id = $promo['codigo_local']; // Esto es IDlocal (int) según el alias de la query
            
            if (!isset($locales_con_promociones[$local_id])) {
                $locales_con_promociones[$local_id] = [
                    'nombre' => $promo['nombre_local'],
                    'rubro' => $promo['rubro_local'],
                    'ubicacion' => $promo['ubicacion_nombre'],
                    'codigo_visible' => $promo['codigo_real_local'], // Guardamos el código real por si se quiere mostrar
                    'promociones' => []
                ];
            }
            $locales_con_promociones[$local_id]['promociones'][] = $promo;
        }

        return $locales_con_promociones;
    }

    // Registrar uso de promoción
    public function usarPromocion($usuario_id, $promocion_id) {
        // Verificar que la promoción existe y está vigente
        $query_verificar = "
            SELECT p.IDpromocion 
            FROM promocion p
            WHERE p.IDpromocion = ?
              AND p.estado = 1
              AND CURDATE() BETWEEN p.desde AND p.hasta
        ";
        
        $stmt = $this->pdo->prepare($query_verificar);
        $stmt->execute([$promocion_id]);
        $promocion_valida = $stmt->fetch();
        
        if (!$promocion_valida) {
            return ['success' => false, 'message' => 'La promoción ya no está disponible o ha vencido.'];
        }

        // Verificar si ya usó esta promoción
        $query_verificar_uso = "
            SELECT * FROM usopromocion 
            WHERE usuarioFk = ? AND promoFK = ? AND estado IN (0, 1)
        ";
        $stmt_uso = $this->pdo->prepare($query_verificar_uso);
        $stmt_uso->execute([$usuario_id, $promocion_id]);
        
        if ($stmt_uso->fetch()) {
            return ['success' => false, 'message' => 'Ya has solicitado esta promoción anteriormente.'];
        }

        // Registrar el uso
        $query_insert = "
            INSERT INTO usopromocion (usuarioFk, promoFK, fechaUso, estado) 
            VALUES (?, ?, CURDATE(), 0)
        ";
        $stmt_insert = $this->pdo->prepare($query_insert);
        
        if ($stmt_insert->execute([$usuario_id, $promocion_id])) {
            return ['success' => true, 'message' => '¡Solicitud enviada! El local debe aceptarla.'];
        } else {
            return ['success' => false, 'message' => 'Error al registrar la solicitud.'];
        }
    }

    public static function getDiaSemana($numero) {
        $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        return $dias[$numero - 1] ?? 'Todos los días';
    }
}
?>