<?php
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../Model/GestionLocales.php';

$mensaje = '';
$tipoMensaje = '';
$localEdit = null;
$mostrarModal = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'crear':
            $datos = [
                'nombre' => trim($_POST['nombre']),
                'rubro' => trim($_POST['rubro']),
                'usuarioFK' => $_POST['usuarioFK'],
                'ubicacionFK' => $_POST['ubicacionFK']
            ];
            
            if (empty($datos['nombre']) || empty($datos['rubro']) || empty($datos['usuarioFK']) || empty($datos['ubicacionFK'])) {
                $mensaje = 'Todos los campos son obligatorios.';
                $tipoMensaje = 'danger';
            } else {
                if (crearLocal($datos)) {
                    $mensaje = 'Local creado exitosamente.';
                    $tipoMensaje = 'success';
                } else {
                    $mensaje = 'Error al crear el local.';
                    $tipoMensaje = 'danger';
                }
            }
            break;
            
        case 'editar':
            $id = $_POST['id'];
            $datos = [
                'nombre' => trim($_POST['nombre']),
                'rubro' => trim($_POST['rubro']),
                'usuarioFK' => $_POST['usuarioFK'],
                'ubicacionFK' => $_POST['ubicacionFK']
            ];
            
            if (empty($datos['nombre']) || empty($datos['rubro']) || empty($datos['usuarioFK']) || empty($datos['ubicacionFK'])) {
                $mensaje = 'Todos los campos son obligatorios.';
                $tipoMensaje = 'danger';
            } else {
                if (actualizarLocal($id, $datos)) {
                    $mensaje = 'Local actualizado exitosamente.';
                    $tipoMensaje = 'success';
                } else {
                    $mensaje = 'Error al actualizar el local.';
                    $tipoMensaje = 'danger';
                }
            }
            break;
            
        case 'eliminar':
            $id = $_POST['id'];
            $result = eliminarLocal($id);
            $mensaje = $result['message'];
            $tipoMensaje = $result['success'] ? 'success' : 'danger';
            break;
    }
    
    header('Location: GestionLocales.php?mensaje=' . urlencode($mensaje) . '&tipo=' . $tipoMensaje);
    exit;
}

if (isset($_GET['mensaje'])) {
    $mensaje = $_GET['mensaje'];
    $tipoMensaje = $_GET['tipo'] ?? 'info';
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['editar'])) {
    $localEdit = getLocalById($_GET['editar']);
    if ($localEdit) {
        $mostrarModal = true;
    } else {
        $mensaje = 'Local no encontrado.';
        $tipoMensaje = 'danger';
    }
}

