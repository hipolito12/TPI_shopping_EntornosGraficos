<?php
include("../Model/ListarNovedades.php");

session_start();
if (isset($_SESSION['Categoria'])) {
    $novedades = listarNovedadesVigentesCategoria($_SESSION['Categoria'], 50);
} else {
  $novedades = listarNovedadesVigentes(30);

}

function h($v): string
{
    if ($v === null) return '';

    if (is_scalar($v)) {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }

    if (is_array($v)) {
        $items = [];
        foreach ($v as $item) {
            if (is_scalar($item) || (is_object($item) && method_exists($item, '__toString'))) {
                $items[] = htmlspecialchars((string)$item, ENT_QUOTES, 'UTF-8');
            } else {
                $json = json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $items[] = htmlspecialchars($json !== false ? $json : '', ENT_QUOTES, 'UTF-8');
            }
        }
        return implode(', ', $items);
    }

    if (is_object($v) && method_exists($v, '__toString')) {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }

    $json = json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return $json === false ? '' : htmlspecialchars($json, ENT_QUOTES, 'UTF-8');
}

function recorte($txt, $len=160){ return h(mb_strimwidth((string)$txt, 0, $len, '…', 'UTF-8')); }
?>
<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <title>Novedades</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">


  <style>
    :root{ --bs-primary:#4A3BC7; }

    .page-novedades .hero        { background: linear-gradient(180deg,#F3F1FF 0%, #fff 70%); padding-block: .75rem !important; }
    .page-novedades .hero h1     { margin-bottom: .25rem !important; }
    .page-novedades .hero p      { margin: 0 !important; }

    .page-novedades main         { padding-block: 1rem !important; }
    .page-novedades .news-wrap   { max-width: 960px; margin-inline: auto; }   
    .page-novedades .news-card   { border-left:.45rem solid #4A3BC7; border-radius:.75rem; }
    .page-novedades .news-card .card-body { padding: .8rem 1rem !important; } 
    .page-novedades .gy-tight > *{ margin-top: .75rem !important; }          

    .page-novedades footer       { padding-top: 1rem !important; padding-bottom: 1rem !important; margin-top: 0 !important; }

  
  </style>
</head>
<body class="page-novedades" style=" background-color: #eeecfd">

  <?php include_once(__DIR__ . "/../layouts/Navbar.php"); ?>
  

  <section class="hero border-bottom">
    <div class="container-xxl">
      <h1 class="h4 text-primary">Novedades</h1>
      <p class="text-body-secondary">Enterate de lo último del shopping (vigentes hoy).</p>
    </div>
  </section>

  <main class="container-xxl">
    <?php if (empty($novedades)): ?>
      <div class="alert alert-light border text-body-secondary mb-0">No hay novedades vigentes por el momento.</div>
    <?php else: ?>
      <div class="news-wrap">
        <?php foreach ($novedades as $n): ?>
          <?php
            $cabecera   = $n['cabecera'] ?? '';
            $desc       = $n['descripcion'] ?? '';
            $cuerpo     = $n['cuerpo'] ?? '';
            $desde      = $n['desde'] ?? '';
            $hasta      = $n['hasta'] ?? '';
            $visiblePara= $n['categorias_permitidas'] ?: 'Todos';
          ?>
          <article class="card news-card shadow-sm border-0 mb-3">
            <div class="card-body d-flex flex-column flex-lg-row align-items-lg-center gap-3">
              <div class="flex-grow-1">
                <h2 class="h6 mb-1 fw-semibold"><?= h($cabecera ?: $desc) ?></h2>

                <ul class="list-inline text-body-secondary small mb-1 d-flex flex-wrap gap-3">
                  <li class="list-inline-item">
                    <i class="bi bi-calendar-event me-1"></i><?= h($desde) ?> → <?= h($hasta) ?>
                  </li>
                  <li class="list-inline-item">
                    <i class="bi bi-people me-1"></i>
                    <span class="badge rounded-pill" style="background:rgba(74,59,199,.10);color:#4A3BC7;"><?= h($visiblePara) ?></span>
                  </li>
                </ul>

                <p class="mb-0 text-body"><?= recorte($desc ?: $cuerpo, 180) ?></p>
              </div>

              <div class="ms-lg-auto">
                <button
                  class="btn btn-outline-primary rounded-pill px-3"
                  data-bs-toggle="modal" data-bs-target="#novedadModal"
                  data-cabecera="<?= h($cabecera ?: '—') ?>"
                  data-descripcion="<?= h($desc ?: '—') ?>"
                  data-cuerpo="<?= h($cuerpo ?: '—') ?>"
                  data-desde="<?= h($desde) ?>"
                  data-hasta="<?= h($hasta) ?>"
                  data-visible="<?= h($visiblePara) ?>"
                >Ver más</button>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>

  <?php include_once(__DIR__ . "/../layouts/footer.php"); ?>

  <div class="modal fade" id="novedadModal" tabindex="-1" aria-labelledby="novedadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content rounded-4">
        <div class="modal-header bg-primary text-white rounded-top-4">
          <h5 class="modal-title" id="novedadModalLabel"><i class="bi bi-megaphone me-2"></i>Detalle de novedad</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <p class="mb-1"><strong id="mCabecera">—</strong></p>
          <ul class="list-inline text-body-secondary small mb-2">
            <li class="list-inline-item"><i class="bi bi-calendar-event me-1"></i><span id="mDesde">—</span> → <span id="mHasta">—</span></li>
            <li class="list-inline-item"><i class="bi bi-people me-1"></i><span id="mVisible">—</span></li>
          </ul>
          <p class="mb-2"><strong>Descripción:</strong> <span id="mDescripcion">—</span></p>
          <hr class="my-2">
          <div>
            <div class="fw-semibold mb-1">Cuerpo</div>
            <p id="mCuerpo" class="mb-0"></p>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Llenar modal
    const novedadModal = document.getElementById('novedadModal');
    novedadModal.addEventListener('show.bs.modal', (ev) => {
      const b = ev.relatedTarget, get = (a) => b?.getAttribute(a) || '';
      document.getElementById('mCabecera').textContent    = get('data-cabecera') || '—';
      document.getElementById('mDesde').textContent       = get('data-desde') || '—';
      document.getElementById('mHasta').textContent       = get('data-hasta') || '—';
      document.getElementById('mVisible').textContent     = get('data-visible') || '—';
      document.getElementById('mDescripcion').textContent = get('data-descripcion') || '—';
      document.getElementById('mCuerpo').textContent      = get('data-cuerpo') || '—';
    });
  </script>
 <?php if (isset($_SESSION['IDusuario'])): ?>
<script src="../layouts/JS/cambiarNombre.js"></script>
<?php endif; ?>
</body>
</html>
