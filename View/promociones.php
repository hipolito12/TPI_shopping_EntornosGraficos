<?php

include("../Model/ListarPromociones.php");
session_start();
if (isset($_SESSION['Categoria'])) {
    $promos = listarPromocionesVigentesCategoria($_SESSION['Categoria'], 50);
} else {
    $promos = listarPromocionesVigentes(50);
}

function diaALabel($n) {
  static $map = [1=>'Lunes',2=>'Martes',3=>'Miércoles',4=>'Jueves',5=>'Viernes',6=>'Sábado',7=>'Domingo'];
  $i = (int)$n;
  return $map[$i] ?? (string)$n;
}


function formatearDias($valor) {
  if ($valor === null) return '—';
  $s = trim((string)$valor);
  if ($s === '') return '—';

  if (strpos($s, ',') !== false) {
    $partes = array_filter(array_map('trim', explode(',', $s)), 'strlen');
    if (!$partes) return '—';
    $labels = array_map('diaALabel', $partes);
    return implode(', ', $labels);
  }


  return diaALabel($s);
}
?>
<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <title>Promociones públicas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">


  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
  :root{ --bs-primary:#4A3BC7; }
  .bg-subtle{ background-color:#F3F1FF; }
  .section-pad{ padding-block: 2.25rem; }
  .promo-card{ border:1px solid rgba(74,59,199,.10); border-radius:16px; }
  .promo-card:hover{ box-shadow:0 14px 40px rgba(74,59,199,.10); }
  .badge-soft{ background:rgba(74,59,199,.08); color:#4A3BC7; }
</style>
</head>
<body class="bg-subtle">
  <?php include_once(__DIR__ . "/../layouts/Navbar.php"); ?>
<header class="bg-white border-bottom">
  <div class="container section-pad">
    <h1 class="h3 text-primary mb-1">Promociones</h1>
    <p class="text-secondary mb-0">Usuario no registrado · Ordenadas por categoría mínima requerida.</p>
  </div>
</header>

<main class="container section-pad">

  <div class="row g-4" aria-live="polite">
    <?php if (empty($promos)): ?>
      <div class="col-12">
        <div class="alert alert-light border text-secondary">No se encontraron promociones vigentes.</div>
      </div>
    <?php else: ?>
      <?php foreach ($promos as $p): ?>
        <?php
          $titulo       = $p['descripcion'] ?? '';
          $desde        = $p['desde'] ?? '';
          $hasta        = $p['hasta'] ?? '';
          $diasRaw      = $p['dia'] ?? '';
   
          $diasLabel    = formatearDias($diasRaw);
          $localNombre  = $p['local_nombre'] ?? '';
          $localRubro   = $p['local_rubro']  ?? '';
          $catMin       = $p['categoriaHabilitada'] ?? 'Todos';
          $descCompleta = $p['descripcion'] ?? '';
          $cats         = $p['categorias_permitidas'] ?? ['Todos'];
          $catsJson     = json_encode($cats, JSON_UNESCAPED_UNICODE);
        ?>
        <div class="col-12">
          <article class="card promo-card shadow-sm border-0">
            <div class="card-body d-flex flex-column flex-lg-row gap-3 align-items-lg-center">
              <div class="flex-grow-1">
                <h2 class="h6 mb-2 fw-semibold"><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></h2>

                <div class="text-secondary small d-flex flex-wrap gap-3">
                  <span><i class="bi bi-shop me-1"></i><strong><?= htmlspecialchars($localNombre, ENT_QUOTES, 'UTF-8') ?></strong></span>
                  <span><i class="bi bi-tag me-1"></i><?= htmlspecialchars($localRubro ?: '—', ENT_QUOTES, 'UTF-8') ?></span>
                  <span><i class="bi bi-calendar-date me-1"></i><?= htmlspecialchars($desde, ENT_QUOTES, 'UTF-8') ?> → <?= htmlspecialchars($hasta, ENT_QUOTES, 'UTF-8') ?></span>
                  <span><i class="bi bi-calendar-week me-1"></i><?= htmlspecialchars($diasLabel, ENT_QUOTES, 'UTF-8') ?></span>
                  <span><i class="bi bi-shield-check me-1"></i>
                    <span class="badge badge-soft rounded-pill px-2"><?= htmlspecialchars($catMin, ENT_QUOTES, 'UTF-8') ?></span>
                  </span>
                </div>

                <p class="mb-0 mt-2 text-body"><?= htmlspecialchars(mb_strimwidth($descCompleta, 0, 160, '…', 'UTF-8'), ENT_QUOTES, 'UTF-8') ?></p>
              </div>

              <div class="ms-lg-auto">
                <button
                  class="btn btn-outline-primary px-3"
                  data-bs-toggle="modal" data-bs-target="#promoModal"
                  data-titulo="<?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?>"
                  data-local="<?= htmlspecialchars($localNombre, ENT_QUOTES, 'UTF-8') ?>"
                  data-rubro="<?= htmlspecialchars($localRubro ?: '—', ENT_QUOTES, 'UTF-8') ?>"
                  data-desde="<?= htmlspecialchars($desde, ENT_QUOTES, 'UTF-8') ?>"
                  data-hasta="<?= htmlspecialchars($hasta, ENT_QUOTES, 'UTF-8') ?>"
                  data-dias="<?= htmlspecialchars($diasLabel, ENT_QUOTES, 'UTF-8') ?>"
                  data-descripcion="<?= htmlspecialchars($descCompleta ?: '—', ENT_QUOTES, 'UTF-8') ?>"
                  data-categorias='<?= $catsJson ?>'
                >Ver detalles</button>
              </div>
            </div>
          </article>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</main>



  <div class="modal fade" id="promoModal" tabindex="-1" aria-labelledby="promoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="promoModalLabel">Detalle de promoción</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <p class="mb-1"><strong id="modalTitulo">—</strong></p>
          <p class="mb-1"><strong>Local:</strong> <span id="modalLocal">—</span></p>
          <p class="mb-1"><strong>Rubro:</strong> <span id="modalRubro">—</span></p>
          <p class="mb-1"><strong>Vigencia:</strong> <span id="modalDesde">—</span> → <span id="modalHasta">—</span></p>
          <p class="mb-1"><strong>Días válidos:</strong> <span id="modalDias">—</span></p>
          <hr>
          <p id="modalDescripcion" class="mb-2">—</p>

          <div class="mb-1"><strong>Categorías habilitadas:</strong></div>
          <div id="modalCategorias" class="d-flex flex-wrap gap-2"></div>
        </div>
        <div class="modal-footer">
          <?php if (!isset($_SESSION['IDusuario'])): ?>
              <a href="./login.php" class="btn btn-outline-secondary">Iniciar sesión</a>
          <?php endif; ?>
          
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

<?php if (isset($_SESSION['IDusuario'])): ?>
<script src="../layouts/JS/cambiarNombre.js"></script>
<?php endif; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>

    const promoModal = document.getElementById('promoModal');
    promoModal.addEventListener('show.bs.modal', (event) => {
      const btn = event.relatedTarget;
      const get = (a) => btn.getAttribute(a) || '';

      document.getElementById('promoModalLabel').textContent = 'Detalle de promoción';
      document.getElementById('modalTitulo').textContent     = get('data-titulo');
      document.getElementById('modalLocal').textContent      = get('data-local');
      document.getElementById('modalRubro').textContent      = get('data-rubro');
      document.getElementById('modalDesde').textContent      = get('data-desde');
      document.getElementById('modalHasta').textContent      = get('data-hasta');
      document.getElementById('modalDias').textContent       = get('data-dias');
      document.getElementById('modalDescripcion').textContent= get('data-descripcion');


      const cont = document.getElementById('modalCategorias');
      cont.innerHTML = '';
      let cats = [];
      try { cats = JSON.parse(get('data-categorias') || '[]'); } catch(e){ cats = []; }
      if (!Array.isArray(cats) || cats.length === 0) cats = ['Todos'];
      cats.forEach(c => {
        const b = document.createElement('span');
        b.className = 'badge text-bg-light border';
        b.textContent = c;
        cont.appendChild(b);
      });
    });
  </script>
<?php include dirname(__DIR__) . '/layouts/footer.php'; ?>
  

</body>
</html>
