<?php
session_start();
require_once '../Model/AprobarPromocion.php';

$mensaje = '';
$tipoMensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $idPromocion = $_POST['id_promocion'] ?? 0;
    
  
    $promocion =  GetPromocionesID($idPromocion);
    
    
    if (!$promocion) {
        $mensaje = 'Promoción no encontrada.';
        $tipoMensaje = 'danger';
    } else {
        switch ($action) {
            case 'aprobar':
                if (actualizarEstadoPromocion($idPromocion, '1')) {
                    $emailEnviado = enviarEmailAprobacionPromocion($promocion, $promocion['comerciante_email']);
                    
                    $mensaje = 'Promoción aprobada exitosamente. ';
                    $mensaje .= $emailEnviado ? 'Se envió notificación al comerciante.' : 'Error al enviar email, pero la promoción fue aprobada.';
                    $tipoMensaje = 'success';
                } else {
                    $mensaje = 'Error al aprobar la promoción.';
                    $tipoMensaje = 'danger';
                }
                break;
                
            case 'rechazar':
                if (actualizarEstadoPromocion($idPromocion, '2')) {
                    $emailEnviado = enviarEmailRechazoPromocion($promocion, $promocion['comerciante_email']);
                    
                    $mensaje = 'Promoción rechazada exitosamente. Se envió notificación al comerciante.';
                    $tipoMensaje = 'success';
                } else {
                    $mensaje = 'Error al rechazar la promoción.';
                    $tipoMensaje = 'danger';
                }
                break;
        }
    }
    
    header('Location: AprobarPromociones.php?mensaje=' . urlencode($mensaje) . '&tipo=' . $tipoMensaje);
    exit;
}

if (isset($_GET['mensaje'])) {
    $mensaje = $_GET['mensaje'];
    $tipoMensaje = $_GET['tipo'] ?? 'info';
}

