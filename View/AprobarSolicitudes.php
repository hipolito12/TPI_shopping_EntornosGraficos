<?php
session_start();
require_once '../Model/AprobarSolicitudes.php';

$mensaje = '';
$tipoMensaje = '';

$pdo = getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $idSolicitud = $_POST['id_solicitud'] ?? 0;
    
    if (!$idSolicitud) {
        $mensaje = 'ID de solicitud no válido.';
        $tipoMensaje = 'danger';
    } else {
        // Datos de solicitud
        $query = "SELECT * FROM solicitud WHERE IDsolicitud = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$idSolicitud]);
        $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$solicitud) {
            $mensaje = 'Solicitud no encontrada.';
            $tipoMensaje = 'danger';
        } else {
            switch ($action) {
                case 'aprobar':
                    try {
                        $pdo->beginTransaction();
                        $resultadoUsuario = crearOActualizarUsuarioComerciante($solicitud, $pdo);
                        
                        if (!$resultadoUsuario) {
                            throw new Exception("Error al procesar el usuario.");
                        }

                        // Crear local
                        $localCodigo = crearLocalDesdeSolicitud($solicitud, $resultadoUsuario['id'], $pdo);
                        
                        if ($localCodigo) {
                            actualizarEstadoSolicitud($idSolicitud, 1, $pdo);
                            $pdo->commit();
                            $emailEnviado = enviarEmailAprobacion($solicitud, $localCodigo, $resultadoUsuario);
                            
                            $mensaje = "¡Solicitud APROBADA con éxito! ";
                            $mensaje .= "Se generó el usuario (Rol Comerciante) y el Local. ";
                            $mensaje .= $emailEnviado ? "Notificación enviada por email." : "No se pudo enviar el email, pero el registro se completó.";
                            $tipoMensaje = 'success';
                        } else {
                            throw new Exception('No se pudo generar el local. Verifique los datos.');
                        }
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $mensaje = 'Error al aprobar: ' . $e->getMessage();
                        $tipoMensaje = 'danger';
                    }
                    break;
                    
                case 'rechazar':
                    try {
                        if (actualizarEstadoSolicitud($idSolicitud, 2, $pdo)) {
                            enviarEmailRechazo($solicitud);
                            $mensaje = 'Solicitud RECHAZADA exitosamente. Se notificó al usuario.';
                            $tipoMensaje = 'warning';
                        } else {
                            $mensaje = 'Error al intentar rechazar la solicitud.';
                            $tipoMensaje = 'danger';
                        }
                    } catch (Exception $e) {
                        $mensaje = 'Error: ' . $e->getMessage();
                        $tipoMensaje = 'danger';
                    }
                    break;
            }
        }
    }
    
    header('Location: AprobarSolicitudes.php?mensaje=' . urlencode($mensaje) . '&tipo=' . $tipoMensaje);
    exit;
}

if (isset($_GET['mensaje'])) {
    $mensaje = $_GET['mensaje'];
    $tipoMensaje = $_GET['tipo'] ?? 'info';
}

