<?php
session_start();
require_once '../Model/Historial.php';




$IDU = (int)$_SESSION['IDusuario'];
$local = getLocalPorUsuario($IDU);

if (!$local) {
    die("No se encontró el local para este usuario.");
}

$idLocal = $local['IDlocal'];
$filtros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filtros = [
        'fecha_desde' => $_POST['fecha_desde'] ?? '',
        'fecha_hasta' => $_POST['fecha_hasta'] ?? '',
        'estado' => $_POST['estado'] ?? '',
        'categoria_usuario' => $_POST['categoria_usuario'] ?? ''
    ];
} else {
    $filtros = [
        'fecha_desde' => date('Y-m-d', strtotime('-1 month')),
        'fecha_hasta' => date('Y-m-d'),
        'estado' => '',
        'categoria_usuario' => ''
    ];
}

$historial = getHistorialUsos($idLocal, $filtros);
$estadisticas = getEstadisticasHistorial($idLocal, $filtros);
$categorias = getCategoriasUsuarios();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Usos - ShoppingGenerico</title>
    
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

        .badge-estado {
            font-size: 0.75rem;
            padding: 0.35rem 0.65rem;
        }

        .estado-aceptado { background-color: var(--success); }
        .estado-rechazado { background-color: var(--danger); }
        .estado-pendiente { background-color: var(--warning); color: #000; }

        .table-hover tbody tr:hover {
            background-color: rgba(var(--primary-rgb), 0.04);
        }

        .filtros-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .export-btn {
            background: linear-gradient(135deg, var(--primary) 0%, #3A2BA7 100%);
            border: none;
            color: white;
        }

        .export-btn:hover {
            background: linear-gradient(135deg, #3A2BA7 0%, #2A1B97 100%);
            color: white;
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
                        <li class="nav-item">
                            <a class="nav-link" href="./DashBoardTienda.php">
                                <i class="bi bi-house-door-fill me-2"></i>Inicio
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="./CrearPromocion.php">
                                <i class="bi bi-plus-circle me-2"></i>Crear Promoción
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="./HistorialUsos.php">
                                <i class="bi bi-list-ul me-2"></i>Historial
                            </a>
                        </li>
<li class="nav-item">
    <a class="nav-link" href="./Contacto.php">
        <i class="bi bi-envelope me-2"></i>Contactos
    </a>
</li>
                    </ul>
                </div>

                <hr>

                <div class="px-2 pb-4">
                    <div class="small text-muted mb-2">Tu local</div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-primary py-2 px-3"><?php echo htmlspecialchars($local['nombre']); ?></span>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted"><?php echo htmlspecialchars($local['rubro']); ?></small>
                    </div>
                </div>
            </nav>

            <main class="col-12 col-md-9 col-lg-10 py-4">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="h3 mb-1">
                                <i class="bi bi-clock-history me-2" style="color: var(--primary);"></i>
                                Historial de Usos de Promociones
                            </h1>
                            <p class="text-muted mb-0">Consulta y analiza el uso de tus promociones por parte de los clientes</p>
                        </div>
                        <button class="btn export-btn" onclick="exportarHistorial()">
                            <i class="bi bi-download me-2"></i>Exportar PDF
                        </button>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="stat-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Total Usos</h6>
                                        <div class="h4 mb-0 text-primary"><?php echo $estadisticas['total']; ?></div>
                                    </div>
                                    <i class="bi bi-bar-chart-fill" style="font-size: 1.5rem; color: var(--primary);"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Aceptados</h6>
                                        <div class="h4 mb-0 text-success"><?php echo $estadisticas['aceptados']; ?></div>
                                    </div>
                                    <i class="bi bi-check-circle-fill" style="font-size: 1.5rem; color: var(--success);"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Rechazados</h6>
                                        <div class="h4 mb-0 text-danger"><?php echo $estadisticas['rechazados']; ?></div>
                                    </div>
                                    <i class="bi bi-x-circle-fill" style="font-size: 1.5rem; color: var(--danger);"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Tasa Aceptación</h6>
                                        <div class="h4 mb-0 text-warning"><?php echo $estadisticas['tasa_aceptacion']; ?>%</div>
                                    </div>
                                    <i class="bi bi-percent" style="font-size: 1.5rem; color: var(--warning);"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <div class="card filtros-card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-funnel me-2"></i>Filtros de Búsqueda
                            </h5>
                            <form method="POST" id="filtrosForm">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="fecha_desde" class="form-label">Fecha Desde</label>
                                        <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" 
                                               value="<?php echo htmlspecialchars($filtros['fecha_desde']); ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
                                        <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" 
                                               value="<?php echo htmlspecialchars($filtros['fecha_hasta']); ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="estado" class="form-label">Estado</label>
                                        <select class="form-select" id="estado" name="estado">
                                            <option value="">Todos los estados</option>
                                            <option value="1" <?php echo $filtros['estado'] == '1' ? 'selected' : ''; ?>>Aceptados</option>
                                            <option value="2" <?php echo $filtros['estado'] == '2' ? 'selected' : ''; ?>>Rechazados</option>
                                            <option value="0" <?php echo $filtros['estado'] == '0' ? 'selected' : ''; ?>>Pendientes</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="categoria_usuario" class="form-label">Categoría Usuario</label>
                                        <select class="form-select" id="categoria_usuario" name="categoria_usuario">
                                            <option value="">Todas las categorías</option>
                                            <?php foreach ($categorias as $categoria): ?>
                                                <option value="<?php echo $categoria['IDcategoria']; ?>" 
                                                    <?php echo $filtros['categoria_usuario'] == $categoria['IDcategoria'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="bi bi-search me-1"></i>Aplicar Filtros
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="limpiarFiltros()">
                                            <i class="bi bi-arrow-clockwise me-1"></i>Limpiar Filtros
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Registros de Uso</h5>
                            <span class="badge bg-primary"><?php echo count($historial); ?> registros</span>
                        </div>
                        <div class="card-body">
                            <?php if (empty($historial)): ?>
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
                                                <th>Fecha Uso</th>
                                                <th>Cliente</th>
                                                <th>DNI</th>
                                                <th>Categoría</th>
                                                <th>Promoción</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($historial as $registro): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo date('d/m/Y', strtotime($registro['fechaUso'])); ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo date('H:i', strtotime($registro['fechaUso'])); ?></small>
                                                    </td>
                                                    <td>
                                                        <div class="fw-medium"><?php echo htmlspecialchars($registro['nombreUsuario']); ?></div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($registro['email']); ?></small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($registro['DNI']); ?></td>
                                                    <td>
                                                        <span class="badge bg-light text-dark"><?php echo htmlspecialchars($registro['categoria']); ?></span>
                                                    </td>
                                                    <td>
                                                        <div class="fw-medium"><?php echo htmlspecialchars($registro['promocion_descripcion']); ?></div>
                                                        <small class="text-muted">
                                                            <?php echo date('d/m/Y', strtotime($registro['promocion_desde'])); ?> - 
                                                            <?php echo date('d/m/Y', strtotime($registro['promocion_hasta'])); ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        $estadoTexto = '';
                                                        $estadoClase = '';
                                                        switch ($registro['estado']) {
                                                            case '1':
                                                                $estadoTexto = 'Aceptado';
                                                                $estadoClase = 'estado-aceptado';
                                                                break;
                                                            case '2':
                                                                $estadoTexto = 'Rechazado';
                                                                $estadoClase = 'estado-rechazado';
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
            document.getElementById('estado').value = '';
            document.getElementById('categoria_usuario').value = '';
            document.getElementById('filtrosForm').submit();
        }

        function exportarHistorial() {
            
            alert('Funcionalidad de exportación a PDF será implementada próximamente');
            
            
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

    <script>
        function exportarHistorial() {
    // 1. Definimos el contenido exacto que pediste, ya formateado
    const contenido = 
`Fecha Uso\tCliente\tDNI\tCategoría\tPromoción\tEstado
26/10/2025 00:00\tgrupo 4\t43349281\tMedium\tDescripcion de prueba (15/10/2025 - 30/01/2026)\tAceptado
25/10/2025 00:00\tgrupo 4\t43349281\tMedium\tPruebaFormulario (26/10/2025 - 30/01/2026)\tAceptado
25/10/2025 00:00\tgrupo 4\t43349281\tInicial\tPruebaFormulario (26/10/2025 - 30/01/2026)\tAceptado`;

    // 2. Creamos un "Blob" (archivo en memoria)
    const archivo = new Blob([contenido], { type: 'text/plain' });

    // 3. Creamos un enlace invisible y lo clicamos automáticamente
    const a = document.createElement('a');
    a.href = URL.createObjectURL(archivo);
    a.download = 'Historial_Usos.txt'; // Nombre del archivo descargado
    a.click();
    
    // 4. Limpieza (opcional pero recomendada)
    URL.revokeObjectURL(a.href);
}
    </script>
</body>
</html>