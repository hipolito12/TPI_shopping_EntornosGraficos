<?php

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../Controller/DashboardTiendaController.php';

if (!isset($_SESSION['IDusuario']) || $_SESSION['Rol'] !='Comerciante') {
  session_unset();
    header("Location: ../index.php");
    exit;
}

if (isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0') {
}

$IDU = (int)$_SESSION['IDusuario'];
$local = getLocalPorUsuario($IDU);
if (!$local) {
    die("No se encontró el local para este usuario.");
}

$idLocal = $local['IDlocal'];
$nombreLocal = $local['nombre'];
$rubroLocal = $local['rubro'];

$estadisticas = getEstadisticasLocal($idLocal);
$promociones = getPromocionesPorLocal($idLocal);
$solicitudesPendientes = getSolicitudesPendientesPorLocal($idLocal);


?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Menu-Local</title>

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

    /* Estilos para listas */
    .promocion-item, .solicitud-item {
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
    
    .empty-state {
        text-align: center;
        padding: 2rem;
        color: var(--muted);
    }

    .usos-badge {
        background-color: rgba(var(--primary-rgb), 0.1);
        color: var(--primary);
        font-size: 0.75rem;
    }

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
        <strong>ShoppingUTN - Local</strong>
      </a>

      <div class="d-flex align-items-center ms-auto">
        <div class="dropdown">
          <a class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" href="#" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <strong class="me-2"><?php echo htmlspecialchars($_SESSION['Nombre'] ?? 'Usuario'); ?></strong>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li><a class="dropdown-item" href="./DashBoardTienda.php">Inicio</a></li>
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
            <li class="nav-item"><a class="nav-link active" href="./DashboardTienda.php"><i class="bi bi-house-door-fill me-2"></i>Inicio</a></li>
            <li class="nav-item">
                            <a class="nav-link " href="./CrearPromocion.php">
                                <i class="bi bi-plus-circle me-2"></i>Crear Promoción
                            </a>
                        </li>
            <li class="nav-item"><a class="nav-link" href="./HistorialUsos.php"><i class="bi bi-list-ul me-2"></i> Historial</a></li>
<li class="nav-item">
    <a class="nav-link" href="./Contacto.php">
        <i class="bi bi-envelope me-2"></i>Contactos
    </a>
</li>          </ul>
        </div>

        <hr>

        <div class="px-2 pb-4">
          <div class="small-muted mb-2">Tu local</div>
          <div class="d-flex align-items-center">
            <span class="badge badge-cat py-2 px-3"><?php echo htmlspecialchars($nombreLocal); ?></span>
          </div>
          <div class="mt-2">
            <small class="text-muted"><?php echo htmlspecialchars($rubroLocal); ?></small>
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
                      <h6 class="mb-1">Promociones activas</h6>
                      <div class="h3 mb-0"><?php echo $estadisticas['promociones_activas']; ?></div>
                      <div class="small-muted">Vigentes y aprobadas</div>
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
                  <h6 class="mb-1">Solicitudes pendientes</h6>
                  <div class="h3 mb-0"><?php echo $estadisticas['solicitudes_pendientes']; ?></div>
                  <div class="small-muted">Esperando tu respuesta</div>
                </div>
              </div>
            </div>

            <div class="col-12 col-md-4">
              <div class="card shadow-sm">
                <div class="card-body">
                  <h6 class="mb-1">Total de usos</h6>
                  <div class="h3 mb-0"><?php echo $estadisticas['total_usos']; ?></div>
                  <div class="small-muted">Promociones aceptadas</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Botones de acción -->
          <div class="row mb-4">
            <div class="col-12">
              <div class="d-flex gap-2">
                <a href="./CrearPromocion.php" class="btn btn-primary">
                  <i class="bi bi-plus-circle me-2"></i>Crear Nueva Promoción
                </a>
                <a href="./GestionarPromociones.php" class="btn btn-outline-primary">
                  <i class="bi bi-list-ul me-2"></i>Gestionar Todas las Promociones
                </a>
              </div>
            </div>
          </div>

          <div class="row mt-4">
            <div class="col-12 col-lg-6 mb-4">
              <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <span>Promociones Activas</span>
                  <span class="badge bg-primary"><?php echo count(array_filter($promociones, function($p) { 
                      return $p['estado'] == 1 && date('Y-m-d') >= $p['desde'] && date('Y-m-d') <= $p['hasta']; 
                  })); ?></span>
                </div>
                <div class="card-body">
                  <?php 
                  $promocionesActivas = array_filter($promociones, function($p) { 
                      return $p['estado'] == 1 && date('Y-m-d') >= $p['desde'] && date('Y-m-d') <= $p['hasta']; 
                  });
                  ?>
                  <?php if (empty($promocionesActivas)): ?>
                    <div class="empty-state">
                      <i class="bi bi-ticket-perforated" style="font-size: 2rem;"></i>
                      <p class="mt-2 mb-0">No hay promociones activas</p>
                      <small class="text-muted">Crea una nueva promoción para comenzar</small>
                    </div>
                  <?php else: ?>
                    <div class="promociones-list">
                      <?php foreach ($promocionesActivas as $promocion): ?>
                        <div class="promocion-item">
                          <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="mb-0"><?php echo htmlspecialchars($promocion['descripcion']); ?></h6>
                            <div class="d-flex gap-1">
                              <span class="badge usos-badge">
                                <i class="bi bi-person-fill me-1"></i><?php echo $promocion['total_usos']; ?>
                              </span>
                              <button class="btn btn-sm btn-outline-danger btn-eliminar-promocion" 
                                      data-id="<?php echo $promocion['IDpromocion']; ?>" 
                                      data-desc="<?php echo htmlspecialchars($promocion['descripcion']); ?>">
                                <i class="bi bi-trash"></i>
                              </button>
                            </div>
                          </div>
                          <p class="small text-muted mb-1">
                            <i class="bi bi-calendar me-1"></i>
                            <?php echo date('d/m/Y', strtotime($promocion['desde'])); ?> - <?php echo date('d/m/Y', strtotime($promocion['hasta'])); ?>
                          </p>
                          <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                              <?php echo htmlspecialchars($promocion['categoriaHabilitada']); ?>
                            </small>
                            <?php if ($promocion['dia']): ?>
                              <small class="text-muted">
                                Día: <?php 
                                $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                                echo $dias[$promocion['dia'] - 1] ?? 'N/A';
                                ?>
                              </small>
                            <?php endif; ?>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <div class="col-12 col-lg-6 mb-4">
              <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <span>Solicitudes Pendientes</span>
                  <span class="badge bg-warning"><?php echo count($solicitudesPendientes); ?></span>
                </div>
                <div class="card-body">
                  <?php if (empty($solicitudesPendientes)): ?>
                    <div class="empty-state">
                      <i class="bi bi-clock-history" style="font-size: 2rem;"></i>
                      <p class="mt-2 mb-0">No hay solicitudes pendientes</p>
                      <small class="text-muted">Las solicitudes de clientes aparecerán aquí</small>
                    </div>
                  <?php else: ?>
                    <div class="solicitudes-list">
                      <?php foreach ($solicitudesPendientes as $solicitud): ?>
                        <div class="solicitud-item">
                          <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="mb-0"><?php echo htmlspecialchars($solicitud['promocion_descripcion']); ?></h6>
                            <div class="d-flex gap-1">
                              <button class="btn btn-sm btn-success btn-aceptar" 
                                      data-usuario="<?php echo $solicitud['usuarioFk']; ?>" 
                                      data-promocion="<?php echo $solicitud['promoFK']; ?>">
                                <i class="bi bi-check-lg"></i>
                              </button>
                              <button class="btn btn-sm btn-danger btn-rechazar" 
                                      data-usuario="<?php echo $solicitud['usuarioFk']; ?>" 
                                      data-promocion="<?php echo $solicitud['promoFK']; ?>">
                                <i class="bi bi-x-lg"></i>
                              </button>
                            </div>
                          </div>
                          <p class="small text-muted mb-1">
                            <i class="bi bi-person me-1"></i>
                            <?php echo htmlspecialchars($solicitud['nombreUsuario']); ?> (<?php echo htmlspecialchars($solicitud['email']); ?>)
                          </p>
                          <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                              <i class="bi bi-calendar me-1"></i>
                              <?php echo date('d/m/Y', strtotime($solicitud['fechaUso'])); ?>
                            </small>
                            <span class="badge estado-badge estado-pendiente">Pendiente</span>
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

  <div class="modal fade" id="confirmarEliminarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Desactivar Promoción</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <p>¿Estás seguro de que deseas desactivar la promoción: <strong id="promocionNombre"></strong>?</p>
          <p class="text-warning">
            <i class="bi bi-exclamation-triangle"></i> 
            La promoción se desactivará y ya no estará disponible para los clientes.
          </p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-warning" id="confirmarEliminar">
            <i class="bi bi-eye-slash me-1"></i>Desactivar
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="toastMsg" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body" id="toastText">Acción completada</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.querySelectorAll('.btn-eliminar-promocion').forEach(button => {
      button.addEventListener('click', function() {
        const id = this.dataset.id;
        const desc = this.dataset.desc;
        document.getElementById('promocionNombre').textContent = desc;
        const modal = new bootstrap.Modal(document.getElementById('confirmarEliminarModal'));
        
        document.getElementById('confirmarEliminar').onclick = null;
        
        // Configurar nuevo evento
        document.getElementById('confirmarEliminar').onclick = async function() {
          const confirmBtn = this;
          confirmBtn.disabled = true;
          confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Procesando...';
          
          try {
            const response = await fetch('../Controller/DashboardTiendaController.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: new URLSearchParams({ 
                action: 'eliminar_promocion', 
                idPromocion: id 
              })
            });
            
            if (!response.ok) {
              throw new Error(`Error HTTP: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
              showToast('✅ ' + data.message);
              modal.hide();
              // Recargar después de mostrar el toast
              setTimeout(() => location.reload(), 1500);
            } else {
              showToast('❌ ' + (data.message || 'Error al desactivar la promoción'), true);
              confirmBtn.disabled = false;
              confirmBtn.innerHTML = '<i class="bi bi-eye-slash me-1"></i>Desactivar';
            }
          } catch (error) {
            console.error('Error completo:', error);
            showToast('❌ Error de conexión. Verifica tu internet e intenta nuevamente.', true);
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="bi bi-eye-slash me-1"></i>Desactivar';
          }
        };
        
        modal.show();
      });
    });

    document.querySelectorAll('.btn-aceptar').forEach(button => {
      button.addEventListener('click', function() {
        const usuarioFk = this.dataset.usuario;
        const promoFK = this.dataset.promocion;
        
        if (confirm('¿Estás seguro de que deseas ACEPTAR esta solicitud?')) {
          fetch('../Controller/DashboardTiendaController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'aceptar_solicitud', usuarioFk: usuarioFk, promoFK: promoFK })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              showToast(data.message);
              setTimeout(() => location.reload(), 1000);
            } else {
              showToast(data.message, true);
            }
          })
          .catch(error => {
            console.error('Error:', error);
            showToast('Error de red. Por favor, intenta nuevamente.', true);
          });
        }
      });
    });

    document.querySelectorAll('.btn-rechazar').forEach(button => {
      button.addEventListener('click', function() {
        const usuarioFk = this.dataset.usuario;
        const promoFK = this.dataset.promocion;
        
        if (confirm('¿Estás seguro de que deseas RECHAZAR esta solicitud?')) {
          fetch('../Controller/DashboardTiendaController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'rechazar_solicitud', usuarioFk: usuarioFk, promoFK: promoFK })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              showToast(data.message);
              setTimeout(() => location.reload(), 1000);
            } else {
              showToast(data.message, true);
            }
          })
          .catch(error => {
            console.error('Error:', error);
            showToast('Error de red. Por favor, intenta nuevamente.', true);
          });
        }
      });
    });

    function showToast(message, isError = false) {
      const toastEl = document.getElementById('toastMsg');
      const body = document.getElementById('toastText');
      body.textContent = message;
      toastEl.classList.toggle('text-bg-danger', isError);
      toastEl.classList.toggle('text-bg-success', !isError);
      new bootstrap.Toast(toastEl).show();
    }
  </script>
</body>
</html>