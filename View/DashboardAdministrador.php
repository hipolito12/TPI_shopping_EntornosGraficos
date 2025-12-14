<?php
session_start();
require_once '../Model/DashBoardAdmin.php';
require_once '../Model/GestionLocales.php';
require_once '../Model/ContactoModel.php'; // <-- AGREGADO para usar las funciones de contacto

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['IDusuario']) || $_SESSION['Rol'] !='Administrador') {
    session_unset();
    header("Location: ../index.php");
    exit;
}

// Paginación
$localesPorPagina = 5;
$paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($paginaActual < 1) {
    $paginaActual = 1;
}
$offset = ($paginaActual - 1) * $localesPorPagina;

// Acciones de locales
$mensajeLocal = '';
$tipoMensajeLocal = '';
$localEdit = null;
$mostrarModal = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_local'])) {
    $action = $_POST['action_local'];
    
    switch ($action) {
        case 'editar':
            $id = $_POST['id'];
            $datos = [
                'nombre' => trim($_POST['nombre']),
                'rubro' => trim($_POST['rubro']),
                'usuarioFK' => $_POST['usuarioFK'],
                'ubicacionFK' => $_POST['ubicacionFK']
            ];
            
            if (empty($datos['nombre']) || empty($datos['rubro']) || empty($datos['usuarioFK']) || empty($datos['ubicacionFK'])) {
                $mensajeLocal = 'Todos los campos son obligatorios.';
                $tipoMensajeLocal = 'danger';
            } else {
                if (actualizarLocal($id, $datos)) {
                    $mensajeLocal = 'Local actualizado exitosamente.';
                    $tipoMensajeLocal = 'success';
                } else {
                    $mensajeLocal = 'Error al actualizar el local.';
                    $tipoMensajeLocal = 'danger';
                }
            }
            break;
            
        case 'eliminar':
            $id = $_POST['id'];
            $result = eliminarLocal($id);
            $mensajeLocal = $result['message'];
            $tipoMensajeLocal = $result['success'] ? 'success' : 'danger';
            break;
    }
    
    // Recargar la página para ver cambios
    header('Location: DashboardAdministrador.php?mensaje_local=' . urlencode($mensajeLocal) . '&tipo_local=' . $tipoMensajeLocal);
    exit;
}

// Si hay mensaje en la URL, mostrarlo
if (isset($_GET['mensaje_local'])) {
    $mensajeLocal = $_GET['mensaje_local'];
    $tipoMensajeLocal = $_GET['tipo_local'] ?? 'info';
}

// Si se solicita editar un local
if (isset($_GET['editar_local'])) {
    $localEdit = getLocalById($_GET['editar_local']);
    if ($localEdit) {
        $mostrarModal = true;
    }
}


// Obtener ubicaciones y comerciantes para los formularios
$ubicaciones = getUbicaciones();
$comerciantes = getComerciantes();

// Obtener locales paginados usando la nueva función
$locales = getLocalesPaginados($localesPorPagina, $offset);
$totalLocales = getTotalLocales();
$totalPaginas = ceil($totalLocales / $localesPorPagina);

// Obtener datos para el dashboard
$reporteUsos = getReporteUsos($filtrosReporte);
$estadisticas = getEstadisticasGenerales();
$solicitudesPendientes = getSolicitudesPendientes();
$promocionesPendientes = getPromocionesPendientes();
$novedadesActivas = getNovedadesActivas();
$todosLocales = getAllLocales();

// Obtener contactos para mostrar
$contactos = getTodosLosContactos();

