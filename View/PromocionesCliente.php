<?php
session_start();
require_once '../Model/PromocionesCliente.php';

if (!isset($_SESSION['IDusuario']) || $_SESSION['Rol'] !='Usuario') {
session_unset();
    header("Location: ../index.php");
    exit;
}

$categoria_usuario = $_SESSION['Categoria'];
$nombre_usuario = $_SESSION['Nombre'];
$usuario_id = $_SESSION['IDusuario'];

$promocionesModel = new PromocionesModel();

$codigo_busqueda = '';
$locales_con_promociones = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['codigo_local'])) {
        $codigo_busqueda = trim($_POST['codigo_local']);
        
        if (!empty($codigo_busqueda)) {
            $locales_con_promociones = $promocionesModel->getPromocionesDisponibles(
                $usuario_id, 
                $categoria_usuario, 
                $codigo_busqueda
            );
        }
    } elseif (isset($_POST['usar_promocion'])) {
        $promocion_id = $_POST['promocion_id'];
        
        $resultado = $promocionesModel->usarPromocion($usuario_id, $promocion_id);
        echo json_encode($resultado);
        exit;
    }
} else {
    $locales_con_promociones = $promocionesModel->getPromocionesDisponibles(
        $usuario_id, 
        $categoria_usuario
    );
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promociones - Shopping</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4A3BC7;
            --primary-rgb: 74, 59, 199;
            --subtle: #F3F1F9;
        }
        
        .bg-primary-custom {
            background-color: var(--primary) !important;
        }
        
        .text-primary-custom {
            color: var(--primary) !important;
        }
        
        .btn-primary-custom {
            background-color: var(--primary);
            border-color: var(--primary);
            color: white;
        }
        
        .btn-primary-custom:hover {
            background-color: #3A2BA7;
            border-color: #3A2BA7;
            color: white;
        }
        
        .btn-outline-custom {
            border-color: var(--primary);
            color: var(--primary);
        }
        
        .btn-outline-custom:hover {
            background-color: var(--primary);
            color: white;
        }
        
        .card-promocion {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .card-promocion:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(var(--primary-rgb), 0.15);
            border-color: var(--primary);
        }
        
        .card-header-custom {
            background-color: var(--subtle);
            border-bottom: 2px solid var(--primary);
        }
        
        .badge-categoria {
            background-color: var(--primary);
            color: white;
        }
        
        .badge-inicial { background-color: #28a745; }
        .badge-medium { background-color: #ffc107; color: #000; }
        .badge-premium { background-color: #dc3545; }
        
        .search-box {
            background: linear-gradient(135deg, var(--subtle) 0%, #ffffff 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid #e0e0e0;
        }
        
        .alert-fixed {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            min-width: 300px;
        }
        
        .btn-used {
            background-color: #6c757d;
            border-color: #6c757d;
            color: white;
            cursor: not-allowed;
        }
        
        .btn-pending {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #000;
            cursor: not-allowed;
        }
        
        .promo-card-footer {
            border-top: 1px solid #e9ecef;
            padding-top: 1rem;
            margin-top: auto;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
        }
        
        .info-value {
            color: #6c757d;
        }
    </style>
</head>
<body>
   
  <?php include_once(__DIR__ . "/../layouts/Navbar.php"); ?>

    <!-- Alertas -->
    <div id="alertContainer" class="alert-fixed"></div>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h1 class="text-primary-custom mb-3">🎁 Promociones Disponibles</h1>
                <p class="text-muted lead">Descubre las mejores ofertas en nuestros locales comerciales</p>
            </div>
        </div>

        <div class="row justify-content-center mb-5">
            <div class="col-md-8">
                <div class="search-box">
                    <h4 class="text-primary-custom mb-3">🔍 Buscar promociones por nombre de local</h4>
                    <form method="POST" action="PromocionesCliente.php" id="searchForm">
                        <div class="input-group input-group-lg">
                            <input type="text" 
                                   class="form-control" 
                                   name="codigo_local" 
                                   placeholder="Ingresa el nombre o parte del nombre del local..."
                                   value="<?php echo htmlspecialchars($codigo_busqueda); ?>"
                                   required>
                            <button class="btn btn-primary-custom" type="submit">
                                Buscar Promociones
                            </button>
                        </div>
                        <?php if (!empty($codigo_busqueda)): ?>
                            <div class="mt-3">
                                <a href="PromocionesCliente.php" class="btn btn-outline-secondary btn-sm">
                                    ← Ver todas las promociones
                                </a>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <div class="row">
            <?php if (empty($locales_con_promociones)): ?>
                <div class="col-12">
                    <div class="alert alert-warning text-center">
                        <h5>😔 No se encontraron promociones</h5>
                        <?php if (!empty($codigo_busqueda)): ?>
                            <p class="mb-0">No hay promociones activas para locales que contengan "<strong><?php echo htmlspecialchars($codigo_busqueda); ?></strong>" en su nombre</p>
                            <p class="mb-0"><small>Verifica el nombre o intenta con otro local</small></p>
                        <?php else: ?>
                            <p class="mb-0">No hay promociones disponibles en este momento para tu categoría</p>
                            <?php 
                            $promociones_usadas = $promocionesModel->getPromocionesUsadas($usuario_id);
                            if (!empty($promociones_usadas)): ?>
                                <p class="mb-0"><small>Algunas promociones pueden estar ocultas porque ya las has solicitado.</small></p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <?php if (!empty($codigo_busqueda)): ?>
                    <div class="col-12">
                        <h2 class="text-primary-custom mb-4">
                            🔍 Resultados para: "<?php echo htmlspecialchars($codigo_busqueda); ?>"
                            <span class="badge bg-primary"><?php echo count($locales_con_promociones); ?> local(es) encontrado(s)</span>
                        </h2>
                    </div>
                    <?php foreach ($locales_con_promociones as $local_id => $local_data): ?>
                        <div class="col-12 mb-4">
                            <div class="card">
                                <div class="card-header card-header-custom">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h3 class="mb-1">🏪 <?php echo htmlspecialchars($local_data['nombre']); ?></h3>
                                            <p class="mb-0">
                                                <strong>Código:</strong> <?php echo $local_id; ?> | 
                                                <strong>Rubro:</strong> <?php echo htmlspecialchars($local_data['rubro']); ?> |
                                                <strong>Ubicación:</strong> <?php echo htmlspecialchars($local_data['ubicacion']); ?>
                                            </p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <span class="badge bg-primary rounded-pill fs-6">
                                                <?php echo count($local_data['promociones']); ?> promoción(es)
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php foreach ($local_data['promociones'] as $promocion): ?>
                                            <div class="col-md-6 col-lg-4 mb-4">
                                                <div class="card card-promocion h-100">
                                                    <div class="card-body d-flex flex-column">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <span class="badge badge-categoria badge-<?php echo strtolower($promocion['categoria_requerida']); ?>">
                                                                <?php echo ucfirst($promocion['categoria_requerida']); ?>
                                                            </span>
                                                            <small class="text-muted">#<?php echo $promocion['id_promocion']; ?></small>
                                                        </div>
                                                        
                                                        <h6 class="card-title fw-bold"><?php echo htmlspecialchars($promocion['descripcion_promo']); ?></h6>
                                                        
                                                        <div class="promo-details mt-3 flex-grow-1">
                                                            <div class="mb-2">
                                                                <small class="text-muted">
                                                                    <strong>📅 Válida:</strong> hasta <?php echo date('d/m/Y', strtotime($promocion['fecha_hasta'])); ?>
                                                                </small>
                                                            </div>
                                                            <?php if ($promocion['dia_promo']): ?>
                                                                <div class="mb-2">
                                                                    <small class="text-muted">
                                                                        <strong>📆 Día:</strong> <?php echo PromocionesModel::getDiaSemana($promocion['dia_promo']); ?>
                                                                    </small>
                                                                </div>
                                                            <?php endif; ?>
                                                            <div class="mb-2">
                                                                <small class="text-muted">
                                                                    <strong>🏪 Local:</strong> <?php echo htmlspecialchars($local_data['nombre']); ?>
                                                                </small>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="promo-card-footer mt-auto">
                                                            <div class="row g-2">
                                                                <div class="col-6">
                                                                    <button class="btn btn-outline-custom w-100 detalles-promocion-btn" 
                                                                            data-promocion='<?php echo json_encode($promocion); ?>'
                                                                            data-local='<?php echo json_encode($local_data); ?>'>
                                                                        📋 Detalles
                                                                    </button>
                                                                </div>
                                                                <div class="col-6">
                                                                    <button class="btn btn-primary-custom w-100 usar-promocion-btn" 
                                                                            data-promocion-id="<?php echo $promocion['id_promocion']; ?>"
                                                                            data-local-id="<?php echo $local_id; ?>"
                                                                            data-descripcion="<?php echo htmlspecialchars($promocion['descripcion_promo']); ?>">
                                                                        🛒 Solicitar
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                
                <?php else: ?>
                    <div class="col-12">
                        <h2 class="text-primary-custom mb-4">📋 Todas las Promociones Disponibles</h2>
                        <p class="text-muted mb-4">Mostrando <?php echo count($locales_con_promociones); ?> locales con promociones activas</p>
                        <?php foreach ($locales_con_promociones as $local_id => $local_data): ?>
                            <div class="card mb-4">
                                <div class="card-header card-header-custom">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h4 class="mb-1">🏪 <?php echo htmlspecialchars($local_data['nombre']); ?></h4>
                                            <p class="mb-0">
                                                <strong>Código:</strong> <?php echo $local_id; ?> | 
                                                <strong>Rubro:</strong> <?php echo htmlspecialchars($local_data['rubro']); ?> |
                                                <strong>Ubicación:</strong> <?php echo htmlspecialchars($local_data['ubicacion']); ?>
                                            </p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <span class="badge bg-primary rounded-pill">
                                                <?php echo count($local_data['promociones']); ?> promoción(es)
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php foreach ($local_data['promociones'] as $promocion): ?>
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="card card-promocion h-100">
                                                    <div class="card-body d-flex flex-column">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <span class="badge badge-categoria badge-<?php echo strtolower($promocion['categoria_requerida']); ?>">
                                                                <?php echo ucfirst($promocion['categoria_requerida']); ?>
                                                            </span>
                                                            <small class="text-muted">#<?php echo $promocion['id_promocion']; ?></small>
                                                        </div>
                                                        
                                                        <h6 class="card-title fw-bold flex-grow-1"><?php echo htmlspecialchars($promocion['descripcion_promo']); ?></h6>
                                                        
                                                        <div class="promo-details mt-2">
                                                            <div class="mb-2">
                                                                <small class="text-muted">
                                                                    <strong>Válida hasta:</strong> <?php echo date('d/m/Y', strtotime($promocion['fecha_hasta'])); ?>
                                                                </small>
                                                            </div>
                                                            <?php if ($promocion['dia_promo']): ?>
                                                                <div class="mb-2">
                                                                    <small class="text-muted">
                                                                        <strong>Día:</strong> <?php echo PromocionesModel::getDiaSemana($promocion['dia_promo']); ?>
                                                                    </small>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        
                                                        <div class="promo-card-footer mt-auto">
                                                            <div class="row g-2">
                                                                <div class="col-6">
                                                                    <button class="btn btn-outline-custom w-100 detalles-promocion-btn" 
                                                                            data-promocion='<?php echo json_encode($promocion); ?>'
                                                                            data-local='<?php echo json_encode($local_data); ?>'>
                                                                        📋 Detalles
                                                                    </button>
                                                                </div>
                                                                <div class="col-6">
                                                                    <button class="btn btn-primary-custom w-100 usar-promocion-btn" 
                                                                            data-promocion-id="<?php echo $promocion['id_promocion']; ?>"
                                                                            data-local-id="<?php echo $local_id; ?>"
                                                                            data-descripcion="<?php echo htmlspecialchars($promocion['descripcion_promo']); ?>">
                                                                        🛒 Solicitar
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal de confirmación para uso -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">🎯 Confirmar Solicitud de Promoción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que quieres solicitar esta promoción?</p>
                    <p id="promo-details" class="text-muted small"></p>
                    <div class="alert alert-info">
                        <small>📝 <strong>Nota:</strong> Tu solicitud será enviada al local para su aprobación.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary-custom" id="confirmUseBtn">Sí, Solicitar Promoción</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de detalles de promoción -->
    <div class="modal fade" id="detallesModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">📋 Detalles de la Promoción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 id="detalle-titulo" class="text-primary-custom mb-3"></h4>
                            <div class="info-section">
                                <h6 class="text-muted mb-3">📊 Información de la Promoción</h6>
                                <div class="info-item">
                                    <span class="info-label">Descripción:</span>
                                    <span class="info-value" id="detalle-descripcion"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Categoría Requerida:</span>
                                    <span class="info-value">
                                        <span id="detalle-categoria" class="badge"></span>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Vigencia:</span>
                                    <span class="info-value" id="detalle-vigencia"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Días Aplicables:</span>
                                    <span class="info-value" id="detalle-dias"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">ID Promoción:</span>
                                    <span class="info-value" id="detalle-id"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-section">
                                <h6 class="text-muted mb-3">🏪 Información del Local</h6>
                                <div class="info-item">
                                    <span class="info-label">Nombre:</span>
                                    <span class="info-value" id="detalle-local-nombre"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Rubro:</span>
                                    <span class="info-value" id="detalle-local-rubro"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Ubicación:</span>
                                    <span class="info-value" id="detalle-local-ubicacion"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">ID Local:</span>
                                    <span class="info-value" id="detalle-local-id"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <small>
                                    <strong>💡 Nota:</strong> Esta promoción está disponible para tu categoría actual 
                                    (<span class="badge bg-primary"><?php echo ucfirst($categoria_usuario); ?></span>)
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary-custom" id="usarDesdeDetalles">Solicitar Esta Promoción</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedPromo = null;
        let selButton = null;
        let promoDetails = null;

        function showAlert(message, type = 'success') {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertContainer.appendChild(alert);
            setTimeout(() => { if (alert.parentNode) alert.remove(); }, 5000);
        }

        function usePromotion(promocionId, localId, button) {
            const formData = new FormData();
            formData.append('usar_promocion', 'true');
            formData.append('promocion_id', promocionId);
            formData.append('local_id', localId);

            if (button) {
                button.disabled = true;
                button.innerHTML = '⌛ Enviando...';
            }

            fetch('PromocionesCliente.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.ok || data.success) {
                    showAlert(data.message, 'success');
                    if (button) {
                        button.disabled = true;
                        button.innerHTML = '⏳ Pendiente';
                        button.classList.remove('btn-primary-custom');
                        button.classList.add('btn-pending');
                    }
                    const usarDesdeDetallesBtn = document.getElementById('usarDesdeDetalles');
                    if (usarDesdeDetallesBtn) {
                        usarDesdeDetallesBtn.disabled = true;
                        usarDesdeDetallesBtn.innerHTML = '⏳ Pendiente';
                        usarDesdeDetallesBtn.classList.remove('btn-primary-custom');
                        usarDesdeDetallesBtn.classList.add('btn-pending');
                    }
                    setTimeout(() => {
                        if (button && button.closest('.col-md-6, .col-lg-4')) {
                            button.closest('.col-md-6, .col-lg-4').style.display = 'none';
                        }
                    }, 2000);
                } else {
                    showAlert(data.message, 'danger');
                    if (button) {
                        button.disabled = false;
                        button.innerHTML = '🛒 Solicitar';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error al procesar la solicitud', 'danger');
                if (button) {
                    button.disabled = false;
                    button.innerHTML = '🛒 Solicitar';
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.usar-promocion-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const promocionId = this.getAttribute('data-promocion-id');
                    const localId = this.getAttribute('data-local-id');
                    const descripcion = this.getAttribute('data-descripcion');
                    selectedPromo = { promocionId, localId, descripcion };
                    selButton = this;
                    document.getElementById('promo-details').textContent = descripcion;
                    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
                    modal.show();
                });
            });

            document.querySelectorAll('.detalles-promocion-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const promocionData = JSON.parse(this.getAttribute('data-promocion'));
                    const localData = JSON.parse(this.getAttribute('data-local'));
                    promoDetails = promocionData;
                    document.getElementById('detalle-titulo').textContent = promocionData.descripcion_promo;
                    document.getElementById('detalle-descripcion').textContent = promocionData.descripcion_promo;
                    const categoriaBadge = document.getElementById('detalle-categoria');
                    categoriaBadge.textContent = promocionData.categoria_requerida;
                    categoriaBadge.className = `badge badge-${promocionData.categoria_requerida.toLowerCase()}`;
                    const desde = new Date(promocionData.fecha_desde).toLocaleDateString();
                    const hasta = new Date(promocionData.fecha_hasta).toLocaleDateString();
                    document.getElementById('detalle-vigencia').textContent = `Desde ${desde} hasta ${hasta}`;
                    const diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                    const diaTexto = promocionData.dia_promo ? diasSemana[promocionData.dia_promo - 1] : 'Todos los días';
                    document.getElementById('detalle-dias').textContent = diaTexto;
                    document.getElementById('detalle-id').textContent = promocionData.id_promocion;
                    document.getElementById('detalle-local-nombre').textContent = localData.nombre;
                    document.getElementById('detalle-local-rubro').textContent = localData.rubro;
                    document.getElementById('detalle-local-ubicacion').textContent = localData.ubicacion;
                    document.getElementById('detalle-local-id').textContent = promocionData.codigo_local;
                    const modal = new bootstrap.Modal(document.getElementById('detallesModal'));
                    modal.show();
                });
            });

            document.getElementById('confirmUseBtn').addEventListener('click', function() {
                if (selectedPromo && selButton) {
                    usePromotion(selectedPromo.promocionId, selectedPromo.localId, selButton);
                    const modal = bootstrap.Modal.getInstance(document.getElementById('confirmModal'));
                    modal.hide();
                }
            });

            document.getElementById('usarDesdeDetalles').addEventListener('click', function() {
                if (promoDetails) {
                    usePromotion(promoDetails.id_promocion, promoDetails.codigo_local, this);
                    const modal = bootstrap.Modal.getInstance(document.getElementById('detallesModal'));
                    modal.hide();
                }
            });

            document.getElementById('searchForm')?.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Buscando...';
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Buscar Promociones';
                }, 2000);
            });
        });
    </script>
    <script src="../layouts/JS/cambiarNombre.js"></script>
</body>
</html>