<?php

include_once("../Model/conexion.php");


function categoriasPermitidas(?string $min): array {
    $min = $min ? strtolower(trim($min)) : '';
    switch ($min) {
        case 'inicial': return ['Inicial','Medium','Premium'];
        case 'medium':  return ['Medium','Premium'];
        case 'premium': return ['Premium'];
        default:        return ['Todos'];
    }
}



if (!function_exists('listarNovedadesVigentes')) {
  /**
   * Lista novedades vigentes (hoy entre desde y hasta), ordenadas por fecha (desc).
   * @param int $limit
   * @return array<int, array<string,mixed>>
   */
  function listarNovedadesVigentes(int $limit = 30): array {
    $pdo = getConnection();

    $sql = "
      SELECT
        n.IDnovedad,
        n.desde,
        n.hasta,
        n.usuarioHabilitado,
        n.descripcion,
        n.cabecera,
        n.cuerpo
      FROM novedad n
      WHERE CURDATE() BETWEEN n.desde AND n.hasta
      ORDER BY n.desde DESC, n.IDnovedad DESC
      LIMIT :lim
    ";

    $st = $pdo->prepare($sql);
    $st->bindValue(':lim', $limit, PDO::PARAM_INT);
    $st->execute();
    $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

 foreach ($rows as &$r) {
        $r['categorias_permitidas'] = categoriasPermitidas($r['usuarioHabilitado'] ?? null);
    }
    unset($r);
    return $rows;
  }

  function listarNovedadesVigentesCategoria(string $categoriaMinima, int $limit = 50): array {
    $pdo = getConnection();

    $sql = "
       SELECT
            n.IDnovedad,
            n.desde,
            n.hasta,
            n.usuarioHabilitado,
            n.descripcion,
            n.cabecera,
            n.cuerpo,
            n.imagen
        FROM novedad n
        WHERE CURDATE() BETWEEN n.desde AND n.hasta
        AND n.usuarioHabilitado = :categoria
        LIMIT :lim
    ";

    $st = $pdo->prepare($sql);
    $st->bindValue(':categoria', $categoriaMinima, PDO::PARAM_STR);
    $st->bindValue(':lim', $limit, PDO::PARAM_INT);
    $st->execute();
    $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    foreach ($rows as &$r) {
        $r['categorias_permitidas'] = categoriasPermitidas($r['usuarioHabilitado'] ?? null);
    }
    unset($r);
    return $rows;
}
}