// Procesar filtros para reportes
$filtrosReporte = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filtro_reporte'])) {
    $filtrosReporte = [
        'fecha_desde' => $_POST['fecha_desde'] ?? '',
        'fecha_hasta' => $_POST['fecha_hasta'] ?? '',
        'local_id' => $_POST['local_id'] ?? ''
    ];
} else {
    // Por defecto, último mes
    $filtrosReporte = [
        'fecha_desde' => date('Y-m-d', strtotime('-1 month')),
        'fecha_hasta' => date('Y-m-d'),
        'local_id' => ''
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Administrador - ShoppingGenerico</title>
    
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

        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background: #3A2BA7;
            border-color: #3A2BA7;
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

        .stat-card {
            background: white;
            border-radius: 10px;
            border-left: 4px solid var(--primary);
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .quick-action-card {
            background: white;
            border-radius: 10px;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            height: 100%;
        }

        .quick-action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
            border-color: var(--primary);
        }

        .badge-estado {
            font-size: 0.75rem;
            padding: 0.35rem 0.65rem;
        }

        .estado-activo { background-color: var(--success); }
        .estado-inactivo { background-color: var(--danger); }
        .estado-pendiente { background-color: var(--warning); color: #000; }

        .table-hover tbody tr:hover {
            background-color: rgba(var(--primary-rgb), 0.04);
        }

        .filtros-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .nav-tabs .nav-link.active {
            color: var(--primary);
            font-weight: 600;
            border-bottom: 3px solid var(--primary);
        }

        .action-btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .pagination-custom .page-link {
            color: var(--primary);
            border: 1px solid #dee2e6;
        }

        .pagination-custom .page-item.active .page-link {
            background-color: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        .pagination-custom .page-link:hover {
            background-color: rgba(var(--primary-rgb), 0.1);
            border-color: var(--primary);
        }

        @media (max-width: 767px) {
            .sidebar {
                min-height: auto;
                border-right: none;
                border-bottom: 1px solid #eee;
            }
        }
        
        .modal-contacto .modal-dialog {
            max-width: 700px;
        }
        
        .modal-contacto .modal-body {
            max-height: 60vh;
            overflow-y: auto;
        }
        
        .contacto-cuerpo {
            white-space: pre-wrap;
            word-break: break-word;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="#">
                <strong>ShoppingUTN- Administrador</strong>
            </a>

            <div class="d-flex align-items-center ms-auto">
                <div class="dropdown">
                    <a class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" href="#" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <strong class="me-2"><?php echo htmlspecialchars($_SESSION['Nombre'] ?? 'Admin'); ?></strong>
                        <span class="badge bg-light text-primary">Administrador</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
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
            <!-- Sidebar -->
            <nav class="col-12 col-md-3 col-lg-2 px-3 sidebar">
                <div class="pt-3 pb-2">
                    <h6 class="text-muted">Panel de Administración</h6>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="./DashboardAdministrador.php">
                                <i class="bi bi-speedometer2 me-2"></i>Menu
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="./GestionLocales.php">
                                <i class="bi bi-shop me-2"></i>Gestión de Locales
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="./AprobarSolicitudes.php">
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
                            <a class="nav-link" href="./Reportes.php">
                                <i class="bi bi-graph-up me-2"></i>Reportes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="./GestionContacto.php">
                                <i class="bi bi-envelope me-2"></i>Contactos
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-12 col-md-9 col-lg-10 py-4">
                <div class="container-fluid">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="h3 mb-1">
                                <i class="bi bi-speedometer2 me-2" style="color: var(--primary);"></i>
                                Menu de Administración
                            </h1>
                            <p class="text-muted mb-0">Gestión completa del sistema ShoppingGenerico</p>
                        </div>
                        <div class="text-end">
                            <div class="text-muted small">Última actualización</div>
                            <div class="fw-medium"><?php echo date('d/m/Y H:i'); ?></div>
                        </div>
                    </div>

                    <!-- Estadísticas Principales -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="stat-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Locales</h6>
                                        <div class="h4 mb-0 text-primary"><?php echo $estadisticas['total_locales']; ?></div>
                                    </div>
                                    <i class="bi bi-shop" style="font-size: 1.5rem; color: var(--primary);"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="stat-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Solicitudes</h6>
                                        <div class="h4 mb-0 text-warning"><?php echo $estadisticas['solicitudes_pendientes']; ?></div>
                                    </div>
                                    <i class="bi bi-person-plus" style="font-size: 1.5rem; color: var(--warning);"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="stat-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Promociones Pend.</h6>
                                        <div class="h4 mb-0 text-info"><?php echo $estadisticas['promociones_pendientes']; ?></div>
                                    </div>
                                    <i class="bi bi-ticket-perforated" style="font-size: 1.5rem; color: var(--info);"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="stat-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Novedades Activas</h6>
                                        <div class="h4 mb-0 text-success"><?php echo $estadisticas['novedades_activas']; ?></div>
                                    </div>
                                    <i class="bi bi-megaphone" style="font-size: 1.5rem; color: var(--success);"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="stat-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Usos (30 días)</h6>
                                        <div class="h4 mb-0 text-danger"><?php echo $estadisticas['usos_30_dias']; ?></div>
                                    </div>
                                    <i class="bi bi-graph-up-arrow" style="font-size: 1.5rem; color: var(--danger);"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="stat-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Contactos</h6>
                                        <div class="h4 mb-0" style="color: #6f42c1;"><?php echo count($contactos); ?></div>
                                    </div>
                                    <i class="bi bi-envelope" style="font-size: 1.5rem; color: #6f42c1;"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones Rápidas -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">Acciones Rápidas</h5>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <a href="./GestionLocales.php" class="text-decoration-none">
                                        <div class="quick-action-card p-4 text-center">
                                            <i class="bi bi-plus-circle" style="font-size: 2rem; color: var(--primary);"></i>
                                            <h6 class="mt-2 mb-1">Crear Local</h6>
                                            <p class="text-muted small mb-0">Agregar nuevo local al sistema</p>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="./AprobarSolicitudes.php" class="text-decoration-none">
                                        <div class="quick-action-card p-4 text-center">
                                            <i class="bi bi-person-check" style="font-size: 2rem; color: var(--success);"></i>
                                            <h6 class="mt-2 mb-1">Validar Cuentas</h6>
                                            <p class="text-muted small mb-0">Revisar solicitudes de dueños</p>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="./AprobarPromociones.php" class="text-decoration-none">
                                        <div class="quick-action-card p-4 text-center">
                                            <i class="bi bi-clipboard-check" style="font-size: 2rem; color: var(--warning);"></i>
                                            <h6 class="mt-2 mb-1">Aprobar Promociones</h6>
                                            <p class="text-muted small mb-0">Revisar promociones pendientes</p>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="./GestionNovedades.php" class="text-decoration-none">
                                        <div class="quick-action-card p-4 text-center">
                                            <i class="bi bi-megaphone" style="font-size: 2rem; color: var(--info);"></i>
                                            <h6 class="mt-2 mb-1">Crear Novedad</h6>
                                            <p class="text-muted small mb-0">Publicar novedad del shopping</p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Locales Recientes -->
                        <div class="col-12 col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Locales Registrados</h5>
                                    <div>
                                        <a href="./GestionLocales.php" class="btn btn-sm btn-primary me-2">Ver Todos</a>
                                        <span class="badge bg-info">Página <?php echo $paginaActual; ?> de <?php echo $totalPaginas; ?></span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($mensajeLocal)): ?>
                                        <div class="alert alert-<?php echo $tipoMensajeLocal; ?> alert-dismissible fade show" role="alert">
                                            <?php echo htmlspecialchars($mensajeLocal); ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (empty($locales)): ?>
                                        <div class="text-center py-3">
                                            <i class="bi bi-shop" style="font-size: 2rem; color: var(--muted);"></i>
                                            <p class="text-muted mt-2 mb-0">No hay locales registrados</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Nombre</th>
                                                        <th>Rubro</th>
                                                        <th>Dueño</th>
                                                        <th>Ubicación</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($locales as $local): ?>
                                                        <tr>
                                                            <td>
                                                                <div class="fw-medium"><?php echo htmlspecialchars($local['nombre']); ?></div>
                                                                <small class="text-muted"><?php echo htmlspecialchars($local['rubro']); ?></small>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($local['dueño'] ?? 'N/A'); ?></td>
                                                            <td><?php echo htmlspecialchars($local['ubicacion_nombre'] ?? 'N/A'); ?></td>
                                                            <td>
                                                                <div class="btn-group btn-group-sm">
                                                                    <a href="DashboardAdministrador.php?editar_local=<?php echo $local['IDlocal']; ?>&pagina=<?php echo $paginaActual; ?>" 
                                                                       class="btn btn-outline-primary action-btn" 
                                                                       title="Editar">
                                                                        <i class="bi bi-pencil"></i>
                                                                    </a>
                                                                    <form method="POST" class="d-inline" 
                                                                          onsubmit="return confirm('¿Estás seguro de que deseas eliminar este local?');">
                                                                        <input type="hidden" name="action_local" value="eliminar">
                                                                        <input type="hidden" name="id" value="<?php echo $local['IDlocal']; ?>">
                                                                        <button type="submit" class="btn btn-outline-danger action-btn" title="Eliminar">
                                                                            <i class="bi bi-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        <!-- PAGINACIÓN -->
                                        <?php if ($totalPaginas > 1): ?>
                                        <nav aria-label="Paginación de locales" class="mt-3">
                                            <ul class="pagination pagination-custom justify-content-center">
                                                <!-- Botón Anterior -->
                                                <li class="page-item <?php echo $paginaActual == 1 ? 'disabled' : ''; ?>">
                                                    <a class="page-link" href="?pagina=<?php echo $paginaActual - 1; ?>" aria-label="Anterior">
                                                        <span aria-hidden="true">&laquo;</span>
                                                    </a>
                                                </li>
                                                
                                                <!-- Números de página -->
                                                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                                    <?php if ($i == $paginaActual || $i == $paginaActual - 1 || $i == $paginaActual + 1 || $i == 1 || $i == $totalPaginas): ?>
                                                        <li class="page-item <?php echo $i == $paginaActual ? 'active' : ''; ?>">
                                                            <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                                                        </li>
                                                    <?php elseif ($i == $paginaActual - 2 || $i == $paginaActual + 2): ?>
                                                        <li class="page-item disabled">
                                                            <span class="page-link">...</span>
                                                        </li>
                                                    <?php endif; ?>
                                                <?php endfor; ?>
                                                
                                                <!-- Botón Siguiente -->
                                                <li class="page-item <?php echo $paginaActual == $totalPaginas ? 'disabled' : ''; ?>">
                                                    <a class="page-link" href="?pagina=<?php echo $paginaActual + 1; ?>" aria-label="Siguiente">
                                                        <span aria-hidden="true">&raquo;</span>
                                                    </a>
                                                </li>
                                            </ul>
                                            <div class="text-center text-muted small mt-2">
                                                Mostrando <?php echo count($locales); ?> de <?php echo $totalLocales; ?> locales
                                            </div>
                                        </nav>
                                        <?php endif; ?>
                                        <!-- FIN PAGINACIÓN -->
                                        
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Solicitudes de Contacto (REEMPLAZA Solicitudes Pendientes) -->
                        <div class="col-12 col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="bi bi-envelope me-2"></i>Solicitudes de Contacto</h5>
                                    <div>
                                        <?php 
                                        $contactosPendientes = array_filter($contactos, function($contacto) {
                                            return $contacto['estado'] == 0;
                                        });
                                        ?>
                                        <span class="badge bg-warning"><?php echo count($contactosPendientes); ?> pendientes</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($contactos)): ?>
                                        <div class="text-center py-3">
                                            <i class="bi bi-envelope-check" style="font-size: 2rem; color: var(--success);"></i>
                                            <p class="text-muted mt-2 mb-0">No hay solicitudes de contacto</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Asunto</th>
                                                        <th>Estado</th>
                                                        <th>Fecha</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach (array_slice($contactos, 0, 5) as $contacto): 
                                                        $estado_badge = $contacto['estado'] == 0 
                                                            ? '<span class="badge bg-warning">Pendiente</span>' 
                                                            : '<span class="badge bg-success">Resuelto</span>';
                                                        
                                                        $fecha = isset($contacto['fecha']) 
                                                            ? date('d/m/Y H:i', strtotime($contacto['fecha']))
                                                            : 'N/A';
                                                    ?>
                                                        <tr>
                                                            <td class="fw-bold">#<?php echo htmlspecialchars($contacto['id']); ?></td>
                                                            <td>
                                                                <div class="fw-medium text-truncate" style="max-width: 150px;" 
                                                                     title="<?php echo htmlspecialchars($contacto['asunto']); ?>">
                                                                    <?php echo htmlspecialchars($contacto['asunto']); ?>
                                                                </div>
                                                            </td>
                                                            <td><?php echo $estado_badge; ?></td>
                                                            <td>
                                                                <small class="text-muted"><?php echo $fecha; ?></small>
                                                            </td>
                                                            <td>
                                                                <div class="btn-group btn-group-sm">
                                                                    <!-- Botón para ver detalles con ojito -->
                                                                    <button type="button" class="btn btn-outline-info btn-sm" 
                                                                            data-bs-toggle="modal" 
                                                                            data-bs-target="#verContactoModal"
                                                                            data-asunto="<?php echo htmlspecialchars($contacto['asunto']); ?>"
                                                                            data-cuerpo="<?php echo htmlspecialchars($contacto['cuerpo']); ?>"
                                                                            data-id="<?php echo $contacto['id']; ?>"
                                                                            data-estado="<?php echo $contacto['estado']; ?>"
                                                                            data-fecha="<?php echo $fecha; ?>"
                                                                            title="Ver detalles">
                                                                        <i class="bi bi-eye"></i>
                                                                    </button>
                                                                    
                                                                    <?php if($contacto['estado'] == 0): ?>
                                                                    <button type="button" class="btn btn-outline-success btn-sm cerrar-contacto-btn" 
                                                                            data-id="<?php echo $contacto['id']; ?>"
                                                                            title="Marcar como resuelto">
                                                                        <i class="bi bi-check-lg"></i>
                                                                    </button>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="text-center mt-3">
                                            <a href="./GestionContacto.php" class="btn btn-sm btn-primary">
                                                <i class="bi bi-list me-1"></i> Ver todos los contactos
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reporte de Usos Recientes -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Reporte de Usos de Promociones</h5>
                        </div>
                        <div class="card-body">
                            <!-- Filtros -->
                            <div class="filtros-card p-3 mb-4">
                                <form method="POST" id="filtrosForm">
                                    <input type="hidden" name="filtro_reporte" value="1">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label for="fecha_desde" class="form-label">Fecha Desde</label>
                                            <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" 
                                                   value="<?php echo htmlspecialchars($filtrosReporte['fecha_desde']); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
                                            <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" 
                                                   value="<?php echo htmlspecialchars($filtrosReporte['fecha_hasta']); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="local_id" class="form-label">Local</label>
                                            <select class="form-select" id="local_id" name="local_id">
                                                <option value="">Todos los locales</option>
                                                <?php foreach ($todosLocales as $local): ?>
                                                    <option value="<?php echo $local['IDlocal']; ?>" 
                                                        <?php echo $filtrosReporte['local_id'] == $local['IDlocal'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($local['nombre']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary me-2">
                                                <i class="bi bi-filter me-1"></i>Filtrar
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" onclick="limpiarFiltros()">
                                                <i class="bi bi-arrow-clockwise me-1"></i>Limpiar
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Tabla de Reportes -->
                            <?php if (empty($reporteUsos)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-inbox" style="font-size: 3rem; color: var(--muted);"></i>
                                    <h5 class="mt-3 text-muted">No se encontraron registros</h5>
                                    <p class="text-muted">No hay usos de promociones que coincidan con los filtros aplicados.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Cliente</th>
                                                <th>Local</th>
                                                <th>Promoción</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($reporteUsos as $registro): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo date('d/m/Y', strtotime($registro['fechaUso'])); ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo date('H:i', strtotime($registro['fechaUso'])); ?></small>
                                                    </td>
                                                    <td>
                                                        <div class="fw-medium"><?php echo htmlspecialchars($registro['nombreUsuario']); ?></div>
                                                        <small class="text-muted">DNI: <?php echo htmlspecialchars($registro['DNI']); ?></small>
                                                    </td>
                                                    <td>
                                                        <div class="fw-medium"><?php echo htmlspecialchars($registro['local_nombre']); ?></div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($registro['local_rubro']); ?></small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($registro['promocion_descripcion']); ?></td>
                                                    <td>
                                                        <?php 
                                                        $estadoTexto = '';
                                                        $estadoClase = '';
                                                        switch ($registro['estado']) {
                                                            case '1':
                                                                $estadoTexto = 'Aceptado';
                                                                $estadoClase = 'estado-activo';
                                                                break;
                                                            case '2':
                                                                $estadoTexto = 'Rechazado';
                                                                $estadoClase = 'estado-inactivo';
                                                                break;
                                                            case '0':
                                                            default:
                                                                $estadoTexto = 'Pendiente';
                                                                $estadoClase = 'estado-pendiente';
                                                                break;
                                                        }
                                                        ?>
                                                        <span class="badge badge-estado <?php echo $estadoClase; ?>">
                                                            <?php echo $estadoTexto; ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal para Editar Local -->
    <div class="modal fade" id="modalEditarLocal" tabindex="-1" aria-labelledby="modalEditarLocalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarLocalLabel">
                        <i class="bi bi-shop me-2"></i>Editar Local
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="formEditarLocal">
                    <div class="modal-body">
                        <input type="hidden" name="action_local" value="editar">
                        <?php if ($localEdit): ?>
                            <input type="hidden" name="id" value="<?php echo $localEdit['IDlocal']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="edit_nombre" class="form-label">Nombre del Local</label>
                            <input type="text" class="form-control" id="edit_nombre" name="nombre" required
                                   value="<?php echo $localEdit ? htmlspecialchars($localEdit['nombre']) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_rubro" class="form-label">Rubro</label>
                            <input type="text" class="form-control" id="edit_rubro" name="rubro" required
                                   value="<?php echo $localEdit ? htmlspecialchars($localEdit['rubro']) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_usuarioFK" class="form-label">Dueño</label>
                            <select class="form-select" id="edit_usuarioFK" name="usuarioFK" required>
                                <option value="">Seleccionar dueño...</option>
                                <?php foreach ($comerciantes as $comerciante): ?>
                                    <option value="<?php echo $comerciante['IDusuario']; ?>"
                                        <?php echo ($localEdit && $localEdit['usuarioFK'] == $comerciante['IDusuario']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($comerciante['nombreUsuario']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_ubicacionFK" class="form-label">Ubicación</label>
                            <select class="form-select" id="edit_ubicacionFK" name="ubicacionFK" required>
                                <option value="">Seleccionar ubicación...</option>
                                <?php foreach ($ubicaciones as $ubicacion): ?>
                                    <option value="<?php echo $ubicacion['IDubicacion']; ?>"
                                        <?php echo ($localEdit && $localEdit['ubicacionFK'] == $ubicacion['IDubicacion']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($ubicacion['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i>Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Ver Contacto (NUEVO) -->
    <div class="modal fade modal-contacto" id="verContactoModal" tabindex="-1" aria-labelledby="verContactoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="verContactoModalLabel">
                        <i class="bi bi-envelope me-2"></i>Detalles del Mensaje
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Asunto:</label>
                        <p id="modal-asunto" class="form-control bg-light"></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mensaje:</label>
                        <div id="modal-cuerpo" class="contacto-cuerpo"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Estado:</label>
                                <p id="modal-estado" class="form-control bg-light"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Fecha:</label>
                                <p id="modal-fecha" class="form-control bg-light"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cerrar
                    </button>
                    <button type="button" class="btn btn-success" id="btn-cerrar-contacto">
                        <i class="bi bi-check-lg me-1"></i> Marcar como Resuelto
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar modal automáticamente si hay un local para editar
        <?php if ($mostrarModal): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = new bootstrap.Modal(document.getElementById('modalEditarLocal'));
            modal.show();
            
            // Limpiar parámetro de URL pero mantener la página
            if (window.history.replaceState) {
                const url = new URL(window.location);
                url.searchParams.delete('editar_local');
                window.history.replaceState({}, '', url);
            }
        });
        <?php endif; ?>

        // Cerrar modal y recargar manteniendo la página actual
        document.getElementById('modalEditarLocal')?.addEventListener('hidden.bs.modal', function () {
            const paginaActual = <?php echo $paginaActual; ?>;
            window.location.href = 'DashboardAdministrador.php?pagina=' + paginaActual;
        });

        // Limpiar mensajes después de 5 segundos
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        function limpiarFiltros() {
            document.getElementById('fecha_desde').value = '';
            document.getElementById('fecha_hasta').value = '';
            document.getElementById('local_id').value = '';
            document.getElementById('filtrosForm').submit();
        }

        // Validación de fechas
        document.getElementById('fecha_desde').addEventListener('change', function() {
            const fechaHasta = document.getElementById('fecha_hasta');
            if (this.value && fechaHasta.value && this.value > fechaHasta.value) {
                alert('La fecha "desde" no puede ser posterior a la fecha "hasta"');
                this.value = '';
            }
        });

        document.getElementById('fecha_hasta').addEventListener('change', function() {
            const fechaDesde = document.getElementById('fecha_desde');
            if (this.value && fechaDesde.value && this.value < fechaDesde.value) {
                alert('La fecha "hasta" no puede ser anterior a la fecha "desde"');
                this.value = '';
            }
        });

        // ========== CÓDIGO PARA EL MODAL DE CONTACTO ==========
        document.addEventListener('DOMContentLoaded', function() {
            const verContactoModal = document.getElementById('verContactoModal');
            let currentContactoId = null;
            let currentContactoEstado = null;
            
            // Llenar modal cuando se abre
            verContactoModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const asunto = button.getAttribute('data-asunto');
                const cuerpo = button.getAttribute('data-cuerpo');
                const id = button.getAttribute('data-id');
                const estado = button.getAttribute('data-estado');
                const fecha = button.getAttribute('data-fecha');
                
                currentContactoId = id;
                currentContactoEstado = estado;
                
                // Actualizar contenido del modal
                document.getElementById('modal-asunto').textContent = asunto;
                document.getElementById('modal-cuerpo').textContent = cuerpo;
                document.getElementById('modal-fecha').textContent = fecha;
                
                // Actualizar estado
                const estadoElement = document.getElementById('modal-estado');
                const cerrarBtn = document.getElementById('btn-cerrar-contacto');
                
                if (estado == 0) {
                    estadoElement.innerHTML = '<span class="badge bg-warning">Pendiente</span>';
                    cerrarBtn.style.display = 'inline-block';
                } else {
                    estadoElement.innerHTML = '<span class="badge bg-success">Resuelto</span>';
                    cerrarBtn.style.display = 'none';
                }
            });
            
            // Botón para cerrar contacto desde el modal
            document.getElementById('btn-cerrar-contacto').addEventListener('click', function() {
                if (currentContactoId && confirm('¿Marcar esta solicitud como resuelta?')) {
                    cerrarContacto(currentContactoId);
                }
            });
            
            // Script para cerrar contacto desde la tabla (botones pequeños)
            document.querySelectorAll('.cerrar-contacto-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    if (confirm('¿Marcar esta solicitud como resuelta?')) {
                        cerrarContacto(id);
                    }
                });
            });
            
            // Función para cerrar contacto (llamada AJAX)
            function cerrarContacto(id) {
                fetch('../controllers/cerrar_contacto.php?id=' + id)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Cerrar modal si está abierto
                            const modal = bootstrap.Modal.getInstance(verContactoModal);
                            if (modal) modal.hide();
                            
                            // Mostrar mensaje de éxito
                            alert('Solicitud marcada como resuelta correctamente.');
                            
                            // Recargar la página después de 500ms
                            setTimeout(() => {
                                location.reload();
                            }, 500);
                        } else {
                            alert('Error al cerrar la solicitud');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error de conexión');
                    });
            }
        });
    </script>
</body>
</html>