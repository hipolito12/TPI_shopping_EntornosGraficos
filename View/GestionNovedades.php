<?php
session_start();
require_once '../Model/GestionNovedades.php';



// Procesar acciones
$mensaje = '';
$tipoMensaje = '';
$novedadEditando = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'crear':
            $datos = [
                'desde' => $_POST['desde'],
                'hasta' => $_POST['hasta'],
                'usuarioHabilitado' => $_POST['usuarioHabilitado'],
                'descripcion' => $_POST['descripcion'],
                'cabecera' => $_POST['cabecera'],
                'cuerpo' => $_POST['cuerpo']
            ];
            
            if (crearNovedad($datos)) {
                $mensaje = 'Novedad creada exitosamente.';
                $tipoMensaje = 'success';
            } else {
                $mensaje = 'Error al crear la novedad.';
                $tipoMensaje = 'danger';
            }
            break;
            
        case 'editar':
            $id = $_POST['id_novedad'];
            $datos = [
                'desde' => $_POST['desde'],
                'hasta' => $_POST['hasta'],
                'usuarioHabilitado' => $_POST['usuarioHabilitado'],
                'descripcion' => $_POST['descripcion'],
                'cabecera' => $_POST['cabecera'],
                'cuerpo' => $_POST['cuerpo']
            ];
            
            if (actualizarNovedad($id, $datos)) {
                $mensaje = 'Novedad actualizada exitosamente.';
                $tipoMensaje = 'success';
            } else {
                $mensaje = 'Error al actualizar la novedad.';
                $tipoMensaje = 'danger';
            }
            break;
            
        case 'eliminar':
            $id = $_POST['id_novedad'];
            if (eliminarNovedad($id)) {
                $mensaje = 'Novedad eliminada exitosamente.';
                $tipoMensaje = 'success';
            } else {
                $mensaje = 'Error al eliminar la novedad.';
                $tipoMensaje = 'danger';
            }
            break;
    }
    
    // PRG redirect
    header('Location: GestionNovedades.php?mensaje=' . urlencode($mensaje) . '&tipo=' . $tipoMensaje);
    exit;
}

// Manejar edición (GET)
if (isset($_GET['editar'])) {
    $novedadEditando = getNovedadById($_GET['editar']);
    if (!$novedadEditando) {
        $mensaje = 'Novedad no encontrada.';
        $tipoMensaje = 'danger';
    }
}

// Manejar mensajes de redirección
if (isset($_GET['mensaje'])) {
    $mensaje = $_GET['mensaje'];
    $tipoMensaje = $_GET['tipo'] ?? 'info';
}

