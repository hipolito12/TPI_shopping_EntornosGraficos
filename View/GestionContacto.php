<?php
session_start();
require_once '../Model/ContactoModel.php';

// Verificar permisos de Administrador
if (!isset($_SESSION['IDusuario']) || $_SESSION['Rol'] != 'Administrador') {
    header("Location: ../index.php");
    exit;
}

$mensaje = '';
$tipoMensaje = '';

// Lógica para cerrar el ticket (cambiar estado a 1)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cerrar') {
    $idContacto = $_POST['id_contacto'] ?? 0;
    if (cerrarContacto($idContacto)) {
        $mensaje = "El ticket #$idContacto ha sido cerrado exitosamente.";
        $tipoMensaje = 'success';
    } else {
        $mensaje = "Error al intentar cerrar el ticket.";
        $tipoMensaje = 'danger';
    }
}

// Obtener la lista
$contactos = getTodosLosContactos();

// Calcular estadísticas rápidas
$pendientes = 0;
foreach ($contactos as $c) {
    if ($c['estado'] == 0) $pendientes++;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Contactos - Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary: #4A3BC7;
            --primary-rgb: 74, 59, 199;
            --subtle: #F3F1FF;
        }
        body { background: linear-gradient(180deg, #fff 0%, var(--subtle) 100%); min-height: 100vh; }
        .navbar, .card-header { background: var(--primary); color: #fff; }
        .sidebar { min-height: 100vh; background: #fff; border-right: 1px solid #e9e9ef; }
        .sidebar .nav-link { color: #333; }
        .sidebar .nav-link.active { background: rgba(var(--primary-rgb), 0.08); color: var(--primary); border-radius: .5rem; }
        
        .card-ticket {
            transition: transform 0.2s;
            border: 1px solid #eee;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .card-ticket:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .estado-pendiente { background-color: #ffc107; color: #000; }
        .estado-cerrado { background-color: #198754; color: #fff; }
        
        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="#">
                <strong>ShoppingUTN - Administrador</strong>
            </a>
            <div class="d-flex align-items-center ms-auto">
                <span class="text-white me-3">Hola, <?php echo htmlspecialchars($_SESSION['Nombre'] ?? 'Admin'); ?></span>
                <a class="btn btn-sm btn-outline-light" href="../Model/logout.php">Salir</a>
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
                            <a class="nav-link " href="./DashboardAdministrador.php">
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
    <a class="nav-link active" href="./GestionContacto.php">
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
                            <h2 class="h3 mb-0">Buzón de Contacto</h2>
                            <p class="text-muted small">Gestiona los mensajes y reclamos de los usuarios.</p>
                        </div>
                        <a href="./DashboardAdministrador.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Volver menu
                        </a>
                    </div>

                    <?php if ($mensaje): ?>
                        <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($mensaje); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card shadow-sm border-0">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4" style="width: 5%;">ID</th>
                                            <th style="width: 20%;">Asunto</th>
                                            <th style="width: 45%;">Mensaje</th>
                                            <th style="width: 15%;">Estado</th>
                                            <th class="text-end pe-4" style="width: 15%;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($contactos)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-5 text-muted">No hay mensajes de contacto.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($contactos as $c): ?>
                                                <tr>
                                                    <td class="ps-4 fw-bold text-secondary">#<?php echo $c['id']; ?></td>
                                                    <td class="fw-medium text-primary"><?php echo htmlspecialchars($c['asunto']); ?></td>
                                                    <td>
                                                        <div class="text-muted small text-truncate-2">
                                                            <?php echo htmlspecialchars($c['cuerpo']); ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php if ($c['estado'] == 0): ?>
                                                            <span class="badge estado-pendiente rounded-pill">Pendiente</span>
                                                        <?php else: ?>
                                                            <span class="badge estado-cerrado rounded-pill">Cerrado</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end pe-4">
                                                        <button class="btn btn-sm btn-outline-primary" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#modalDetalle"
                                                                data-id="<?php echo $c['id']; ?>"
                                                                data-asunto="<?php echo htmlspecialchars($c['asunto']); ?>"
                                                                data-cuerpo="<?php echo htmlspecialchars($c['cuerpo']); ?>"
                                                                data-estado="<?php echo $c['estado']; ?>">
                                                            <i class="bi bi-eye me-1"></i>Ver
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitulo">Detalle del Mensaje</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="small text-muted text-uppercase fw-bold">Asunto</label>
                        <h4 id="detalleAsunto" class="text-dark mt-1"></h4>
                    </div>
                    
                    <div class="card bg-light border-0 p-3 mb-4">
                        <label class="small text-muted text-uppercase fw-bold mb-2">Mensaje del Usuario</label>
                        <p id="detalleCuerpo" class="mb-0" style="white-space: pre-wrap;"></p>
                    </div>

                    <div id="statusContainer" class="mb-3">
                        </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-arrow-left me-1"></i>Volver
                    </button>

                    <form method="POST" id="formCerrar">
                        <input type="hidden" name="action" value="cerrar">
                        <input type="hidden" name="id_contacto" id="inputID">
                        <button type="submit" class="btn btn-success" id="btnCerrarTicket">
                            <i class="bi bi-check-circle me-1"></i>Marcar como Cerrado
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script para pasar datos al Modal
        const modalDetalle = document.getElementById('modalDetalle');
        modalDetalle.addEventListener('show.bs.modal', event => {
            // Botón que activó el modal
            const button = event.relatedTarget;
            
            // Extraer info de los atributos data-*
            const id = button.getAttribute('data-id');
            const asunto = button.getAttribute('data-asunto');
            const cuerpo = button.getAttribute('data-cuerpo');
            const estado = button.getAttribute('data-estado'); // 0 o 1

            // Actualizar el contenido del modal
            document.getElementById('modalTitulo').textContent = 'Mensaje #' + id;
            document.getElementById('detalleAsunto').textContent = asunto;
            document.getElementById('detalleCuerpo').textContent = cuerpo;
            document.getElementById('inputID').value = id;

            // Manejar visualización del botón "Cerrar Ticket"
            const btnCerrar = document.getElementById('btnCerrarTicket');
            const statusContainer = document.getElementById('statusContainer');

            if (estado == '1') {
                // Si ya está cerrado
                btnCerrar.style.display = 'none'; // Ocultar botón de acción
                statusContainer.innerHTML = '<div class="alert alert-success py-2"><i class="bi bi-check-circle-fill me-2"></i>Este ticket ya se encuentra <strong>Cerrado</strong>.</div>';
            } else {
                // Si está pendiente
                btnCerrar.style.display = 'block'; // Mostrar botón
                statusContainer.innerHTML = '<div class="alert alert-warning py-2"><i class="bi bi-clock-history me-2"></i>Estado actual: <strong>Pendiente</strong></div>';
            }
        });
    </script>
</body>
</html>