<?php
session_start();


require_once '../Model/CrearPromociones.php';




$IDU = (int)$_SESSION['IDusuario'];
$local = getLocalPorUsuario($IDU);

if (!$local) {
    die("No se encontró el local para este usuario.");
}

$mensaje = '';
$tipoMensaje = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'descripcion' => trim($_POST['descripcion']),
        'desde' => $_POST['desde'],
        'hasta' => $_POST['hasta'],
        'categoria' => $_POST['categoria'],
        'dia' => $_POST['dia'] ?: null,
        'local_id' => $local['IDlocal']
    ];

    // Validaciones básicas
    if (empty($datos['descripcion']) || empty($datos['desde']) || empty($datos['hasta']) || empty($datos['categoria'])) {
        $mensaje = 'Por favor, complete todos los campos obligatorios.';
        $tipoMensaje = 'danger';
    } elseif ($datos['desde'] > $datos['hasta']) {
        $mensaje = 'La fecha de inicio no puede ser posterior a la fecha de fin.';
        $tipoMensaje = 'danger';
    } else {
        if (crearPromocion($datos)) {
            $mensaje = '¡Promoción creada exitosamente! La promoción está pendiente de aprobación.';
            $tipoMensaje = 'success';
            
            // Limpiar el formulario
            $_POST = [];
        } else {
            $mensaje = 'Error al crear la promoción. Por favor, intente nuevamente.';
            $tipoMensaje = 'danger';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Promoción - ShoppingUTN</title>
    
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

        .form-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            padding: 2rem;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .required::after {
            content: " *";
            color: var(--danger);
        }

        .info-card {
            background: linear-gradient(135deg, var(--subtle) 0%, #ffffff 100%);
            border: 1px solid rgba(var(--primary-rgb), 0.1);
            border-radius: 10px;
            padding: 1.5rem;
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            background: rgba(var(--primary-rgb), 0.1);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .feature-icon i {
            font-size: 1.5rem;
            color: var(--primary);
        }

        @media (max-width: 767px) {
            .sidebar {
                min-height: auto;
                border-right: none;
                border-bottom: 1px solid #eee;
            }
            
            .form-container {
                padding: 1rem;
                margin: 0 -1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
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
                        <li><a class="dropdown-item" href="./DashBoardLocal.php">Inicio</a></li>
                        <li><a class="dropdown-item" href="./CrearPromocion.php">Crear Promoción</a></li>
                        <li><a class="dropdown-item" href="./GestionarPromociones.php">Gestionar Promociones</a></li>
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
          <h6 class="text-muted">Navegación</h6>
          <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link " href="./DashboardTienda.php"><i class="bi bi-house-door-fill me-2"></i>Inicio</a></li>
            <li class="nav-item">
                            <a class="nav-link active " href="./CrearPromocion.php">
                                <i class="bi bi-plus-circle me-2"></i>Crear Promoción
                            </a>
                        </li>
            <li class="nav-item"><a class="nav-link" href="./HistorialUsos.php"><i class="bi bi-list-ul me-2"></i>Historial</a></li>
<li class="nav-item">
    <a class="nav-link" href="./Contacto.php">
        <i class="bi bi-envelope me-2"></i>Contactos
    </a>
</li>          </ul>
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

            <!-- Main Content -->
            <main class="col-12 col-md-9 col-lg-10 py-4">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-12 col-lg-10">
                            <!-- Header -->
                            <div class="d-flex align-items-center mb-4">
                                <div class="me-3">
                                    <i class="bi bi-plus-circle" style="font-size: 2rem; color: var(--primary);"></i>
                                </div>
                                <div>
                                    <h1 class="h3 mb-1">Crear Nueva Promoción</h1>
                                    <p class="text-muted mb-0">Completa el formulario para crear una nueva promoción en tu local</p>
                                </div>
                            </div>

                            <!-- Alertas -->
                            <?php if ($mensaje): ?>
                                <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show" role="alert">
                                    <?php echo htmlspecialchars($mensaje); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>

                            <div class="row">
                                <!-- Formulario -->
                                <div class="col-12 col-lg-8 mb-4">
                                    <div class="form-container">
                                        <form method="POST" id="promocionForm">
                                            <!-- Descripción -->
                                            <div class="mb-4">
                                                <label for="descripcion" class="form-label required">Descripción de la Promoción</label>
                                                <textarea 
                                                    class="form-control" 
                                                    id="descripcion" 
                                                    name="descripcion" 
                                                    rows="4" 
                                                    placeholder="Ej: 20% de descuento en toda la tienda, 2x1 en productos seleccionados, etc."
                                                    required
                                                    maxlength="500"
                                                ><?php echo htmlspecialchars($_POST['descripcion'] ?? ''); ?></textarea>
                                                <div class="form-text">
                                                    <span id="contadorCaracteres">0</span>/500 caracteres
                                                </div>
                                            </div>

                                            <!-- Fechas -->
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <label for="desde" class="form-label required">Fecha de Inicio</label>
                                                    <input 
                                                        type="date" 
                                                        class="form-control" 
                                                        id="desde" 
                                                        name="desde" 
                                                        value="<?php echo htmlspecialchars($_POST['desde'] ?? ''); ?>"
                                                        required
                                                        min="<?php echo date('Y-m-d'); ?>"
                                                    >
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="hasta" class="form-label required">Fecha de Fin</label>
                                                    <input 
                                                        type="date" 
                                                        class="form-control" 
                                                        id="hasta" 
                                                        name="hasta" 
                                                        value="<?php echo htmlspecialchars($_POST['hasta'] ?? ''); ?>"
                                                        required
                                                        min="<?php echo date('Y-m-d'); ?>"
                                                    >
                                                </div>
                                            </div>

                                            <!-- Categoría y Día -->
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <label for="categoria" class="form-label required">Categoría de Cliente</label>
                                                    <select class="form-select" id="categoria" name="categoria" required>
                                                        <option value="">Selecciona una categoría</option>
                                                        <option value="Inicial" <?php echo (($_POST['categoria'] ?? '') === 'Inicial') ? 'selected' : ''; ?>>Inicial</option>
                                                        <option value="Medium" <?php echo (($_POST['categoria'] ?? '') === 'Medium') ? 'selected' : ''; ?>>Medium</option>
                                                        <option value="Premium" <?php echo (($_POST['categoria'] ?? '') === 'Premium') ? 'selected' : ''; ?>>Premium</option>
                                                    </select>
                                                    <div class="form-text">
                                                        Los clientes de categorías superiores también podrán acceder
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="dia" class="form-label">Día de la Semana</label>
                                                    <select class="form-select" id="dia" name="dia">
                                                        <option value="">Todos los días</option>
                                                        <option value="1" <?php echo (($_POST['dia'] ?? '') === '1') ? 'selected' : ''; ?>>Domingo</option>
                                                        <option value="2" <?php echo (($_POST['dia'] ?? '') === '2') ? 'selected' : ''; ?>>Lunes</option>
                                                        <option value="3" <?php echo (($_POST['dia'] ?? '') === '3') ? 'selected' : ''; ?>>Martes</option>
                                                        <option value="4" <?php echo (($_POST['dia'] ?? '') === '4') ? 'selected' : ''; ?>>Miércoles</option>
                                                        <option value="5" <?php echo (($_POST['dia'] ?? '') === '5') ? 'selected' : ''; ?>>Jueves</option>
                                                        <option value="6" <?php echo (($_POST['dia'] ?? '') === '6') ? 'selected' : ''; ?>>Viernes</option>
                                                        <option value="7" <?php echo (($_POST['dia'] ?? '') === '7') ? 'selected' : ''; ?>>Sábado</option>
                                                    </select>
                                                    <div class="form-text">
                                                        Opcional: selecciona un día específico
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Botones -->
                                            <div class="d-flex gap-2 pt-3">
                                                <button type="submit" class="btn btn-primary px-4">
                                                    <i class="bi bi-check-lg me-2"></i>Crear Promoción
                                                </button>
                                                <a href="./DashBoardTienda.php" class="btn btn-outline-secondary px-4">
                                                    <i class="bi bi-arrow-left me-2"></i>Volver al Menu
                                                </a>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Información -->
                                <div class="col-12 col-lg-4">
                                    <div class="info-card h-100">
                                        <h5 class="mb-4">
                                            <i class="bi bi-info-circle me-2"></i>Información Importante
                                        </h5>
                                        
                                        <div class="mb-4">
                                            <div class="feature-icon">
                                                <i class="bi bi-clock"></i>
                                            </div>
                                            <h6>Proceso de Aprobación</h6>
                                            <p class="small text-muted mb-0">
                                                Todas las promociones deben ser aprobadas por el administrador antes de estar disponibles para los clientes.
                                            </p>
                                        </div>

                                        <div class="mb-4">
                                            <div class="feature-icon">
                                                <i class="bi bi-eye"></i>
                                            </div>
                                            <h6>Visibilidad</h6>
                                            <p class="small text-muted mb-0">
                                                Los clientes solo verán las promociones que correspondan a su categoría o categorías inferiores.
                                            </p>
                                        </div>

                                        <div class="mb-4">
                                            <div class="feature-icon">
                                                <i class="bi bi-calendar-check"></i>
                                            </div>
                                            <h6>Vigencia</h6>
                                            <p class="small text-muted mb-0">
                                                Las promociones se mostrarán automáticamente durante el período de fechas especificado.
                                            </p>
                                        </div>

                                        <div class="alert alert-warning mt-4">
                                            <small>
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                <strong>Importante:</strong> Una vez creada, la promoción no puede ser editada. 
                                                Si cometes un error, deberás eliminarla y crear una nueva.
                                            </small>
                                        </div>
                                    </div>
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
        const descripcion = document.getElementById('descripcion');
        const contador = document.getElementById('contadorCaracteres');
        
        descripcion.addEventListener('input', function() {
            contador.textContent = this.value.length;
        });

        contador.textContent = descripcion.value.length;

        const desdeInput = document.getElementById('desde');
        const hastaInput = document.getElementById('hasta');

        desdeInput.addEventListener('change', function() {
            hastaInput.min = this.value;
        });

        hastaInput.addEventListener('change', function() {
            if (this.value < desdeInput.value) {
                this.setCustomValidity('La fecha de fin no puede ser anterior a la fecha de inicio');
            } else {
                this.setCustomValidity('');
            }
        });

        document.getElementById('promocionForm').addEventListener('submit', function(e) {
            const descripcion = document.getElementById('descripcion').value.trim();
            const desde = document.getElementById('desde').value;
            const hasta = document.getElementById('hasta').value;
            const categoria = document.getElementById('categoria').value;

            if (!descripcion || !desde || !hasta || !categoria) {
                e.preventDefault();
                alert('Por favor, complete todos los campos obligatorios.');
                return;
            }

            if (desde > hasta) {
                e.preventDefault();
                alert('La fecha de inicio no puede ser posterior a la fecha de fin.');
                return;
            }
        });
    </script>
</body>
</html>