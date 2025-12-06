<?php 
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['IDusuario']) || $_SESSION['Rol'] !='Usuario') {
    session_unset();
    header("Location: ../index.php");
    exit;
}

if (isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0') {
}

require_once '../Model/ProcesarDashboardCliente.php';

$IDU = (int)($_SESSION['IDusuario'] ?? 0);

// Actualizar categoría del usuario al entrar al dashboard
$resultado_actualizacion = actualizarCategoriaUsuario($IDU);
if ($resultado_actualizacion) {
    $_SESSION['Categoria'] = $resultado_actualizacion['nueva_categoria'];
}

$CAT = $_SESSION['Categoria'];

// Obtener información de progreso
$info_progreso = getInfoProgresoCategoria($IDU);

$numPromosDisponibles = getPromocionesDisponiblesPorCategoria($CAT);
$numPromosUsadas      = $info_progreso['promociones_usadas'];
$numNovedadesActivas  = getNovedadesPorCategoria($CAT);
$promociones          = getPromocionesPorCategoria($CAT, $IDU);
$historialUso         = getHistorialUsoCliente($IDU, 8); 
$novedadesRecientes   = getNovedadesRecientesPorCategoria($CAT, 8);
$totalPromos          = count($promociones);
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Menu - Cliente</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <style>
    :root{
      --primary: #4A3BC7;
      --primary-rgb: 74,59,199;
      --subtle: #F3F1FF;
      --muted: #6c6c6c;
      --success: #28a745;
      --warning: #ffc107;
      --danger: #dc3545;
    }

    body { background: linear-gradient(180deg, #fff 0%, var(--subtle) 100%); }
    .navbar, .card-header { background: var(--primary); color: #fff; }
    .btn-primary { background: var(--primary); border-color: rgba(var(--primary-rgb), 0.9); }
    .btn-primary:focus, .btn-primary:hover { filter: brightness(0.95); }

    .sidebar { min-height: 100vh; background: #fff; border-right: 1px solid #e9e9ef; }
    .sidebar .nav-link { color: #333; }
    .sidebar .nav-link.active { background: rgba(var(--primary-rgb), 0.08); color: var(--primary); border-radius: .5rem; }

    .promo-card { border: 1px solid rgba(74,59,199,0.08); border-radius: .75rem; transition: transform .12s ease; }
    .promo-card:hover { transform: translateY(-4px); box-shadow: 0 6px 18px rgba(74,59,199,0.06); }

    .small-muted { color: var(--muted); font-size: .9rem; }
    .badge-cat { background: rgba(var(--primary-rgb), 0.12); color: var(--primary); font-weight:600; }

    .copy-chip { cursor:pointer; user-select:all; }

    /* Estilos para el historial y novedades */
    .historial-item, .novedad-item {
        border-left: 4px solid var(--primary);
        padding-left: 1rem;
        margin-bottom: 1rem;
    }
    
    .estado-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    
    .estado-pendiente { background-color: var(--warning); color: #000; }
    .estado-aceptado { background-color: var(--success); color: #fff; }
    .estado-rechazado { background-color: var(--danger); color: #fff; }
    
    .historial-empty, .novedades-empty {
        text-align: center;
        padding: 2rem;
        color: var(--muted);
    }

    .novedad-badge {
        background-color: rgba(var(--primary-rgb), 0.1);
        color: var(--primary);
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    .progress-bar-animated {
        background: linear-gradient(90deg, var(--primary), rgba(var(--primary-rgb),0.85));
        transition: width 0.6s ease;
    }

    .categoria-badge {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
    }

    .categoria-inicial { background-color: #28a745; color: white; }
    .categoria-medium { background-color: #ffc107; color: #000; }
    .categoria-premium { background-color: #dc3545; color: white; }

    a:focus, button:focus { outline: 3px solid rgba(var(--primary-rgb), 0.12); outline-offset: 2px; }

    @media (max-width: 767px) {
      .sidebar { min-height: auto; border-right: none; border-bottom: 1px solid #eee; }
    }
  </style>
</head>
<body>

  <nav class="navbar navbar-expand-lg sticky-top">
    <div class="container-fluid">
      <a class="navbar-brand text-white" href="#">
        <strong>ShoppingUTN</strong>
      </a>

      <div class="d-flex align-items-center ms-auto">
        <form class="d-none d-md-flex me-3" role="search" action="/buscar" method="get" aria-label="Buscar promociones">
          <input name="q" class="form-control form-control-sm" type="search" placeholder="Buscar promociones..." aria-label="Buscar">
        </form>

        <div class="dropdown">
          <a class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" href="#" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <strong class="me-2"><?php echo htmlspecialchars($_SESSION['Nombre'] ?? 'Usuario'); ?></strong>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li><a class="dropdown-item" href="./PromocionesCliente.php">Mis promociones</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="../Model/logout.php">Cerrar sesión</a></li>
          </ul>
        </div>
      </div>
    </div>
  </nav>

  <div class="container-fluid">
    <div class="row">

      <nav class="col-12 col-md-3 col-lg-2 px-3 sidebar">
        <div class="pt-3 pb-2">
          <h6 class="text-muted">Navegación</h6>
          <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link active" href="#"><i class="bi bi-house-door-fill me-2"></i>Inicio</a></li>
            <li class="nav-item"><a class="nav-link" href="./PromocionesCliente.php">Promociones</a></li>
            <li class="nav-item"><a class="nav-link" href="./Tienda.php">Tiendas</a></li>
            <li class="nav-item"><a class="nav-link" href="./Novedades.php">Novedades</a></li>
          </ul>
        </div>

        <hr>

        <div class="px-2 pb-4">
          <div class="small-muted mb-2">Tu categoría</div>
          <div class="d-flex align-items-center mb-2">
            <span class="badge categoria-badge categoria-<?php echo strtolower($info_progreso['categoria_actual']); ?>">
              <?php echo htmlspecialchars($info_progreso['categoria_actual']); ?>
            </span>
            <span class="ms-auto small-muted">
              <?php echo $info_progreso['promociones_usadas']; ?> usos
            </span>
          </div>
          
          <div class="progress mt-2" style="height:8px;">
            <div class="progress-bar progress-bar-animated" 
                 role="progressbar" 
                 style="width:<?php echo $info_progreso['progreso_porcentaje']; ?>%;" 
                 aria-valuenow="<?php echo $info_progreso['progreso_porcentaje']; ?>" 
                 aria-valuemin="0" 
                 aria-valuemax="100">
            </div>
          </div>
          
          <?php if ($info_progreso['proxima_categoria']): ?>
            <div class="small-muted mt-2">
              <i class="bi bi-arrow-up-circle me-1"></i>
              <?php echo $info_progreso['restantes']; ?> promociones más para 
              <strong><?php echo $info_progreso['proxima_categoria']; ?></strong>
            </div>
          <?php else: ?>
            <div class="small-muted mt-2 text-success">
              <i class="bi bi-trophy me-1"></i>
              ¡Has alcanzado la categoría máxima!
            </div>
          <?php endif; ?>
        </div>

        <!-- Información del sistema de categorías -->
        <div class="px-2 pb-4">
          <div class="small-muted mb-2">Sistema de categorías</div>
          <div class="small text-muted">
            <div class="d-flex justify-content-between mb-1">
              <span>Inicial</span>
              <span>0-4 usos</span>
            </div>
            <div class="d-flex justify-content-between mb-1">
              <span>Medium</span>
              <span>5-12 usos</span>
            </div>
            <div class="d-flex justify-content-between">
              <span>Premium</span>
              <span>13+ usos</span>
            </div>
          </div>
        </div>
      </nav>

      <main class="col-12 col-md-9 col-lg-10 py-4">
        <div class="container-fluid">

          <div class="row g-3 mb-3">
            <div class="col-12 col-md-4">
              <div class="card shadow-sm">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start">
                    <div>
                      <h6 class="mb-1">Promociones disponibles</h6>
                      <div class="h3 mb-0"><?php echo (int)$numPromosDisponibles; ?></div>
                      <div class="small-muted">Vigentes y accesibles para tu categoría</div>
                    </div>
                    <div>
                      <i class="bi bi-ticket-perforated" style="font-size:28px; color:var(--primary)"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12 col-md-4">
              <div class="card shadow-sm">
                <div class="card-body">
                  <h6 class="mb-1">Promociones usadas</h6>
                  <div class="h3 mb-0"><?php echo (int)$numPromosUsadas; ?></div>
                  <div class="small-muted">Últimos 6 meses</div>
                </div>
              </div>
            </div>

            <div class="col-12 col-md-4">
              <div class="card shadow-sm">
                <div class="card-body">
                  <h6 class="mb-1">Novedades activas</h6>
                  <div class="h3 mb-0"><?php echo (int)$numNovedadesActivas; ?></div>
                  <div class="small-muted">Para tu categoría</div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="row align-items-center mb-3">
             <div class="col-12 text-md-end mt-2 mt-md-0">
               <a class="btn btn-primary btn-sm" href="./PromocionesUsuario.php" role="button">Mis Promociones</a>
               <a class="btn btn-outline-secondary btn-sm ms-2" href="./Novedades.php" role="button">Ver Novedades</a>
             </div>
          </div>

          <!-- === PROMOS === -->
          <section class="container my-5">
            <h2 class="h4 mb-3">Promociones Recomendadas</h2>

            <div class="row g-3" id="promos-container">
              <?php if ($totalPromos === 0): ?>
                <p class="text-secondary">No hay promociones disponibles por el momento.</p>
              <?php else: ?>
                <?php foreach ($promociones as $i => $p): 
                  $id          = (int)$p['IDpromocion'];
                  $desc        = htmlspecialchars($p['descripcion']);
                  $desde       = htmlspecialchars($p['desde']);
                  $hasta       = htmlspecialchars($p['hasta']);
                  $localNombre = htmlspecialchars($p['local_nombre'] ?? '');
                  $localCodigo = (string)($p['codigo'] ?? $p['localFk']);
                  $ubicNombre  = htmlspecialchars($p['ubicacion_nombre'] ?? '');
                  $catMin      = htmlspecialchars($p['categoriaHabilitada'] ?? 'Inicial');
                  $diaVal      = htmlspecialchars($p['dia'] ?? ''); // numérico
                ?>
                <div class="col-md-4 promo-card <?= $i >= 3 ? 'd-none extra-promo' : '' ?>" id="promo-<?= $id ?>">
                  <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                      <div class="d-flex justify-content-between">
                        <span class="badge badge-cat"><?= $catMin ?></span>
                        <small class="text-muted">#<?= $id ?></small>
                      </div>

                      <h5 class="card-title mt-2"><?= $desc ?></h5>
                      <p class="card-text text-secondary mb-1">
                        <i class="bi bi-shop"></i> <?= $localNombre ?>
                      </p>
                      <div class="mb-2">
                        <span class="badge text-bg-light copy-chip" title="Copiar código de local" data-copy="<?= $localCodigo ?>">
                          Código de local: <?= $localCodigo ?> 📋
                        </span>
                      </div>

                      <p class="card-text small mb-2">
                        Desde <?= $desde ?> hasta <?= $hasta ?>
                      </p>
                      <?php if ($ubicNombre): ?>
                        <span class="badge bg-primary-subtle text-primary mb-3"><?= $ubicNombre ?></span>
                      <?php endif; ?>

                      <div class="mt-auto d-flex gap-2">
                        <button 
                          class="btn btn-outline-secondary btn-sm flex-fill btn-detalle"
                          data-bs-toggle="modal"
                          data-bs-target="#detallePromoModal"
                          data-id="<?= $id ?>"
                          data-desc="<?= $desc ?>"
                          data-local="<?= $localNombre ?>"
                          data-codigo="<?= $localCodigo ?>"
                          data-desde="<?= $desde ?>"
                          data-hasta="<?= $hasta ?>"
                          data-cat="<?= $catMin ?>"
                          data-dia="<?= $diaVal ?>"
                          data-ubic="<?= $ubicNombre ?>"
                        >
                          Detalle
                        </button>

                        <button class="btn btn-primary btn-sm flex-fill btn-solicitar" data-id="<?= $id ?>">
                          Solicitar
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>

            <?php if ($totalPromos > 3): ?>
              <div class="text-center mt-3">
                <button id="btnMostrarMas" class="btn btn-outline-primary">
                  Mostrar más
                </button>
              </div>
            <?php endif; ?>
          </section>

          <div class="row mt-4">
            <div class="col-12 col-lg-6">
              <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <span>Historial de uso</span>
                </div>
                <div class="card-body">
                  <?php if (empty($historialUso)): ?>
                    <div class="historial-empty">
                      <i class="bi bi-clock-history" style="font-size: 2rem;"></i>
                      <p class="mt-2 mb-0">No hay historial de uso</p>
                      <small class="text-muted">Tus promociones solicitadas aparecerán aquí</small>
                    </div>
                  <?php else: ?>
                    <div class="historial-list">
                      <?php foreach ($historialUso as $historial): ?>
                        <div class="historial-item">
                          <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="mb-0"><?php echo htmlspecialchars($historial['descripcion_promo']); ?></h6>
                            <?php
                            $estado = $historial['estado'];
                            $badgeClass = '';
                            $badgeText = '';
                            
                            if ($estado == 0) {
                                $badgeClass = 'estado-pendiente';
                                $badgeText = '⏳ Pendiente';
                            } elseif ($estado == 1) {
                                $badgeClass = 'estado-aceptado';
                                $badgeText = '✅ Aceptado';
                            } else {
                                $badgeClass = 'estado-rechazado';
                                $badgeText = '❌ Rechazado';
                            }
                            ?>
                            <span class="badge estado-badge <?php echo $badgeClass; ?>">
                              <?php echo $badgeText; ?>
                            </span>
                          </div>
                          <p class="small text-muted mb-1">
                            <i class="bi bi-shop me-1"></i>
                            <?php echo htmlspecialchars($historial['nombre_local']); ?>
                          </p>
                          <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                              <i class="bi bi-calendar me-1"></i>
                              <?php echo date('d/m/Y', strtotime($historial['fechaUso'])); ?>
                            </small>
                            <small class="text-muted">
                              <?php echo htmlspecialchars($historial['categoriaHabilitada']); ?>
                            </small>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <div class="col-12 col-lg-6">
              <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <span>Novedades para ti</span>
                  <a href="./Novedades.php" class="btn btn-outline-primary btn-sm">Ver todo</a>
                </div>
                <div class="card-body">
                  <?php if (empty($novedadesRecientes)): ?>
                    <div class="novedades-empty">
                      <i class="bi bi-megaphone" style="font-size: 2rem;"></i>
                      <p class="mt-2 mb-0">No hay novedades recientes</p>
                      <small class="text-muted">Las novedades para tu categoría aparecerán aquí</small>
                    </div>
                  <?php else: ?>
                    <div class="novedades-list">
                      <?php foreach ($novedadesRecientes as $novedad): ?>
                        <div class="novedad-item">
                          <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="mb-0"><?php echo htmlspecialchars($novedad['cabecera'] ?? 'Sin título'); ?></h6>
                            <span class="badge novedad-badge">
                              <?php echo htmlspecialchars($novedad['categoriaHabilitada'] ?? 'General'); ?>
                            </span>
                          </div>
                          
                          <p class="small text-muted mb-2">
                            <?php 
                            $descripcion = $novedad['descripcion'] ?? '';
                            if (strlen($descripcion) > 120) {
                                $descripcion = substr($descripcion, 0, 120) . '...';
                            }
                            echo htmlspecialchars($descripcion);
                            ?>
                          </p>
                          
                          <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                              <i class="bi bi-calendar me-1"></i>
                              <?php 
                              $desde = $novedad['desde'] ?? '';
                              $hasta = $novedad['hasta'] ?? '';
                              if ($desde && $hasta) {
                                  echo date('d/m/Y', strtotime($desde)) . ' - ' . date('d/m/Y', strtotime($hasta));
                              } elseif ($desde) {
                                  echo 'Desde ' . date('d/m/Y', strtotime($desde));
                              } else {
                                  echo 'Fecha no especificada';
                              }
                              ?>
                            </small>
                            <?php if (strlen($novedad['descripcion'] ?? '') > 120): ?>
                              <a href="./Novedades.php" class="btn btn-sm btn-outline-primary">Leer más</a>
                            <?php endif; ?>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>

        </div>
      </main>
    </div>
  </div>

  <div class="modal fade" id="detallePromoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Detalle de la promoción</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <dl class="row mb-0">
            <dt class="col-5">Local</dt><dd class="col-7" id="m-local"></dd>
            <dt class="col-5">Descripción</dt><dd class="col-7" id="m-desc"></dd>
            <dt class="col-5">Vigencia</dt><dd class="col-7"><span id="m-desde"></span> — <span id="m-hasta"></span></dd>
            <dt class="col-5">Días válidos</dt><dd class="col-7" id="m-dias"></dd>
            <dt class="col-5">Categoría mínima</dt><dd class="col-7" id="m-cat"></dd>
            <dt class="col-5">Código de local</dt><dd class="col-7" id="m-codigo" class="copy-chip"></dd>
            <dt class="col-5">Ubicación</dt><dd class="col-7" id="m-ubic"></dd>
          </dl>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button class="btn btn-primary" id="m-solicitar" data-id="">Solicitar descuento</button>
        </div>
      </div>
    </div>
  </div>

  <div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="toastMsg" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body" id="toastText">Promoción solicitada</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  document.getElementById('btnMostrarMas')?.addEventListener('click', function(){
    document.querySelectorAll('.extra-promo').forEach(el => el.classList.remove('d-none'));
    this.remove();
  });

  document.addEventListener('click', (e) => {
    const chip = e.target.closest('.copy-chip');
    if (chip && chip.dataset.copy){
      const text = chip.dataset.copy;
      navigator.clipboard.writeText(text).then(() => showToast('Código copiado: ' + text));
    }
  });

  function diaLabel(d){
    const n = parseInt(d, 10);
    const w0 = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];      
    const w1 = [null,'Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado']; 
    if (!isFinite(n)) return '—';
    if (n >= 0 && n <= 6) return w0[n];
    if (n >= 1 && n <= 7) return w1[n];
    return '—';
  }

  const modal = document.getElementById('detallePromoModal');
  modal?.addEventListener('show.bs.modal', (ev) => {
    const btn = ev.relatedTarget;
    if (!btn) return;
    const id     = btn.dataset.id;
    const desc   = btn.dataset.desc || '';
    const local  = btn.dataset.local || '';
    const desde  = btn.dataset.desde || '';
    const hasta  = btn.dataset.hasta || '';
    const cat    = btn.dataset.cat || '';
    const codigo = btn.dataset.codigo || '';
    const ubic   = btn.dataset.ubic || '';
    const dia    = btn.dataset.dia || '';

    document.getElementById('m-local').textContent  = local;
    document.getElementById('m-desc').textContent   = desc;
    document.getElementById('m-desde').textContent  = desde;
    document.getElementById('m-hasta').textContent  = hasta;
    document.getElementById('m-cat').textContent    = cat;
    document.getElementById('m-codigo').textContent = codigo;
    document.getElementById('m-ubic').textContent   = ubic || '—';
    document.getElementById('m-dias').textContent   = diaLabel(dia);

    const btnSolicitarModal = document.getElementById('m-solicitar');
    btnSolicitarModal.dataset.id = id;
  });

  // Acción SOLICITAR (cards y modal)
  document.addEventListener('click', async (e) => {
    const btnCard = e.target.closest('.btn-solicitar');
    const btnModal = e.target.id === 'm-solicitar' ? e.target : null;
    const id = btnCard?.dataset.id || btnModal?.dataset.id;
    if (!id) return;

    (btnCard || btnModal).disabled = true;

    try{
      const res = await fetch('../Model/ProcesarDashboardCliente.php', {
        method: 'POST',
        headers: { 'Content-Type':'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ action:'solicitar', idPromocion:id })
      });
      const data = await res.json();

      if (data.ok){
        const card = document.getElementById('promo-' + id);
        card?.remove();
        // cerrar modal si vino desde el modal
        if (btnModal){
          const instance = bootstrap.Modal.getInstance(document.getElementById('detallePromoModal'));
          instance?.hide();
        }
        showToast('Promoción solicitada');
        
        // Recargar la página para actualizar la categoría y progreso
        setTimeout(() => {
          window.location.reload();
        }, 1500);
      }else{
        showToast(data.msg || 'No se pudo solicitar', true);
        (btnCard || btnModal).disabled = false;
      }
    }catch{
      showToast('Error de red', true);
      (btnCard || btnModal).disabled = false;
    }
  });

  function showToast(text, isError=false){
    const toastEl = document.getElementById('toastMsg');
    const body    = document.getElementById('toastText');
    body.textContent = text;
    toastEl.classList.toggle('text-bg-danger', !!isError);
    toastEl.classList.toggle('text-bg-success', !isError);
    new bootstrap.Toast(toastEl).show();
  }
  </script>
</body>
</html>