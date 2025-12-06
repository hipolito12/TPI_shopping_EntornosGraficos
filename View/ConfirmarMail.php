<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cuenta habilitada</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
    body {
      background: linear-gradient(135deg, #9a95caff 0%, #7784e9ff 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Inter', sans-serif;
    }
    .card {
      border: none;
      border-radius: 1.5rem;
      box-shadow: 0 8px 32px rgba(0,0,0,0.12);
      animation: fadeInDown 0.8s ease-out;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
    }
    @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-30px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .icon-success {
      font-size: 5rem;
      color: #198754;
      animation: bounce 1.5s ease-in-out;
      margin-bottom: 1rem;
    }
    @keyframes bounce {
      0%, 100% { transform: scale(1); }
      30% { transform: scale(1.15); }
      60% { transform: scale(1.05); }
    }
    .email-display {
      background: linear-gradient(135deg, #4A3BC7 0%, #6a5bde 100%);
      color: white;
      padding: 1.25rem;
      border-radius: 1rem;
      font-size: 1.2rem;
      margin: 1.5rem 0;
      box-shadow: 0 6px 20px rgba(74, 59, 199, 0.25);
      border: 2px solid rgba(255, 255, 255, 0.2);
      position: relative;
      overflow: hidden;
    }
    .email-display::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: left 0.5s;
    }
    .email-display:hover::before {
      left: 100%;
    }
    .btn-success {
      background: linear-gradient(135deg, #198754 0%, #20c997 100%);
      border: none;
      border-radius: 0.75rem;
      padding: 0.75rem 2rem;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(25, 135, 84, 0.3);
    }
    .btn-success:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(25, 135, 84, 0.4);
    }
    .brand-highlight {
      color: #4A3BC7;
      font-weight: 700;
    }
    .subtitle {
      color: #6c757d;
      font-size: 1.1rem;
      line-height: 1.6;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-6">
        <div class="card p-5 text-center">
          <div class="mb-4">
            <i class="fa-solid fa-circle-check icon-success"></i>
          </div>
          
          <h1 class="mb-3 fw-bold" style="color: #2d3748;">¡Cuenta Habilitada!</h1>
          
          <?php
          // Capturar y sanitizar el email de la URL
          $email = '';
          if (isset($_GET['mail'])) {
              $email = htmlspecialchars(urldecode($_GET['mail']));
          }
          
          if (!empty($email)): ?>
            <div class="email-display animate__animated animate__fadeInUp">
              <i class="fa-solid fa-envelope-circle-check me-2"></i>
              <strong><?php echo $email; ?></strong>
            </div>
          <?php endif; ?>
          
          <div class="subtitle mb-4">
            <?php if (!empty($email)): ?>
              <p class="mb-3">La cuenta <strong class="brand-highlight"><?php echo $email; ?></strong> ha sido verificada y activada exitosamente.</p>
            <?php else: ?>
              <p class="mb-3">Tu cuenta ha sido verificada y activada de forma satisfactoria.</p>
            <?php endif; ?>
            
            <p class="mb-0">Ahora puedes acceder a todas las funcionalidades de <span class="brand-highlight">SHOPPING GENERICO</span> y disfrutar de nuestras promociones exclusivas.</p>
          </div>
          
          <div class="d-grid gap-2 d-md-flex justify-content-md-center">
            <a href="../index.php" class="btn btn-success btn-lg px-4 me-md-2">
              <i class="fa-solid fa-arrow-right-to-bracket me-2"></i>Iniciar Sesión
            </a>
            <a href="../View/signIn.php" class="btn btn-outline-primary btn-lg px-4">
              <i class="fa-solid fa-user-plus me-2"></i>Crear Cuenta
            </a>
          </div>
          
          <div class="mt-4 pt-3 border-top">
            <small class="text-muted">
              <i class="fa-solid fa-shield-check me-1"></i>
              Tu cuenta está segura y protegida
            </small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Efecto adicional de confeti visual
    document.addEventListener('DOMContentLoaded', function() {
      const icon = document.querySelector('.icon-success');
      if (icon) {
        setTimeout(() => {
          icon.style.transform = 'rotate(360deg)';
          icon.style.transition = 'transform 0.8s ease';
        }, 1000);
      }
      
      // Mostrar mensaje en consola para desarrollo
      <?php if (!empty($email)): ?>
        console.log('✅ Cuenta verificada: <?php echo $email; ?>');
      <?php endif; ?>
    });
  </script>
</body>
</html>