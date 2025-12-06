<?php
session_start();
require_once '../Model/Reportes.php';
$estadisticasGenerales = getEstadisticasGenerales();
$usuariosPorCategoria = getUsuariosPorCategoria();
$usuariosPorRol = getUsuariosPorRol();
$localesPorRubro = getLocalesPorRubro();
$promocionesPorEstado = getPromocionesPorEstado();
$usoPromociones = getUsoPromociones();
$topLocales = getTopLocales();
$solicitudesPorEstado = getSolicitudesPorEstado();
$crecimientoUsuarios = getCrecimientoUsuarios();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - ShoppingGenerico</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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

        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: 100%;
        }

        .chart-title {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 1rem;
            border-bottom: 2px solid var(--subtle);
            padding-bottom: 0.5rem;
        }

        .report-section {
            margin-bottom: 2rem;
        }

        .badge-estado {
            font-size: 0.75rem;
            padding: 0.35rem 0.65rem;
        }

        @media (max-width: 767px) {
            .sidebar {
                min-height: auto;
                border-right: none;
                border-bottom: 1px solid #eee;
            }
            
            .chart-container {
                padding: 1rem;
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
                            <a class="nav-link  active" href="./Reportes.php">
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
                                <i class="bi bi-graph-up me-2" style="color: var(--primary);"></i>
                                Reportes y Estadísticas
                            </h1>
                            <p class="text-muted mb-0">Análisis y métricas del sistema ShoppingGenerico</p>
                        </div>
                        <div>
                            <button class="btn btn-outline-primary" onclick="window.print()">
                                <i class="bi bi-printer me-1"></i>Imprimir Reporte
                            </button>
                        </div>
                    </div>

                    <!-- Estadísticas Rápidas -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="stats-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Total Locales</h6>
                                        <div class="h4 mb-0 text-primary"><?php echo $estadisticasGenerales['total_locales']; ?></div>
                                    </div>
                                    <i class="bi bi-shop" style="font-size: 1.5rem; color: var(--primary);"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stats-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Usuarios Registrados</h6>
                                        <div class="h4 mb-0 text-success"><?php echo $estadisticasGenerales['total_usuarios']; ?></div>
                                    </div>
                                    <i class="bi bi-people" style="font-size: 1.5rem; color: var(--success);"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stats-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Promociones Activas</h6>
                                        <div class="h4 mb-0 text-info"><?php echo $estadisticasGenerales['promociones_activas']; ?></div>
                                    </div>
                                    <i class="bi bi-tag" style="font-size: 1.5rem; color: var(--info);"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stats-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Novedades Activas</h6>
                                        <div class="h4 mb-0 text-warning"><?php echo $estadisticasGenerales['novedades_activas']; ?></div>
                                    </div>
                                    <i class="bi bi-megaphone" style="font-size: 1.5rem; color: var(--warning);"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row report-section">
                        <div class="col-12 col-md-6">
                            <div class="chart-container">
                                <h5 class="chart-title">
                                    <i class="bi bi-pie-chart me-2"></i>Usuarios por Categoría
                                </h5>
                                <canvas id="chartUsuariosCategoria" height="250"></canvas>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="chart-container">
                                <h5 class="chart-title">
                                    <i class="bi bi-bar-chart me-2"></i>Usuarios por Rol
                                </h5>
                                <canvas id="chartUsuariosRol" height="250"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="row report-section">
                        <div class="col-12 col-md-6">
                            <div class="chart-container">
                                <h5 class="chart-title">
                                    <i class="bi bi-shop-window me-2"></i>Locales por Rubro
                                </h5>
                                <canvas id="chartLocalesRubro" height="250"></canvas>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="chart-container">
                                <h5 class="chart-title">
                                    <i class="bi bi-tags me-2"></i>Estado de Promociones
                                </h5>
                                <canvas id="chartPromocionesEstado" height="250"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="row report-section">
                        <div class="col-12 col-md-8">
                            <div class="chart-container">
                                <h5 class="chart-title">
                                    <i class="bi bi-graph-up-arrow me-2"></i>Uso de Promociones - Últimos 6 Meses
                                </h5>
                                <canvas id="chartUsoPromociones" height="200"></canvas>
                            </div>
                        </div>

                        <!-- Estado de Solicitudes -->
                        <div class="col-12 col-md-4">
                            <div class="chart-container">
                                <h5 class="chart-title">
                                    <i class="bi bi-clipboard-data me-2"></i>Estado de Solicitudes
                                </h5>
                                <canvas id="chartSolicitudesEstado" height="200"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Tablas de Datos -->
                    <div class="row report-section">
                        <!-- Top 10 Locales más Populares -->
                        <div class="col-12 col-lg-8">
                            <div class="chart-container">
                                <h5 class="chart-title">
                                    <i class="bi bi-trophy me-2"></i>Top 10 Locales Más Populares
                                </h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Local</th>
                                                <th>Rubro</th>
                                                <th>Usos de Promociones</th>
                                                <th>Rendimiento</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($topLocales as $index => $local): ?>
                                                <tr>
                                                    <td><?php echo $index + 1; ?></td>
                                                    <td class="fw-medium"><?php echo htmlspecialchars($local['local_nombre']); ?></td>
                                                    <td><?php echo htmlspecialchars($local['rubro']); ?></td>
                                                    <td>
                                                        <span class="badge bg-primary"><?php echo $local['usos']; ?> usos</span>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        $maxUsos = max(array_column($topLocales, 'usos'));
                                                        $porcentaje = $maxUsos > 0 ? ($local['usos'] / $maxUsos) * 100 : 0;
                                                        ?>
                                                        <div class="progress" style="height: 8px;">
                                                            <div class="progress-bar" role="progressbar" 
                                                                 style="width: <?php echo $porcentaje; ?>%;" 
                                                                 aria-valuenow="<?php echo $porcentaje; ?>" 
                                                                 aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div>
                                                        <small class="text-muted"><?php echo round($porcentaje); ?>%</small>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Crecimiento de Usuarios -->
                        <div class="col-12 col-lg-4">
                            <div class="chart-container">
                                <h5 class="chart-title">
                                    <i class="bi bi-people-fill me-2"></i>Crecimiento de Usuarios 2025
                                </h5>
                                <canvas id="chartCrecimientoUsuarios" height="300"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Resumen Ejecutivo -->
                    <div class="row report-section">
                        <div class="col-12">
                            <div class="chart-container">
                                <h5 class="chart-title">
                                    <i class="bi bi-file-text me-2"></i>Resumen Ejecutivo
                                </h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Puntos Destacados:</h6>
                                        <ul>
                                            <li>El sistema cuenta con <strong><?php echo $estadisticasGenerales['total_usuarios']; ?> usuarios</strong> registrados</li>
                                            <li><strong><?php echo $estadisticasGenerales['total_locales']; ?> locales</strong> operando en el shopping</li>
                                            <li><strong><?php echo $estadisticasGenerales['promociones_activas']; ?> promociones activas</strong> disponibles</li>
                                            <li>Distribución balanceada entre categorías de usuarios</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Recomendaciones:</h6>
                                        <ul>
                                            <li>Incentivar la creación de más promociones en rubros con menor representación</li>
                                            <li>Fomentar el registro de nuevos usuarios con campañas específicas</li>
                                            <li>Revisar locales con menor uso de promociones para ofrecer soporte</li>
                                            <li>Mantener actualizadas las novedades para engagement con usuarios</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="mt-3 p-3 bg-light rounded">
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Reporte generado el <?php echo date('d/m/Y H:i:s'); ?> | 
                                        ShoppingGenerico - Sistema de Gestión Comercial
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        /*Cargar todos los graficos de la pagina*/
        const colors = {
            primary: '#4A3BC7',
            success: '#28a745',
            warning: '#ffc107',
            danger: '#dc3545',
            info: '#17a2b8',
            secondary: '#6c757d',
            light: '#f8f9fa',
            dark: '#343a40'
        };

        const usuariosCategoriaLabels = [<?php 
            $labels = [];
            foreach ($usuariosPorCategoria as $item) {
                $labels[] = "'" . addslashes($item['categoria']) . "'";
            }
            echo implode(',', $labels);
        ?>];
        
        const usuariosCategoriaData = [<?php 
            $data = [];
            foreach ($usuariosPorCategoria as $item) {
                $data[] = $item['cantidad'];
            }
            echo implode(',', $data);
        ?>];

        const usuariosRolLabels = [<?php 
            $labels = [];
            foreach ($usuariosPorRol as $item) {
                $labels[] = "'" . addslashes($item['rol']) . "'";
            }
            echo implode(',', $labels);
        ?>];
        
        const usuariosRolData = [<?php 
            $data = [];
            foreach ($usuariosPorRol as $item) {
                $data[] = $item['cantidad'];
            }
            echo implode(',', $data);
        ?>];

        const localesRubroLabels = [<?php 
            $labels = [];
            foreach ($localesPorRubro as $item) {
                $labels[] = "'" . addslashes($item['rubro']) . "'";
            }
            echo implode(',', $labels);
        ?>];
        
        const localesRubroData = [<?php 
            $data = [];
            foreach ($localesPorRubro as $item) {
                $data[] = $item['cantidad'];
            }
            echo implode(',', $data);
        ?>];

        const promocionesEstadoLabels = [<?php 
            $labels = [];
            foreach ($promocionesPorEstado as $item) {
                $labels[] = "'" . addslashes($item['estado']) . "'";
            }
            echo implode(',', $labels);
        ?>];
        
        const promocionesEstadoData = [<?php 
            $data = [];
            foreach ($promocionesPorEstado as $item) {
                $data[] = $item['cantidad'];
            }
            echo implode(',', $data);
        ?>];

        const usoPromocionesLabels = [<?php 
            $labels = [];
            $meses = ['01'=>'Ene','02'=>'Feb','03'=>'Mar','04'=>'Abr','05'=>'May','06'=>'Jun','07'=>'Jul','08'=>'Ago','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dic'];
            foreach ($usoPromociones as $item) {
                list($anio, $mes) = explode('-', $item['mes']);
                $mesNombre = isset($meses[$mes]) ? $meses[$mes] : '';
                $labels[] = "'" . $mesNombre . ' ' . $anio . "'";
            }
            echo implode(',', $labels);
        ?>];
        
        const usoPromocionesData = [<?php 
            $data = [];
            foreach ($usoPromociones as $item) {
                $data[] = $item['cantidad'];
            }
            echo implode(',', $data);
        ?>];

        const solicitudesEstadoLabels = [<?php 
            $labels = [];
            foreach ($solicitudesPorEstado as $item) {
                $labels[] = "'" . addslashes($item['estado']) . "'";
            }
            echo implode(',', $labels);
        ?>];
        
        const solicitudesEstadoData = [<?php 
            $data = [];
            foreach ($solicitudesPorEstado as $item) {
                $data[] = $item['cantidad'];
            }
            echo implode(',', $data);
        ?>];
        const crecimientoUsuariosLabels = [<?php 
            $labels = [];
            $meses = ['01'=>'Ene','02'=>'Feb','03'=>'Mar','04'=>'Abr','05'=>'May','06'=>'Jun','07'=>'Jul','08'=>'Ago','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dic'];
            foreach ($crecimientoUsuarios as $item) {
                list($anio, $mes) = explode('-', $item['mes']);
                $mesNombre = isset($meses[$mes]) ? $meses[$mes] : '';
                $labels[] = "'" . $mesNombre . "'";
            }
            echo implode(',', $labels);
        ?>];
        
        const crecimientoUsuariosData = [<?php 
            $data = [];
            foreach ($crecimientoUsuarios as $item) {
                $data[] = $item['cantidad'];
            }
            echo implode(',', $data);
        ?>];

        function getNombreMes(numeroMes) {
            const meses = [
                'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun',
                'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'
            ];
            return meses[parseInt(numeroMes) - 1] || '';
        }

        const chartUsuariosCategoria = new Chart(
            document.getElementById('chartUsuariosCategoria'),
            {
                type: 'doughnut',
                data: {
                    labels: usuariosCategoriaLabels,
                    datasets: [{
                        data: usuariosCategoriaData,
                        backgroundColor: [
                            colors.primary,
                            colors.success,
                            colors.warning,
                            colors.info,
                            colors.secondary
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            }
        );

        const chartUsuariosRol = new Chart(
            document.getElementById('chartUsuariosRol'),
            {
                type: 'bar',
                data: {
                    labels: usuariosRolLabels,
                    datasets: [{
                        label: 'Cantidad de Usuarios',
                        data: usuariosRolData,
                        backgroundColor: colors.primary,
                        borderColor: colors.primary,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            }
        );

        const chartLocalesRubro = new Chart(
            document.getElementById('chartLocalesRubro'),
            {
                type: 'pie',
                data: {
                    labels: localesRubroLabels,
                    datasets: [{
                        data: localesRubroData,
                        backgroundColor: [
                            colors.primary,
                            colors.success,
                            colors.warning,
                            colors.info,
                            colors.secondary,
                            colors.danger
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            }
        );

        const chartPromocionesEstado = new Chart(
            document.getElementById('chartPromocionesEstado'),
            {
                type: 'polarArea',
                data: {
                    labels: promocionesEstadoLabels,
                    datasets: [{
                        data: promocionesEstadoData,
                        backgroundColor: [
                            colors.warning,
                            colors.success,
                            colors.secondary,
                            colors.danger
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            }
        );

        // Gráfico de Uso de Promociones
        const chartUsoPromociones = new Chart(
            document.getElementById('chartUsoPromociones'),
            {
                type: 'line',
                data: {
                    labels: usoPromocionesLabels,
                    datasets: [{
                        label: 'Usos de Promociones',
                        data: usoPromocionesData,
                        backgroundColor: colors.primary + '20',
                        borderColor: colors.primary,
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            }
        );

        const chartSolicitudesEstado = new Chart(
            document.getElementById('chartSolicitudesEstado'),
            {
                type: 'doughnut',
                data: {
                    labels: solicitudesEstadoLabels,
                    datasets: [{
                        data: solicitudesEstadoData,
                        backgroundColor: [
                            colors.warning,
                            colors.success,
                            colors.danger
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            }
        );

        const chartCrecimientoUsuarios = new Chart(
            document.getElementById('chartCrecimientoUsuarios'),
            {
                type: 'line',
                data: {
                    labels: crecimientoUsuariosLabels,
                    datasets: [{
                        label: 'Total de Usuarios',
                        data: crecimientoUsuariosData,
                        backgroundColor: colors.success + '20',
                        borderColor: colors.success,
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            }
        );

        // Auto-refresh cada 5 minutos para datos en tiempo real
        setTimeout(() => {
            window.location.reload();
        }, 300000);
    </script>
</body>
</html>