$novedades = getNovedades();
$estadisticas = getEstadisticasNovedades();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Novedades - ShoppingGenerico</title>
    
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

        .estado-activa { background-color: var(--success); color: #fff; }
        .estado-expirada { background-color: var(--danger); color: #fff; }
        .estado-proximamente { background-color: var(--info); color: #fff; }

        .novedad-card {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            transition: all 0.3s ease;
            background: white;
        }

        .novedad-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .badge-audiencia {
            font-size: 0.7rem;
            padding: 0.3rem 0.5rem;
        }

        .audiencia-inicial { background-color: #6f42c1; color: white; }
        .audiencia-medium { background-color: #fd7e14; color: white; }
        .audiencia-premium { background-color: #20c997; color: white; }

        .nav-tabs .nav-link.active {
            color: var(--primary);
            font-weight: 600;
            border-bottom: 3px solid var(--primary);
        }

        .form-section {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
                            <a class="nav-link active" href="./GestionNovedades.php">
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

            <main class="col-12 col-md-9 col-lg-10 py-4">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="h3 mb-1">
                                <i class="bi bi-megaphone me-2" style="color: var(--primary);"></i>
                                Gestión de Novedades
                            </h1>
                            <p class="text-muted mb-0">Crea y gestiona las novedades del shopping</p>
                        </div>
                    </div>

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
                                        <h6 class="mb-0">Total Novedades</h6>
                                        <div class="h4 mb-0 text-primary"><?php echo $estadisticas['total']; ?></div>
                                    </div>
                                    <i class="bi bi-megaphone" style="font-size: 1.5rem; color: var(--primary);"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stats-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Activas</h6>
                                        <div class="h4 mb-0 text-success"><?php echo $estadisticas['activas']; ?></div>
                                    </div>
                                    <i class="bi bi-eye" style="font-size: 1.5rem; color: var(--success);"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stats-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Expiradas</h6>
                                        <div class="h4 mb-0 text-danger"><?php echo $estadisticas['expiradas']; ?></div>
                                    </div>
                                    <i class="bi bi-clock-history" style="font-size: 1.5rem; color: var(--danger);"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stats-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Distribución</h6>
                                        <div class="h4 mb-0 text-info"><?php echo count($estadisticas['categorias']); ?> categorías</div>
                                    </div>
                                    <i class="bi bi-pie-chart" style="font-size: 1.5rem; color: var(--info);"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-lg-5">
                            <div class="form-section">
                                <h4 class="mb-4">
                                    <i class="bi bi-<?php echo $novedadEditando ? 'pencil' : 'plus-circle'; ?> me-2"></i>
                                    <?php echo $novedadEditando ? 'Editar Novedad' : 'Crear Nueva Novedad'; ?>
                                </h4>
                                
                                <form method="POST" id="formNovedad">
                                    <?php if ($novedadEditando): ?>
                                        <input type="hidden" name="action" value="editar">
                                        <input type="hidden" name="id_novedad" value="<?php echo $novedadEditando['IDnovedad']; ?>">
                                    <?php else: ?>
                                        <input type="hidden" name="action" value="crear">
                                    <?php endif; ?>
                                    
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label for="cabecera" class="form-label">Título de la Novedad *</label>
                                            <input type="text" class="form-control" id="cabecera" name="cabecera" 
                                                   value="<?php echo $novedadEditando ? htmlspecialchars($novedadEditando['cabecera']) : ''; ?>" 
                                                   required maxlength="3000">
                                        </div>
                                        
                                        <div class="col-12">
                                            <label for="descripcion" class="form-label">Descripción Corta *</label>
                                            <textarea class="form-control" id="descripcion" name="descripcion" 
                                                      rows="2" required maxlength="1000"><?php echo $novedadEditando ? htmlspecialchars($novedadEditando['descripcion']) : ''; ?></textarea>
                                            <div class="form-text">Breve descripción que aparecerá en los listados.</div>
                                        </div>
                                        
                                        <div class="col-12">
                                            <label for="cuerpo" class="form-label">Contenido Completo *</label>
                                            <textarea class="form-control" id="cuerpo" name="cuerpo" 
                                                      rows="5" required maxlength="7000"><?php echo $novedadEditando ? htmlspecialchars($novedadEditando['cuerpo']) : ''; ?></textarea>
                                            <div class="form-text">Contenido detallado de la novedad.</div>
                                        </div>
                                        
                                        <div class="col-12 col-md-6">
                                            <label for="desde" class="form-label">Fecha de Inicio *</label>
                                            <input type="date" class="form-control" id="desde" name="desde" 
                                                   value="<?php echo $novedadEditando ? $novedadEditando['desde'] : ''; ?>" required>
                                        </div>
                                        
                                        <div class="col-12 col-md-6">
                                            <label for="hasta" class="form-label">Fecha de Fin *</label>
                                            <input type="date" class="form-control" id="hasta" name="hasta" 
                                                   value="<?php echo $novedadEditando ? $novedadEditando['hasta'] : ''; ?>" required>
                                        </div>
                                        
                                        <div class="col-12">
                                            <label for="usuarioHabilitado" class="form-label">Audiencia Destinada *</label>
                                            <select class="form-select" id="usuarioHabilitado" name="usuarioHabilitado" required>
                                                <option value="">Seleccionar categoría...</option>
                                                <option value="Inicial" <?php echo ($novedadEditando && $novedadEditando['usuarioHabilitado'] == 'Inicial') ? 'selected' : ''; ?>>Todos los clientes</option>
                                                <option value="Medium" <?php echo ($novedadEditando && $novedadEditando['usuarioHabilitado'] == 'Medium') ? 'selected' : ''; ?>>Clientes Medium y Premium</option>
                                                <option value="Premium" <?php echo ($novedadEditando && $novedadEditando['usuarioHabilitado'] == 'Premium') ? 'selected' : ''; ?>>Solo clientes Premium</option>
                                            </select>
                                            <div class="form-text">
                                                <small>
                                                    <strong>Inicial:</strong> Visible para todos los clientes<br>
                                                    <strong>Medium:</strong> Visible para Medium y Premium<br>
                                                    <strong>Premium:</strong> Solo visible para Premium
                                                </small>
                                            </div>
                                        </div>
                                        
                                        <div class="col-12">
                                            <div class="d-flex gap-2">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bi bi-<?php echo $novedadEditando ? 'check-lg' : 'plus-circle'; ?> me-1"></i>
                                                    <?php echo $novedadEditando ? 'Actualizar Novedad' : 'Crear Novedad'; ?>
                                                </button>
                                                
                                                <?php if ($novedadEditando): ?>
                                                    <a href="GestionNovedades.php" class="btn btn-secondary">
                                                        <i class="bi bi-x-circle me-1"></i>Cancelar
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col-12 col-lg-7">
                            <div class="form-section">
                                <h4 class="mb-4">
                                    <i class="bi bi-list-ul me-2"></i>
                                    Lista de Novedades
                                </h4>
                                
                                <?php if (empty($novedades)): ?>
                                    <div class="text-center py-5">
                                        <i class="bi bi-megaphone" style="font-size: 3rem; color: var(--muted);"></i>
                                        <h5 class="mt-3 text-muted">No hay novedades creadas</h5>
                                        <p class="text-muted">Crea tu primera novedad usando el formulario.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Título</th>
                                                    <th>Período</th>
                                                    <th>Audiencia</th>
                                                    <th>Estado</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($novedades as $novedad): 
                                                    $hoy = new DateTime();
                                                    $desde = new DateTime($novedad['desde']);
                                                    $hasta = new DateTime($novedad['hasta']);
                                                    
                                                    if ($hoy < $desde) {
                                                        $estado = 'proximamente';
                                                        $estadoTexto = 'Próxima';
                                                        $badgeClass = 'estado-proximamente';
                                                    } elseif ($hoy > $hasta) {
                                                        $estado = 'expirada';
                                                        $estadoTexto = 'Expirada';
                                                        $badgeClass = 'estado-expirada';
                                                    } else {
                                                        $estado = 'activa';
                                                        $estadoTexto = 'Activa';
                                                        $badgeClass = 'estado-activa';
                                                    }
                                                    
                                                    $audienciaClass = 'audiencia-' . strtolower($novedad['usuarioHabilitado']);
                                                ?>
                                                    <tr>
                                                        <td>
                                                            <div class="fw-medium"><?php echo htmlspecialchars($novedad['cabecera']); ?></div>
                                                            <small class="text-muted"><?php echo htmlspecialchars($novedad['descripcion']); ?></small>
                                                        </td>
                                                        <td>
                                                            <small>
                                                                <?php echo date('d/m/Y', strtotime($novedad['desde'])); ?><br>
                                                                al <?php echo date('d/m/Y', strtotime($novedad['hasta'])); ?>
                                                            </small>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-audiencia <?php echo $audienciaClass; ?>">
                                                                <?php echo htmlspecialchars($novedad['audiencia_descripcion']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge <?php echo $badgeClass; ?>">
                                                                <?php echo $estadoTexto; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex gap-1">
                                                                <a href="GestionNovedades.php?editar=<?php echo $novedad['IDnovedad']; ?>" 
                                                                   class="btn btn-sm btn-outline-primary" 
                                                                   title="Editar">
                                                                    <i class="bi bi-pencil"></i>
                                                                </a>
                                                                <form method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta novedad?')">
                                                                    <input type="hidden" name="action" value="eliminar">
                                                                    <input type="hidden" name="id_novedad" value="<?php echo $novedad['IDnovedad']; ?>">
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
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
                                <?php endif; ?>
                            </div>
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

        // Validación de fechas
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('formNovedad');
            const desdeInput = document.getElementById('desde');
            const hastaInput = document.getElementById('hasta');
            
            form.addEventListener('submit', function(e) {
                const desde = new Date(desdeInput.value);
                const hasta = new Date(hastaInput.value);
                const hoy = new Date();
                hoy.setHours(0, 0, 0, 0);
                
                if (desde < hoy) {
                    e.preventDefault();
                    alert('La fecha de inicio no puede ser anterior al día de hoy.');
                    return false;
                }
                
                if (hasta <= desde) {
                    e.preventDefault();
                    alert('La fecha de fin debe ser posterior a la fecha de inicio.');
                    return false;
                }
            });
            
            // Establecer fecha mínima como hoy
            const hoy = new Date().toISOString().split('T')[0];
            desdeInput.min = hoy;
            hastaInput.min = hoy;
        });
    </script>
</body>
</html>