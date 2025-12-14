<?php
include("../Model/ListadoTienda.php");
session_start();
$tiendas = listarTiendas(200);

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <title>Tiendas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{ --bs-primary:#4A3BC7; }
    .hero{ background: linear-gradient(180deg,#F3F1FF 0%, #fff 70%); }
    .tile{ border:1px solid rgba(74,59,199,.10); }
    .tile:hover{ box-shadow:0 12px 36px rgba(74,59,199,.12); }
    .code-pill{ background:#fff; border:1px solid rgba(74,59,199,.20); }
    .code-pill:hover{ border-color:#4A3BC7; }
  </style>
</head>
<body style=" background-color: #eeecfd">

  <?php include_once(__DIR__ . "/../layouts/Navbar.php"); ?>

  <!-- HERO -->
  <section class="hero border-bottom py-3 py-md-4">
    <div class="container-xxl">
      <h1 class="h3 text-primary mb-1">Tiendas</h1>
      <p class="text-body-secondary mb-0">Listado de locales y su ubicación dentro del shopping.</p>
    </div>
  </section>

  <main class="container-xxl py-4 py-md-5">

    <?php if (empty($tiendas)): ?>
      <div class="alert alert-light border text-body-secondary">No hay tiendas para mostrar.</div>
    <?php else: ?>
      <div class="row row-cols-1 gy-3 gy-md-4">
        <?php foreach ($tiendas as $t): ?>
          <?php
            $localNombre = $t['local_nombre'] ?? '';
            $localRubro  = $t['local_rubro']  ?? '';
            $ubiNombre   = $t['ubicacion_nombre'] ?? '—';
            $ubiDesc     = $t['ubicacion_descripcion'] ?? '—';
            $codigo      = $t['codigo'] ?? ($t['local_codigo'] ?? ($t['codigo_local'] ?? ''));
          ?>
          <div class="col">
            <article class="card tile rounded-4 shadow-sm border-0">
              <div class="card-body p-3 p-md-4 d-flex flex-column flex-lg-row align-items-lg-center gap-3">
                <div class="flex-grow-1">
                  <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h2 class="h6 mb-0 fw-semibold"><?= h($localNombre) ?></h2>

                    <?php if ($codigo !== ''): ?>
                      <span class="badge rounded-pill code-pill text-body-secondary d-inline-flex align-items-center">
                        <i class="bi bi-hash me-1"></i>
                        <span class="me-1">Código:</span>
                        <strong class="me-2"><?= h($codigo) ?></strong>
                        <button type="button"
                                class="btn btn-sm btn-outline-primary rounded-pill copy-code"
                                data-code="<?= h($codigo) ?>"
                                title="Copiar código">
                          <i class="bi bi-clipboard"></i>
                          <span class="visually-hidden">Copiar código</span>
                        </button>
                      </span>
                    <?php else: ?>
                      <span class="badge rounded-pill text-bg-light border">Sin código</span>
                    <?php endif; ?>
                  </div>

                  <ul class="list-inline text-body-secondary small mb-0 mt-2 d-flex flex-wrap gap-3">
                    <li class="list-inline-item" title="Rubro">
                      <i class="bi bi-tag me-1"></i><?= h($localRubro ?: '—') ?>
                    </li>
                    <li class="list-inline-item" title="Ubicación">
                      <i class="bi bi-geo-alt me-1"></i><strong><?= h($ubiNombre) ?></strong>
                    </li>
                    <li class="list-inline-item d-none d-md-inline" title="Descripción de la ubicación">
                      <i class="bi bi-info-circle me-1"></i><?= h(mb_strimwidth($ubiDesc, 0, 100, '…', 'UTF-8')) ?>
                    </li>
                  </ul>
                </div>

                <div class="ms-lg-auto">
                  <button
                    class="btn btn-outline-primary rounded-pill px-3"
                    data-bs-toggle="modal" data-bs-target="#tiendaModal"
                    data-nombre="<?= h($localNombre) ?>"
                    data-rubro="<?= h($localRubro ?: '—') ?>"
                    data-ubicacion="<?= h($ubiNombre) ?>"
                    data-ubicaciondesc="<?= h($ubiDesc) ?>"
                  >Ver detalles</button>
                </div>
              </div>
            </article>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div id="copyLive" class="visually-hidden" aria-live="polite"></div>
  </main>

  <?php include_once(__DIR__ . "/../layouts/footer.php"); ?>

  <!-- MODAL: detalle de tienda -->
  <div class="modal fade" id="tiendaModal" tabindex="-1" aria-labelledby="tiendaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content rounded-4">
        <div class="modal-header bg-primary text-white rounded-top-4">
          <h5 class="modal-title" id="tiendaModalLabel"><i class="bi bi-shop me-2"></i>Detalle de tienda</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <p class="mb-1"><strong id="mNombre">—</strong></p>
          <p class="mb-1"><strong>Rubro:</strong> <span id="mRubro">—</span></p>
          <hr>
          <p class="mb-1"><strong>Ubicación:</strong> <span id="mUbicacion">—</span></p>
          <p class="mb-0"><strong>Descripción:</strong> <span id="mUbicacionDesc">—</span></p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Copiar código al portapapeles -->
  <script>
    function copyText(text){
      if (!text) return Promise.reject('No hay código');
      if (navigator.clipboard && window.isSecureContext) {
        return navigator.clipboard.writeText(text);
      } else {
        // Fallback para contextos inseguros
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.focus(); ta.select();
        try { document.execCommand('copy'); } finally { document.body.removeChild(ta); }
        return Promise.resolve();
      }
    }

    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('.copy-code');
      if (!btn) return;

      const code = btn.getAttribute('data-code') || '';
      const icon = btn.querySelector('i');

      try {
        await copyText(code);
        const live = document.getElementById('copyLive');
        if (live) live.textContent = `Código ${code} copiado.`;

        // feedback visual
        if (icon) icon.className = 'bi bi-clipboard-check';
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-primary');
        setTimeout(() => {
          if (icon) icon.className = 'bi bi-clipboard';
          btn.classList.remove('btn-primary');
          btn.classList.add('btn-outline-primary');
        }, 1500);
      } catch(err) {
        alert('No se pudo copiar el código. Copialo manualmente: ' + code);
      }
    });

    // Poblar modal
    const tiendaModal = document.getElementById('tiendaModal');
    tiendaModal.addEventListener('show.bs.modal', (ev) => {
      const b = ev.relatedTarget, get = (a) => b?.getAttribute(a) || '';
      document.getElementById('mNombre').textContent        = get('data-nombre');
      document.getElementById('mRubro').textContent         = get('data-rubro');
      document.getElementById('mUbicacion').textContent     = get('data-ubicacion');
      document.getElementById('mUbicacionDesc').textContent = get('data-ubicaciondesc');
    });
  </script>

<?php if (isset($_SESSION['IDusuario'])): ?>
<script src="../layouts/JS/cambiarNombre.js"></script>
<?php endif; ?>







</body>
</html>