$promocionesPendientes = getPromocionesPendientes();
$promocionesAprobadas = getPromocionesAprobadas();
$promocionesRechazadas = getPromocionesRechazadas();
$estadisticas = getEstadisticasPromociones();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aprobar Promociones - ShoppingGenerico</title>
    
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

        .table-hover tbody tr:hover {
            background-color: rgba(var(--primary-rgb), 0.04);
        }

        .stats-card {
            background: white;
            border-radius: 10px;
            border-left: 4px solid var(--primary);
            transition: transform 0.2s ease;
        }

        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .badge-estado {
            font-size: 0.75rem;
            padding: 0.35rem 0.65rem;
        }

        .estado-pendiente { background-color: var(--warning); color: #000; }
        .estado-aprobado { background-color: var(--success); color: #fff; }
        .estado-rechazado { background-color: var(--danger); color: #fff; }
        .estado-activo { background-color: var(--info); color: #fff; }

        .promocion-card {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            transition: all 0.3s ease;
            background: white;
        }

        .promocion-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .promocion-card.pendiente {
            border-left: 4px solid var(--warning);
        }

        .promocion-card.aprobada {
            border-left: 4px solid var(--success);
        }

        .promocion-card.rechazada {
            border-left: 4px solid var(--danger);
        }

        .nav-tabs .nav-link.active {
            color: var(--primary);
            font-weight: 600;
            border-bottom: 3px solid var(--primary);
        }

        .badge-categoria {
            font-size: 0.7rem;
            padding: 0.3rem 0.5rem;
        }

        .categoria-inicial { background-color: #6f42c1; color: white; }
        .categoria-medium { background-color: #fd7e14; color: white; }
        .categoria-premium { background-color: #20c997; color: white; }

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
                <strong>ShoppingUTN - Administrador</strong>
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
                            <a class="nav-link" href="./DashboardAdministrador.php">
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
                            <a class="nav-link active" href="./AprobarPromociones.php">
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
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="h3 mb-1">
                                <i class="bi bi-tag me-2" style="color: var(--primary);"></i>
                                Aprobar Promociones
                            </h1>
                            <p class="text-muted mb-0">Revisa y gestiona las promociones creadas por los comerciantes</p>
                        </div>
                    </div>

                    <!-- Alertas -->
                    <?php if ($mensaje): ?>
                        <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($mensaje); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="stats-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Pendientes</h6>
                                        <div class="h4 mb-0 text-warning"><?php echo $estadisticas['pendientes']; ?></div>
                                    </div>
                                    <i class="bi bi-clock-history" style="font-size: 1.5rem; color: var(--warning);"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stats-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Aprobadas</h6>
                                        <div class="h4 mb-0 text-success"><?php echo $estadisticas['aprobadas_total']; ?></div>
                                    </div>
                                    <i class="bi bi-check-circle" style="font-size: 1.5rem; color: var(--success);"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stats-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Activas</h6>
                                        <div class="h4 mb-0 text-info"><?php echo $estadisticas['activas']; ?></div>
                                    </div>
                                    <i class="bi bi-lightning-charge" style="font-size: 1.5rem; color: var(--info);"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stats-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Tasa Aprobación</h6>
                                        <div class="h4 mb-0 text-primary"><?php echo $estadisticas['tasa_aprobacion']; ?>%</div>
                                    </div>
                                    <i class="bi bi-percent" style="font-size: 1.5rem; color: var(--primary);"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <ul class="nav nav-tabs mb-4" id="promocionesTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pendientes-tab" data-bs-toggle="tab" data-bs-target="#pendientes" type="button" role="tab">
                                Pendientes
                                <span class="badge bg-warning ms-1"><?php echo count($promocionesPendientes); ?></span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="aprobadas-tab" data-bs-toggle="tab" data-bs-target="#aprobadas" type="button" role="tab">
                                Aprobadas
                                <span class="badge bg-success ms-1"><?php echo count($promocionesAprobadas); ?></span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="rechazadas-tab" data-bs-toggle="tab" data-bs-target="#rechazadas" type="button" role="tab">
                                Rechazadas
                                <span class="badge bg-danger ms-1"><?php echo count($promocionesRechazadas); ?></span>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="promocionesTabContent">
                        <!-- Pestaña Pendientes -->
                        <div class="tab-pane fade show active" id="pendientes" role="tabpanel">
                            <?php if (empty($promocionesPendientes)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-check-circle" style="font-size: 3rem; color: var(--success);"></i>
                                    <h5 class="mt-3 text-muted">No hay promociones pendientes</h5>
                                    <p class="text-muted">Todas las promociones han sido revisadas.</p>
                                </div>
                            <?php else: ?>
                                <div class="row g-3">
                                    <?php foreach ($promocionesPendientes as $promocion): ?>
                                        <div class="col-12 col-lg-6">
                                            <div class="promocion-card pendiente p-4">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <h6 class="mb-0">Promoción #<?php echo $promocion['IDpromocion']; ?></h6>
                                                    <span class="badge estado-pendiente">Pendiente</span>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <h6 class="text-primary">
                                                        <i class="bi bi-shop me-1"></i>
                                                        Información del Local
                                                    </h6>
                                                    <div class="row small text-muted">
                                                        <div class="col-6">
                                                            <strong>Local:</strong><br>
                                                            <?php echo htmlspecialchars($promocion['local_nombre']); ?>
                                                        </div>
                                                        <div class="col-6">
                                                            <strong>Rubro:</strong><br>
                                                            <?php echo htmlspecialchars($promocion['local_rubro']); ?>
                                                        </div>
                                                        <div class="col-6 mt-2">
                                                            <strong>Comerciante:</strong><br>
                                                            <?php echo htmlspecialchars($promocion['comerciante_nombre']); ?>
                                                        </div>
                                                        <div class="col-6 mt-2">
                                                            <strong>Ubicación:</strong><br>
                                                            <?php echo htmlspecialchars($promocion['ubicacion_nombre']); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <h6 class="text-primary">
                                                        <i class="bi bi-tag me-1"></i>
                                                        Detalles de la Promoción
                                                    </h6>
                                                    <div class="row small text-muted">
                                                        <div class="col-12 mb-2">
                                                            <strong>Descripción:</strong><br>
                                                            <?php echo htmlspecialchars($promocion['descripcion']); ?>
                                                        </div>
                                                        <div class="col-6">
                                                            <strong>Válida desde:</strong><br>
                                                            <?php echo date('d/m/Y', strtotime($promocion['desde'])); ?>
                                                        </div>
                                                        <div class="col-6">
                                                            <strong>Válida hasta:</strong><br>
                                                            <?php echo date('d/m/Y', strtotime($promocion['hasta'])); ?>
                                                        </div>
                                                        <div class="col-6 mt-2">
                                                            <strong>Día de aplicación:</strong><br>
                                                            <?php echo getNombreDia($promocion['dia']); ?>
                                                        </div>
                                                        <div class="col-6 mt-2">
                                                            <strong>Categoría:</strong><br>
                                                            <?php 
                                                            $categoriaClass = 'categoria-' . strtolower($promocion['categoriaHabilitada']);
                                                            ?>
                                                            <span class="badge badge-categoria <?php echo $categoriaClass; ?>">
                                                                <?php echo htmlspecialchars($promocion['categoriaHabilitada']); ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="d-flex gap-2">
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="aprobar">
                                                        <input type="hidden" name="id_promocion" value="<?php echo $promocion['IDpromocion']; ?>">
                                                        <button type="submit" class="btn btn-success btn-sm" 
                                                                onclick="return confirm('¿Estás seguro de que deseas APROBAR esta promoción?')">
                                                            <i class="bi bi-check-lg me-1"></i>Aprobar
                                                        </button>
                                                    </form>
                                                    
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="rechazar">
                                                        <input type="hidden" name="id_promocion" value="<?php echo $promocion['IDpromocion']; ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm"
                                                                onclick="return confirm('¿Estás seguro de que deseas RECHAZAR esta promoción?')">
                                                            <i class="bi bi-x-lg me-1"></i>Rechazar
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Pestaña Aprobadas -->
                        <div class="tab-pane fade" id="aprobadas" role="tabpanel">
                            <?php if (empty($promocionesAprobadas)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-inbox" style="font-size: 3rem; color: var(--muted);"></i>
                                    <h5 class="mt-3 text-muted">No hay promociones aprobadas</h5>
                                    <p class="text-muted">Las promociones aprobadas aparecerán aquí.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Local</th>
                                                <th>Descripción</th>
                                                <th>Período</th>
                                                <th>Día</th>
                                                <th>Categoría</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($promocionesAprobadas as $promocion): ?>
                                                <tr>
                                                    <td>
                                                        <div class="fw-medium"><?php echo htmlspecialchars($promocion['local_nombre']); ?></div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($promocion['local_rubro']); ?></small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($promocion['descripcion']); ?></td>
                                                    <td>
                                                        <small>
                                                            <?php echo date('d/m/Y', strtotime($promocion['desde'])); ?><br>
                                                            al <?php echo date('d/m/Y', strtotime($promocion['hasta'])); ?>
                                                        </small>
                                                    </td>
                                                    <td><?php echo getNombreDia($promocion['dia']); ?></td>
                                                    <td>
                                                        <?php 
                                                        $categoriaClass = 'categoria-' . strtolower($promocion['categoriaHabilitada']);
                                                        ?>
                                                        <span class="badge badge-categoria <?php echo $categoriaClass; ?>">
                                                            <?php echo htmlspecialchars($promocion['categoriaHabilitada']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        $hoy = new DateTime();
                                                        $hasta = new DateTime($promocion['hasta']);
                                                        $estado = ($hasta >= $hoy) ? 'activo' : 'expirado';
                                                        $badgeClass = ($estado == 'activo') ? 'estado-activo' : 'estado-pendiente';
                                                        ?>
                                                        <span class="badge <?php echo $badgeClass; ?>">
                                                            <?php echo ucfirst($estado); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Pestaña Rechazadas -->
                        <div class="tab-pane fade" id="rechazadas" role="tabpanel">
                            <?php if (empty($promocionesRechazadas)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-inbox" style="font-size: 3rem; color: var(--muted);"></i>
                                    <h5 class="mt-3 text-muted">No hay promociones rechazadas</h5>
                                    <p class="text-muted">Las promociones rechazadas aparecerán aquí.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Local</th>
                                                <th>Descripción</th>
                                                <th>Período Solicitado</th>
                                                <th>Comerciante</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($promocionesRechazadas as $promocion): ?>
                                                <tr>
                                                    <td>
                                                        <div class="fw-medium"><?php echo htmlspecialchars($promocion['local_nombre']); ?></div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($promocion['local_rubro']); ?></small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($promocion['descripcion']); ?></td>
                                                    <td>
                                                        <small>
                                                            <?php echo date('d/m/Y', strtotime($promocion['desde'])); ?><br>
                                                            al <?php echo date('d/m/Y', strtotime($promocion['hasta'])); ?>
                                                        </small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($promocion['comerciante_nombre']); ?></td>
                                                    <td>
                                                        <span class="badge estado-rechazado">Rechazada</span>
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
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Actualizar automáticamente cada 30 segundos para ver nuevas promociones
        setTimeout(() => {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>