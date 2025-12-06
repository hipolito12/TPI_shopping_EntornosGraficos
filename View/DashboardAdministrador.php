<?php
session_start();
require_once '../Model/DashBoardAdmin.php';

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['IDusuario']) || $_SESSION['Rol'] !='Administrador') {
  session_unset();
    header("Location: ../index.php");
    exit;
}

if (isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0') {
}

$reporteUsos = getReporteUsos($filtrosReporte);
$estadisticas = getEstadisticasGenerales();
$locales = getLocales();
$solicitudesPendientes = getSolicitudesPendientes();
$promocionesPendientes = getPromocionesPendientes();
$novedadesActivas = getNovedadesActivas();
$todosLocales = getAllLocales();

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

        @media (max-width: 767px) {
            .sidebar {
                min-height: auto;
                border-right: none;
                border-bottom: 1px solid #eee;
            }
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
                        <li><a class="dropdown-item" href="./DashboardAdministrador.php">Inicio</a></li>
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
                                    <a href="./ValidarCuentas.php" class="text-decoration-none">
                                        <div class="quick-action-card p-4 text-center">
                                            <i class="bi bi-person-check" style="font-size: 2rem; color: var(--success);"></i>
                                            <h6 class="mt-2 mb-1">Validar Cuentas</h6>
                                            <p class="text-muted small mb-0">Revisar solicitudes de dueños</p>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="./AprobarSolicitudes.php" class="text-decoration-none">
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
                                    <a href="./GestionLocales.php" class="btn btn-sm btn-primary">Ver Todos</a>
                                </div>
                                <div class="card-body">
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
                                                        <th>Dueño</th>
                                                        <th>Ubicación</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach (array_slice($locales, 0, 5) as $local): ?>
                                                        <tr>
                                                            <td>
                                                                <div class="fw-medium"><?php echo htmlspecialchars($local['nombre']); ?></div>
                                                                <small class="text-muted"><?php echo htmlspecialchars($local['rubro']); ?></small>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($local['dueño'] ?? 'N/A'); ?></td>
                                                            <td><?php echo htmlspecialchars($local['ubicacion_nombre'] ?? 'N/A'); ?></td>
                                                            <td>
                                                                <div class="btn-group btn-group-sm">
                                                                    <button class="btn btn-outline-primary action-btn" title="Editar">
                                                                        <i class="bi bi-pencil"></i>
                                                                    </button>
                                                                    <button class="btn btn-outline-danger action-btn" title="Eliminar">
                                                                        <i class="bi bi-trash"></i>
                                                                    </button>
                                                                </div>
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

                        <!-- Solicitudes Pendientes -->
                        <div class="col-12 col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Solicitudes Pendientes</h5>
                                    <span class="badge bg-warning"><?php echo count($solicitudesPendientes); ?></span>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($solicitudesPendientes)): ?>
                                        <div class="text-center py-3">
                                            <i class="bi bi-check-circle" style="font-size: 2rem; color: var(--success);"></i>
                                            <p class="text-muted mt-2 mb-0">No hay solicitudes pendientes</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach (array_slice($solicitudesPendientes, 0, 5) as $solicitud): ?>
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($solicitud['nombre']); ?></h6>
                                                        <p class="mb-1 small text-muted"><?php echo htmlspecialchars($solicitud['email']); ?></p>
                                                        <small class="text-muted">Local: <?php echo htmlspecialchars($solicitud['nombreLocal']); ?></small>
                                                    </div>
                                                    <div class="btn-group">
                                                        <button class="btn btn-sm btn-success" title="Aprobar">
                                                            <i class="bi bi-check-lg"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger" title="Rechazar">
                                                            <i class="bi bi-x-lg"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
    </script>
</body>
</html>