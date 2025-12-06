

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css" rel="stylesheet">
    <link href="View/css/EstiloLogin.css" rel="stylesheet">
</head>
<body style="background-color: #eeecfd !important;">
    <?php 
    include dirname(__DIR__) . '/layouts/Navbar.php';
    ?>
     
  <section class="container py-5 " >
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-7 col-xl-6">
      <div class="card shadow-sm">
        <div class="card-body p-5">
          <h1 class="h3 mb-4 text-center">Iniciar sesión</h1>

          <form action="../Controller/LogInController.php" method="post" autocomplete="on">
            <div class="form-floating mb-3">
              <input id="email" name="mail" type="email"
                     class="form-control form-control-lg" placeholder=" "
                     autocomplete="username" required>
              <label for="email" class="fs-6">Email</label>
            </div>

            <!-- Campo de contraseña con toggle -->
            <div class="form-floating mb-3 position-relative">
              <input id="password" name="password" type="password"
                     class="form-control form-control-lg pe-5" placeholder=" "
                     autocomplete="current-password" required>
              <label for="password" class="fs-6">Contraseña</label>
              <!-- Botón del ojito -->
              <button type="button" class="btn btn-link position-absolute top-50 end-0 translate-middle-y text-secondary p-0 me-3"
                      id="togglePassword" aria-label="Mostrar u ocultar contraseña">
                <i class="bi bi-eye" id="passwordIcon"></i>
              </button>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                <label class="form-check-label" for="remember">Recordarme</label>
              </div>
              <a class="small" href="recuperar.php">¿Olvidaste tu contraseña?</a>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100">Ingresar</button>
          </form>

          <div class="text-center mt-3 small">
            ¿No tenés cuenta? <a href="../View/signIn.php">Registrate</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

  <?php include dirname(__DIR__) . '/layouts/footer.php'; ?>
  
<script src="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.js"></script>

<script>
// Script para mostrar/ocultar contraseña
document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const passwordIcon = document.getElementById('passwordIcon');
    
    if (togglePassword && passwordInput && passwordIcon) {
        togglePassword.addEventListener('click', function() {
            // Cambiar el tipo de input
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Cambiar el icono
            if (type === 'text') {
                passwordIcon.classList.remove('bi-eye');
                passwordIcon.classList.add('bi-eye-slash');
                togglePassword.setAttribute('aria-label', 'Ocultar contraseña');
            } else {
                passwordIcon.classList.remove('bi-eye-slash');
                passwordIcon.classList.add('bi-eye');
                togglePassword.setAttribute('aria-label', 'Mostrar contraseña');
            }
        });
        
        // Mejorar accesibilidad - permitir activar con espacio/enter
        togglePassword.addEventListener('keydown', function(e) {
            if (e.key === ' ' || e.key === 'Enter' || e.key === 'Spacebar') {
                e.preventDefault();
                togglePassword.click();
            }
        });
    }
});
</script>

<script src="./js/login_Fetch.js"></script>
<script src="https://kit.fontawesome.com/accf4898f4.js" crossorigin="anonymous"></script>  
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>


<?php if (isset($_SESSION['IDusuario'])): ?>
    
    <script src="../layouts/JS/cambiarNombre.js"></script>

<?php endif; ?>
</body>
</html>