$locales = getLocalesCompletos();
$ubicaciones = getUbicaciones();
$comerciantes = getComerciantes();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Locales - ShoppingUTN</title>
    
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

        .local-card {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            transition: all 0.3s ease;
            background: white;
        }

        .local-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-color: var(--primary);
        }

        .badge-estado {
            font-size: 0.75rem;
            padding: 0.35rem 0.65rem;
        }

        .estado-activo { background-color: var(--success); }
        .estado-inactivo { background-color: var(--danger); }

        .action-btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .stats-card {
            background: white;
            border-radius: 10px;
            border-left: 4px solid var(--primary);
        }

        .form-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
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
                            <a class="nav-link active" href="./GestionLocales.php">
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
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="h3 mb-1">
                                <i class="bi bi-shop me-2" style="color: var(--primary);"></i>
                                Gestión de Locales
                            </h1>
                            <p class="text-muted mb-0">Administra todos los locales del shopping</p>
                        </div>
                        <a href="GestionLocales.php?crear=1" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Nuevo Local
                        </a>
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
                                        <h6 class="mb-0">Total Locales</h6>
                                        <div class="h4 mb-0 text-primary"><?php echo count($locales); ?></div>
                                    </div>
                                    <i class="bi bi-shop" style="font-size: 1.5rem; color: var(--primary);"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stats-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Ubicaciones</h6>
                                        <div class="h4 mb-0 text-success"><?php echo count($ubicaciones); ?></div>
                                    </div>
                                    <i class="bi bi-geo-alt" style="font-size: 1.5rem; color: var(--success);"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stats-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Comerciantes</h6>
                                        <div class="h4 mb-0 text-info"><?php echo count($comerciantes); ?></div>
                                    </div>
                                    <i class="bi bi-people" style="font-size: 1.5rem; color: var(--info);"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Locales Registrados</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($locales)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-shop" style="font-size: 3rem; color: var(--muted);"></i>
                                    <h5 class="mt-3 text-muted">No hay locales registrados</h5>
                                    <p class="text-muted">Comienza agregando el primer local al sistema.</p>
                                    <a href="GestionLocales.php?crear=1" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-2"></i>Agregar Primer Local
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Rubro</th>
                                                <th>Dueño</th>
                                                <th>Ubicación</th>
                                                <th>Promociones</th>
                                                <th>Código</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($locales as $local): ?>
                                                <tr>
                                                    <td>
                                                        <div class="fw-medium"><?php echo htmlspecialchars($local['nombre']); ?></div>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($local['rubro']); ?></td>
                                                    <td>
                                                        <div class="fw-medium"><?php echo htmlspecialchars($local['dueño_nombre'] ?? 'N/A'); ?></div>
                                                        <?php if ($local['dueño_email']): ?>
                                                            <small class="text-muted"><?php echo htmlspecialchars($local['dueño_email']); ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="fw-medium"><?php echo htmlspecialchars($local['ubicacion_nombre'] ?? 'N/A'); ?></div>
                                                        <?php if ($local['ubicacion_descripcion']): ?>
                                                            <small class="text-muted"><?php echo htmlspecialchars($local['ubicacion_descripcion']); ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary"><?php echo $local['total_promociones']; ?></span>
                                                    </td>
                                                    <td>
                                                        <code><?php echo htmlspecialchars($local['codigo']); ?></code>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="GestionLocales.php?editar=<?php echo $local['IDlocal']; ?>" class="btn btn-outline-primary action-btn" title="Editar">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            <form method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este local?');">
                                                                <input type="hidden" name="action" value="eliminar">
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
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div class="modal fade" id="modalLocal" tabindex="-1" aria-labelledby="modalLocalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLocalLabel">
                        <i class="bi bi-shop me-2"></i>
                        <?php echo $localEdit ? 'Editar Local' : 'Nuevo Local'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="cerrarModal()"></button>
                </div>
                <form method="POST" id="formLocal">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="<?php echo $localEdit ? 'editar' : 'crear'; ?>">
                        <?php if ($localEdit): ?>
                            <input type="hidden" name="id" value="<?php echo $localEdit['IDlocal']; ?>">
                        <?php endif; ?>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label required">Nombre del Local</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required
                                       placeholder="Ej: Tienda de Ropa XYZ"
                                       value="<?php echo $localEdit ? htmlspecialchars($localEdit['nombre']) : ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="rubro" class="form-label required">Rubro</label>
                                <input type="text" class="form-control" id="rubro" name="rubro" required
                                       placeholder="Ej: Venta de ropa, Calzado, etc."
                                       value="<?php echo $localEdit ? htmlspecialchars($localEdit['rubro']) : ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="usuarioFK" class="form-label required">Dueño del Local</label>
                                <select class="form-select" id="usuarioFK" name="usuarioFK" required>
                                    <option value="">Seleccionar dueño...</option>
                                    <?php foreach ($comerciantes as $comerciante): ?>
                                        <option value="<?php echo $comerciante['IDusuario']; ?>"
                                            <?php echo ($localEdit && $localEdit['usuarioFK'] == $comerciante['IDusuario']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($comerciante['nombreUsuario']); ?> 
                                            (<?php echo htmlspecialchars($comerciante['email']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">
                                    Solo se muestran usuarios con rol de comerciante activos.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="ubicacionFK" class="form-label required">Ubicación</label>
                                <select class="form-select" id="ubicacionFK" name="ubicacionFK" required>
                                    <option value="">Seleccionar ubicación...</option>
                                    <?php foreach ($ubicaciones as $ubicacion): ?>
                                        <option value="<?php echo $ubicacion['IDubicacion']; ?>"
                                            <?php echo ($localEdit && $localEdit['ubicacionFK'] == $ubicacion['IDubicacion']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($ubicacion['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">
                                    Ubicación física dentro del shopping.
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <small>
                                        <i class="bi bi-info-circle me-1"></i>
                                        <strong>Información:</strong> 
                                        <?php if ($localEdit): ?>
                                            Editando local existente. Los cambios se guardarán inmediatamente.
                                        <?php else: ?>
                                            El código del local se generará automáticamente después de guardar y será único para cada local.
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="cerrarModal()">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i>
                            <?php echo $localEdit ? 'Actualizar Local' : 'Crear Local'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        <?php if ($mostrarModal || isset($_GET['crear'])): ?>
            document.addEventListener('DOMContentLoaded', function() {
                const modal = new bootstrap.Modal(document.getElementById('modalLocal'));
                modal.show();
            });
        <?php endif; ?>

        function cerrarModal() {
            window.location.href = 'GestionLocales.php';
        }

        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Validación del formulario
        document.getElementById('formLocal')?.addEventListener('submit', function(e) {
            const nombre = document.getElementById('nombre').value.trim();
            const rubro = document.getElementById('rubro').value.trim();
            const usuarioFK = document.getElementById('usuarioFK').value;
            const ubicacionFK = document.getElementById('ubicacionFK').value;

            if (!nombre || !rubro || !usuarioFK || !ubicacionFK) {
                e.preventDefault();
                alert('Por favor, complete todos los campos obligatorios.');
                return;
            }

            if (nombre.length < 2) {
                e.preventDefault();
                alert('El nombre del local debe tener al menos 2 caracteres.');
                return;
            }
        });
    </script>
</body>
</html>