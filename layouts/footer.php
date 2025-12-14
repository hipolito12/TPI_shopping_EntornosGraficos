<?php
$docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : '';
$appRoot = realpath(dirname(__DIR__)); 

$docRoot = $docRoot ? str_replace('\\', '/', $docRoot) : '';
$appRoot = $appRoot ? str_replace('\\', '/', $appRoot) : '';

$rel = (strpos($appRoot, $docRoot) === 0) ? substr($appRoot, strlen($docRoot)) : '';
$rel = trim($rel, '/');

$BASE = ($rel === '') ? '/' : '/' . $rel . '/'; 

if (!empty($_ENV['BASE_URI']))  { $BASE = rtrim($_ENV['BASE_URI'],  '/').'/'; }
if (!empty($_SERVER['BASE_URI'])){ $BASE = rtrim($_SERVER['BASE_URI'],'/').'/'; }
?>

<style>
    <?php include dirname(__DIR__) . '/layouts/css/footer.css'; ?>

    /* Estilos footer */
    .footer-links a {
        transition: all 0.3s ease;
        display: block;
        padding: 4px 0;
        text-decoration: none;
        color: rgba(255, 255, 255, 0.8);
    }

    .footer-links a:hover {
        color: #ffffff !important;
        transform: translateX(8px);
    }

    .footer-section h6 {
        font-weight: 600;
        margin-bottom: 1rem;
        position: relative;
        color: #fff;
    }

    .footer-section h6::after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 0;
        width: 30px;
        height: 2px;
        background: #4A3BC7; /* Tu color primario */
    }

    .footer-links li {
        margin-bottom: 6px;
    }

    .footer-links a:focus {
        outline: 2px solid #4A3BC7;
        outline-offset: 2px;
        border-radius: 3px;
    }

    @media (max-width: 768px) {
        .footer-section {
            margin-bottom: 1.5rem;
        }
        .footer-links a {
            padding: 6px 0;
        }
    }
</style>

<footer class="site-footer mt-auto text-white py-2" style="background-color: #1a1a1a;">
  <div class="container py-4">
    <div class="row g-4 justify-content-between">
      
      <div class="col-12 col-md-4 footer-section">
        <h6>Explorar</h6>
        <ul class="list-unstyled small footer-links">
          <li><a href="<?= $BASE ?>index.php" aria-label="Ir a inicio"><i class="bi bi-house-door me-2"></i>Inicio</a></li>
          <li><a href="<?= $BASE ?>View/promociones.php"><i class="bi bi-tag me-2"></i>Promociones</a></li>
          <li><a href="<?= $BASE ?>View/Tienda.php"><i class="bi bi-shop me-2"></i>Tiendas</a></li>
          <li><a href="<?= $BASE ?>View/Novedades.php"><i class="bi bi-newspaper me-2"></i>Novedades</a></li>
          <li><a href="<?= $BASE ?>View/Comer.html"><i class="bi bi-cup-hot me-2"></i>Patio de Comidas</a></li>
        </ul>
      </div>

      <div class="col-12 col-md-4 footer-section">
        <h6>Mi Cuenta</h6>
        <ul class="list-unstyled small footer-links">
          <li><a href="<?= $BASE ?>View/login.php"><i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión</a></li>
          <li><a href="<?= $BASE ?>View/signIn.php"><i class="bi bi-person-plus me-2"></i>Registrarse</a></li>
          </ul>
      </div>

      <div class="col-12 col-md-4 footer-section">
        <h6>Ayuda</h6>
        <ul class="list-unstyled small footer-links">
          <li><a href="<?= $BASE ?>View/Contacto.php"><i class="bi bi-envelope-paper me-2"></i>Formulario de Contacto</a></li>
          <li><a href="mailto:admin@shoppinggenerico.com"><i class="bi bi-envelope me-2"></i>Email Soporte</a></li>
        </ul>
      </div>

    </div>

    <div class="row mt-4 pt-3 border-top border-secondary">
      <div class="col-12 col-lg-6">
        <h6 class="text-white mb-3" style="font-size: 0.9rem;">Seguinos en redes</h6>
        <div class="d-flex gap-3 fs-5">
          <a href="#" class="text-white-50 hover-white" aria-label="Twitter"><i class="bi bi-twitter"></i></a>
          <a href="#" class="text-white-50 hover-white" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
          <a href="#" class="text-white-50 hover-white" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
          <a href="#" class="text-white-50 hover-white" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>
        </div>
      </div>
      
      <div class="col-12 col-lg-6 mt-3 mt-lg-0 text-lg-end">
        <div class="d-flex align-items-center justify-content-lg-end gap-2 text-white-50">
          <i class="bi bi-envelope"></i>
          <span style="font-size: 0.875rem;">admin@shoppinggenerico.com</span>
        </div>
        <p class="small text-white-50 mb-0 mt-2">
          &copy; 2025 Shopping UTN. Todos los derechos reservados.
        </p>
      </div>
    </div>
  </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const footerLinks = document.querySelectorAll('.footer-links a');
    
    footerLinks.forEach(link => {
        link.addEventListener('touchstart', function() {
            this.style.transform = 'translateX(4px)';
        });
        
        link.addEventListener('touchend', function() {
            this.style.transform = '';
        });
    });
});

window.addEventListener("pageshow", function(event) {
    var historyTraversal = event.persisted || 
                           (typeof window.performance != "undefined" && 
                            window.performance.navigation.type === 2);
    
    if (historyTraversal) window.location.reload();
});
</script>