$solicitudesPendientes = getSolicitudesPendientes();
$solicitudesAprobadas = getSolicitudesAprobadas();
$solicitudesRechazadas = getSolicitudesRechazadas();
$estadisticas = getEstadisticasSolicitudes();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aprobar Solicitudes - ShoppingGenerico</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary: #4A3BC7;
            --primary-rgb: 74, 59, 199;
            --subtle: #F3F1FF;
            --muted: #6c6c6c;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --info: #17a2b8;
        }

        body {
            background: linear-gradient(180deg, #fff 0%, var(--subtle) 100%);
            min-height: 100vh;
        }

        .navbar, .card-header {
            background: var(--primary);
            color: #fff;
        }

        .sidebar {
            min-height: 100vh;
            background: #fff;
            border-right: 1px solid #e9e9ef;
        }

        .sidebar .nav-link {
            color: #333;
        }

        .sidebar .nav-link.active {
            background: rgba(var(--primary-rgb), 0.08);
            color: var(--primary);
            border-radius: .5rem;
        }

        .solicitud-card {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            transition: all 0.3s ease;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .solicitud-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .solicitud-card.pendiente { border-left: 5px solid var(--warning); }
        .solicitud-card.aprobada { border-left: 5px solid var(--success); }
        .solicitud-card.rechazada { border-left: 5px solid var(--danger); }

        .stats-card {
            background: white;
            border-radius: 10px;
            border-left: 4px solid var(--primary);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .badge-estado {
            font-size: 0.8rem;
            padding: 0.5em 0.8em;
        }
        .estado-pendiente { background-color: var(--warning); color: #000; }
        .estado-aprobado { background-color: var(--success); color: #fff; }
        .estado-rechazado { background-color: var(--danger); color: #fff; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="#">
                <i class="bi bi-shield-lock me-2"></i><strong>ShoppingUTN - Admin</strong>
            </a>

            <div class="d-flex align-items-center ms-auto">
                <div class="dropdown">
                    <a class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" href="#" id="userDropdown" data-bs-toggle="dropdown">
                        <span class="me-2"> <strong><?php echo htmlspecialchars($_SESSION['Nombre'] ?? 'Admin'); ?></strong></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="./DashboardAdministrador.php">Menu</a></li>
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
                    <h6 class="text-muted">Panel de Administración</h6>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="./DashboardAdministrador.php">
                                <i class="bi bi-speedometer2 me-2"></i>Menu
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="./GestionLocales.php">
                                <i class="bi bi-shop me-2"></i>Gestión de Locales
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link active" href="./AprobarSolicitudes.php">
                                <i class="bi bi-clipboard-check me-2"></i>Aprobar Solicitudes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="./AprobarPromociones.php">
                                <i class="bi bi-tag me-2"></i>Aprobar Promociones
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link " href="./GestionNovedades.php">
                                <i class="bi bi-megaphone me-2"></i>Gestión de Novedades
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link  " href="./Reportes.php">
                                <i class="bi bi-graph-up me-2"></i>Reportes
                            </a>
                        </li>
                        <li class="nav-item ">
    <a class="nav-link" href="./GestionContacto.php">
        <i class="bi bi-envelope me-2"></i>Contactos
    </a>
</li>
                    </ul>
                </div>
            </nav>

            <main class="col-12 col-md-9 col-lg-10 py-4">
                <div class="container-fluid">
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="h3 mb-0 text-gray-800">Aprobar Solicitudes de Locales</h2>
                            <p class="text-muted small">Gestiona las nuevas peticiones de apertura de tiendas.</p>
                        </div>
                    </div>

                    <?php if ($mensaje): ?>
                        <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show shadow-sm" role="alert">
                            <i class="bi <?php echo ($tipoMensaje == 'success') ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?> me-2"></i>
                            <?php echo htmlspecialchars($mensaje); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="stats-card p-3 h-100">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1 small">Pendientes</h6>
                                        <h3 class="mb-0 text-warning"><?php echo $estadisticas['pendientes']; ?></h3>
                                    </div>
                                    <div class="icon-shape bg-warning text-white rounded-circle p-2 bg-opacity-75">
                                        <i class="bi bi-clock-history fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stats-card p-3 h-100">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1 small">Aprobadas</h6>
                                        <h3 class="mb-0 text-success"><?php echo $estadisticas['aprobadas_total']; ?></h3>
                                    </div>
                                    <div class="icon-shape bg-success text-white rounded-circle p-2 bg-opacity-75">
                                        <i class="bi bi-check-lg fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stats-card p-3 h-100">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1 small">Rechazadas</h6>
                                        <h3 class="mb-0 text-danger"><?php echo $estadisticas['rechazadas_total']; ?></h3>
                                    </div>
                                    <div class="icon-shape bg-danger text-white rounded-circle p-2 bg-opacity-75">
                                        <i class="bi bi-x-lg fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stats-card p-3 h-100">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1 small">Tasa Aprob.</h6>
                                        <h3 class="mb-0 text-info"><?php echo $estadisticas['tasa_aprobacion']; ?>%</h3>
                                    </div>
                                    <div class="icon-shape bg-info text-white rounded-circle p-2 bg-opacity-75">
                                        <i class="bi bi-percent fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header bg-white p-0">
                            <ul class="nav nav-tabs card-header-tabs m-0" id="solicitudesTab" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active py-3 px-4 border-top-0 border-start-0" id="pendientes-tab" data-bs-toggle="tab" data-bs-target="#pendientes" type="button">
                                        Pendientes <span class="badge bg-warning text-dark ms-2 rounded-pill"><?php echo count($solicitudesPendientes); ?></span>
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link py-3 px-4 border-top-0" id="aprobadas-tab" data-bs-toggle="tab" data-bs-target="#aprobadas" type="button">
                                        Aprobadas <span class="badge bg-success ms-2 rounded-pill"><?php echo count($solicitudesAprobadas); ?></span>
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link py-3 px-4 border-top-0 border-end-0" id="rechazadas-tab" data-bs-toggle="tab" data-bs-target="#rechazadas" type="button">
                                        Rechazadas <span class="badge bg-danger ms-2 rounded-pill"><?php echo count($solicitudesRechazadas); ?></span>
                                    </button>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="card-body p-4 bg-light">
                            <div class="tab-content" id="solicitudesTabContent">
                                
                                <div class="tab-pane fade show active" id="pendientes" role="tabpanel">
                                    <?php if (empty($solicitudesPendientes)): ?>
                                        <div class="text-center py-5">
                                            <i class="bi bi-clipboard-check text-muted" style="font-size: 3rem; opacity: 0.5;"></i>
                                            <h5 class="mt-3 text-muted">¡Todo al día!</h5>
                                            <p class="text-muted small">No hay solicitudes pendientes de revisión.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="row g-3">
                                            <?php foreach ($solicitudesPendientes as $solicitud): ?>
                                                <div class="col-12 col-xl-6">
                                                    <div class="solicitud-card pendiente h-100 p-3">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <div>
                                                                <h5 class="mb-1 fw-bold text-primary"><?php echo htmlspecialchars($solicitud['nombreLocal']); ?></h5>
                                                                <span class="badge bg-light text-dark border"><i class="bi bi-shop me-1"></i><?php echo htmlspecialchars($solicitud['rubro']); ?></span>
                                                            </div>
                                                            <span class="badge estado-pendiente shadow-sm">Pendiente</span>
                                                        </div>
                                                        
                                                        <hr class="my-2 text-muted opacity-25">

                                                        <div class="row g-2 small mb-3">
                                                            <div class="col-6">
                                                                <strong class="text-secondary d-block">Solicitante</strong>
                                                                <?php echo htmlspecialchars($solicitud['nombre']); ?>
                                                            </div>
                                                            <div class="col-6">
                                                                <strong class="text-secondary d-block">DNI / CUIL</strong>
                                                                <?php echo htmlspecialchars($solicitud['dni']); ?> / <?php echo htmlspecialchars($solicitud['cuil']); ?>
                                                            </div>
                                                            <div class="col-6">
                                                                <strong class="text-secondary d-block">Email</strong>
                                                                <?php echo htmlspecialchars($solicitud['email']); ?>
                                                            </div>
                                                            <div class="col-6">
                                                                <strong class="text-secondary d-block">Ubicación</strong>
                                                                <?php echo htmlspecialchars($solicitud['ubicacion_nombre']); ?>
                                                            </div>
                                                        </div>

                                                        <div class="d-flex gap-2 justify-content-end mt-auto">
                                                            <form method="POST" onsubmit="return confirm('¿Rechazar esta solicitud? Esta acción es irreversible.');">
                                                                <input type="hidden" name="action" value="rechazar">
                                                                <input type="hidden" name="id_solicitud" value="<?php echo $solicitud['IDsolicitud']; ?>">
                                                                <button type="submit" class="btn btn-outline-danger btn-sm px-3">
                                                                    <i class="bi bi-x-lg me-1"></i>Rechazar
                                                                </button>
                                                            </form>

                                                            <form method="POST" onsubmit="return confirm('¿APROBAR solicitud?\n\n- Se creará el usuario (Rol Comerciante).\n- Se creará el Local.\n- Se enviará email con credenciales.');">
                                                                <input type="hidden" name="action" value="aprobar">
                                                                <input type="hidden" name="id_solicitud" value="<?php echo $solicitud['IDsolicitud']; ?>">
                                                                <button type="submit" class="btn btn-success btn-sm px-3 fw-bold shadow-sm">
                                                                    <i class="bi bi-check-lg me-1"></i>Aprobar & Crear
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="tab-pane fade" id="aprobadas" role="tabpanel">
                                    <?php if (empty($solicitudesAprobadas)): ?>
                                        <p class="text-center text-muted py-4">No hay historial de solicitudes aprobadas.</p>
                                    <?php else: ?>
                                        <div class="table-responsive bg-white rounded shadow-sm border">
                                            <table class="table table-hover mb-0 align-middle">
                                                <thead class="bg-light text-secondary">
                                                    <tr>
                                                        <th class="ps-4">Local</th>
                                                        <th>Solicitante</th>
                                                        <th>Contacto</th>
                                                        <th>Ubicación</th>
                                                        <th class="text-end pe-4">Estado</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($solicitudesAprobadas as $solicitud): ?>
                                                        <tr>
                                                            <td class="ps-4 fw-medium text-primary">
                                                                <?php echo htmlspecialchars($solicitud['nombreLocal']); ?>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex flex-column">
                                                                    <span class="fw-bold"><?php echo htmlspecialchars($solicitud['nombre']); ?></span>
                                                                    <span class="small text-muted">DNI: <?php echo htmlspecialchars($solicitud['dni']); ?></span>
                                                                </div>
                                                            </td>
                                                            <td class="small">
                                                                <?php echo htmlspecialchars($solicitud['email']); ?><br>
                                                                <?php echo htmlspecialchars($solicitud['telefono']); ?>
                                                            </td>
                                                            <td class="small"><?php echo htmlspecialchars($solicitud['ubicacion_nombre']); ?></td>
                                                            <td class="text-end pe-4">
                                                                <span class="badge estado-aprobado rounded-pill">Aprobada</span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="tab-pane fade" id="rechazadas" role="tabpanel">
                                    <?php if (empty($solicitudesRechazadas)): ?>
                                        <p class="text-center text-muted py-4">No hay historial de solicitudes rechazadas.</p>
                                    <?php else: ?>
                                        <div class="table-responsive bg-white rounded shadow-sm border">
                                            <table class="table table-hover mb-0 align-middle">
                                                <thead class="bg-light text-secondary">
                                                    <tr>
                                                        <th class="ps-4">Local Solicitado</th>
                                                        <th>Solicitante</th>
                                                        <th>Rubro</th>
                                                        <th>Ubicación</th>
                                                        <th class="text-end pe-4">Estado</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($solicitudesRechazadas as $solicitud): ?>
                                                        <tr>
                                                            <td class="ps-4 fw-medium text-secondary">
                                                                <?php echo htmlspecialchars($solicitud['nombreLocal']); ?>
                                                            </td>
                                                            <td>
                                                                <?php echo htmlspecialchars($solicitud['nombre']); ?><br>
                                                                <small class="text-muted"><?php echo htmlspecialchars($solicitud['email']); ?></small>
                                                            </td>
                                                            <td class="small"><?php echo htmlspecialchars($solicitud['rubro']); ?></td>
                                                            <td class="small"><?php echo htmlspecialchars($solicitud['ubicacion_nombre']); ?></td>
                                                            <td class="text-end pe-4">
                                                                <span class="badge estado-rechazado rounded-pill">Rechazada</span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>

                            </div> </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-cerrar alertas
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(el => {
                const alert = new bootstrap.Alert(el);
                alert.close();
            });
        }, 5000);
    </script>
</body>
</html>