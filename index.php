<?php
session_start();
   
include_once(__DIR__ . '/Model/EventosModel.php');

$eventosModel = new EventosModel();
$eventosDestacados = $eventosModel->getEventosDestacados(3);
$totalEventos = $eventosModel->getTotalEventosActivos();

$SHOPPING_NAME = "Shopping UTN";

if (isset($_SESSION['IDusuario'])): ?>
<script src="./layouts/JS/cambiarNombre.js"></script>
<?php endif; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Descubrí las mejores tiendas y ofertas en nuestro shopping">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="./layouts/css/index.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Inicio - <?= htmlspecialchars($SHOPPING_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        :root {
            --primary: #4A3BC7;
            --primary-rgb: 74, 59, 199;
            --subtle: #F3F1FF;
            --primary-light: #6B5DD3;
            --primary-dark: #3A2DA5;
            --primary-very-light: #F8F7FF;
            --primary-gradient: linear-gradient(135deg, #4A3BC7 0%, #6B5DD3 100%);
        }
        
        .hero{
            background: linear-gradient(180deg, var(--primary) 0%, #2A2668 100%);
            padding: 4rem 0;
        }
        .hero-search .form-control { 
            border-top-left-radius: 50rem; 
            border-bottom-left-radius: 50rem; 
        }
        .hero-search .btn { 
            border-top-right-radius: 50rem; 
            border-bottom-right-radius: 50rem; 
        }
        .event-card {
            transition: all 0.3s ease;
            border: 1px solid var(--bs-border-color);
        }
        .event-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 0.5rem 1rem rgba(var(--primary-rgb), 0.15);
            border-color: var(--primary);
        }
        /* FIX: Corrección para promociones destacadas */
        .promo-top {
            height: 44px;
            background: var(--primary);
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
            position: relative;
        }
        .badge-discount {
            background: rgba(var(--primary-rgb), 0.15);
            color: var(--primary);
            font-weight: 600;
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
        }
        /* FIX: Asegurar que el contenido de la card sea visible */
        .promo-card .card-body {
            padding: 1rem;
            background: white;
        }
    </style>
</head>

<body>
<?php include __DIR__ . '/layouts/Navbar.php'; ?>

<!-- Hero Section -->
<section class="hero text-white">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-5 fw-bold mb-4">Descubrí las mejores<br>tiendas y ofertas</h1>
                <form class="input-group input-group-lg hero-search" role="search" action="/buscar" method="get">
                    <input class="form-control border-0" name="q" type="search" placeholder="Buscar tiendas, productos...">
                    <button class="btn btn-light text-primary border-0" type="submit">
                        <i class="bi bi-search me-2"></i>Buscar
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<main class="container my-5">

    <!-- Promociones Destacadas -->
    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Promociones destacadas</h2>
            <a href="./View/promociones.php" class="btn btn-outline-primary btn-sm">Ver todas</a>
        </div>
        <div class="row g-3">
            <?php 
            $promociones = [
                [
                    'nombre' => 'Moda Chic',
                    'descripcion' => 'Indumentaria y accesorios.',
                    'descuento' => '30% OFF'
                ],
                [
                    'nombre' => 'ElectroHogar',
                    'descripcion' => 'Electrónica seleccionada.',
                    'descuento' => '30% OFF'
                ],
                [
                    'nombre' => 'Calzado Urbano',
                    'descripcion' => 'Calzado y urban style.',
                    'descuento' => '25% OFF'
                ],
                [
                    'nombre' => 'Deportes',
                    'descripcion' => 'Indumentaria deportiva.',
                    'descuento' => '40% OFF'
                ]
            ];
            ?>
            
            <?php foreach($promociones as $promo): ?>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card promo-card shadow-sm h-100 border-0">
                    <!-- Header con descuento -->
                    <div class="promo-top">
                        <span class="badge badge-discount position-absolute top-50 start-0 translate-middle-y ms-3">
                            <?= $promo['descuento'] ?>
                        </span>
                    </div>
                    <!-- Contenido de la promoción -->
                    <div class="card-body d-flex flex-column">
                        <h3 class="h6 card-title mb-2 text-dark"><?= $promo['nombre'] ?></h3>
                        <p class="card-text small text-secondary flex-grow-1"><?= $promo['descripcion'] ?></p>
                        <div class="mt-auto">
                            <small class="text-primary fw-semibold">
                                Ver promoción <i class="bi bi-arrow-right ms-1"></i>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Categorías - CON VARIACIONES DE LA PALETA PRINCIPAL -->
    <section class="mb-5">
        <h2 class="h4 mb-4">Categorías</h2>
        <div class="row g-3">
            <?php 
            $categorias = [
                [
                    'icon' => 'fas fa-tshirt', 
                    'nombre' => 'Ropa',
                    'variante' => 'primary-light', // #6B5DD3
                    'gradiente' => 'linear-gradient(135deg, #6B5DD3 0%, #7D6DDB 100%)'
                ],
                [
                    'icon' => 'bi-tv', 
                    'nombre' => 'Electrodomésticos',
                    'variante' => 'primary', // #4A3BC7
                    'gradiente' => 'linear-gradient(135deg, #4A3BC7 0%, #5A4BCF 100%)'
                ],
                [
                    'icon' => 'bi-house-door', 
                    'nombre' => 'Hogar',
                    'variante' => 'primary-dark', // #3A2DA5
                    'gradiente' => 'linear-gradient(135deg, #3A2DA5 0%, #4A3BC7 100%)'
                ],
                [
                    'icon' => 'fas fa-medal', 
                    'nombre' => 'Deportes',
                    'variante' => 'primary-gradient', // Gradiente completo
                    'gradiente' => 'linear-gradient(135deg, #4A3BC7 0%, #6B5DD3 50%, #3A2DA5 100%)'
                ]
            ];
            ?>
            
            <?php foreach($categorias as $categoria): ?>
            <div class="col-6 col-md-3">
                <a class="card category-card text-center text-decoration-none h-100 border-0"
                   href="#" 
                   style="background: <?= $categoria['gradiente'] ?>; transition: all 0.3s ease;">
                    <div class="card-body py-4 position-relative text-white">
                        <!-- Efecto hover overlay -->
                        <div class="category-hover position-absolute top-0 start-0 w-100 h-100 bg-white opacity-0 rounded" 
                             style="transition: opacity 0.3s ease;"></div>
                        
                        <div class="position-relative z-1">
                            <i class="<?= $categoria['icon'] ?> fs-2 d-block mb-2 text-white"></i>
                            <span class="fw-semibold"><?= $categoria['nombre'] ?></span>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Eventos Destacados -->
    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Eventos destacados</h2>
            <?php if($totalEventos > 3): ?>
            <a href="./View/Novedades.php" class="btn btn-outline-primary btn-sm">
                Ver todos los eventos (<?= $totalEventos ?>)
            </a>
            <?php endif; ?>
        </div>
        
        <div class="row g-3">
            <?php if(!empty($eventosDestacados)): ?>
                <?php foreach($eventosDestacados as $evento): ?>
                <div class="col-md-6 col-lg-4">
                    <a href="./View/Novedades.php#evento-<?= $evento['id'] ?>" 
                       class="card event-card text-decoration-none h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-primary bg-opacity-10 text-primary small">
                                    <?= htmlspecialchars($evento['categoria'] ?? 'General') ?>
                                </span>
                                <small class="text-muted">
                                    <?= date('d/m', strtotime($evento['desde'])) ?> - <?= date('d/m', strtotime($evento['hasta'])) ?>
                                </small>
                            </div>
                            
                            <h3 class="h6 mb-2 fw-semibold text-dark">
                                <?= htmlspecialchars($evento['titulo']) ?>
                            </h3>
                            <p class="small text-secondary mb-0">
                                <?= htmlspecialchars(mb_substr($evento['descripcion'], 0, 100)) ?><?= strlen($evento['descripcion']) > 100 ? '...' : '' ?>
                            </p>
                            
                            <div class="mt-3 pt-2 border-top">
                                <small class="text-primary fw-semibold">
                                    Ver detalles <i class="bi bi-arrow-right ms-1"></i>
                                </small>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="card border-dashed bg-light">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-calendar-x fs-1 text-muted mb-3"></i>
                            <h3 class="h5 text-muted">No hay eventos activos</h3>
                            <p class="text-muted mb-0">Próximamente tendremos nuevos eventos para vos.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Mapa de Tiendas -->
    <section class="mb-5">
        <h2 class="h4 mb-3">Nuestras tiendas</h2>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m17!1m12!1m3!1d3272.5384890332753!2d-57.98214252424715!3d-34.89293367285195!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m2!1m1!2zMzTCsDUzJzM0LjYiUyA1N8KwNTgnNDYuNCJX!5e0!3m2!1ses!2sar!4v1757898954412!5m2!1ses!2sar" 
                    class="w-100" 
                    style="height: 400px; border: 0;" 
                    allowfullscreen 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Ubicación de nuestro shopping">
                </iframe>
            </div>
        </div>
    </section>

    <!-- CTA Locatarios -->
    <section class="mb-5">
        <div class="bg-primary bg-opacity-10 rounded-3 p-4 p-md-5 text-center">
            <h2 class="display-6 fw-bold text-primary mb-3">
                Abrí tu local en <?= htmlspecialchars($SHOPPING_NAME, ENT_QUOTES, 'UTF-8') ?>
            </h2>
            <p class="lead mb-4 text-dark opacity-75">
                Alto flujo de visitas. Contratos flexibles. Ubicaciones premium.
            </p>
            
            <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                <a class="btn btn-primary btn-lg px-4"
                   href="./View/CrearTienda.php"
                   aria-label="Ir al formulario para abrir un local">
                    <i class="bi bi-shop me-2"></i>Quiero abrir un local
                </a>
                
               <a class="btn btn-outline-dark btn-lg"
   href="data:text/plain;charset=utf-8,Hola%20mundo%20%E2%9C%8F%EF%B8%8F" 
   download="dossier_pendiente.txt"
   aria-label="Descargar dossier comercial para locatarios">

                       <i class="bi bi-download me-2"></i>Pautas para abrir una tienda

</a>
            </div>
            
            <div class="mt-3 small text-muted">
                <i class="bi bi-info-circle me-1"></i>Espacios desde XX m². Respuesta en 24 h.
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/layouts/footer.php'; ?>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Efecto hover mejorado para categorías
document.addEventListener('DOMContentLoaded', function() {
    const categoryCards = document.querySelectorAll('.category-card');
    
    categoryCards.forEach(card => {
        const hoverEffect = card.querySelector('.category-hover');
        
        card.addEventListener('mouseenter', function() {
            hoverEffect.style.opacity = '0.1';
            card.style.transform = 'translateY(-6px) scale(1.02)';
            card.style.boxShadow = '0 12px 30px rgba(74, 59, 199, 0.25)';
        });
        
        card.addEventListener('mouseleave', function() {
            hoverEffect.style.opacity = '0';
            card.style.transform = 'translateY(0) scale(1)';
            card.style.boxShadow = 'none';
        });
    });
});
</script>


</body>
</html>