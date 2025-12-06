<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="./css/login.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            z-index: 10;
        }
        .form-floating.position-relative .form-control {
            padding-right: 45px;
        }
    </style>
</head>
<body style="background-color: #eeecfd !important;">
   
  <?php include '../layouts/Navbar.php'; ?>
     
<section class="container py-5 ">
  <div class="row justify-content-center  ">
    <div class="col-12 col-lg-8 col-xl-7 card card-body p-3">
      <h1 class="h4 mb-4">Registro</h1>

      <form id="regForm" class="needs-validation " action="../Controller/signInController.php" method="post" novalidate>
        <div class="row g-3">
          <div class="col-md-6">
            <div class="form-floating">
              <input id="nombre" name="nombre" type="text" class="form-control" placeholder=" " required>
              <label for="nombre">Nombre</label>
              <div class="invalid-feedback">Nombre inválido.</div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-floating">
              <input id="apellido" name="apellido" type="text" class="form-control" placeholder=" " required>
              <label for="apellido">Apellido</label>
              <div class="invalid-feedback">Apellido inválido.</div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-floating">
              <input id="email" name="email" type="email" class="form-control" placeholder=" " required>
              <label for="email">Email</label>
              <div class="invalid-feedback">Email inválido.</div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-floating">
              <input id="email2" name="email2" type="email" class="form-control" placeholder=" " required>
              <label for="email2">Repetir email</label>
              <div class="invalid-feedback">Los emails no coinciden.</div>
            </div>
          </div>

          <!-- Campo de contraseña con ojito -->
          <div class="col-md-6">
            <div class="form-floating position-relative">
              <input id="password" name="password" type="password" class="form-control" placeholder=" " required>
              <label for="password">Contraseña</label>
              <button type="button" class="password-toggle" id="togglePassword">
                <i class="bi bi-eye"></i>
              </button>
              <div class="invalid-feedback">Mínimo 8, con letra y número.</div>
            </div>
          </div>

          <!-- Campo de repetir contraseña con ojito -->
          <div class="col-md-6">
            <div class="form-floating position-relative">
              <input id="password2" name="password2" type="password" class="form-control" placeholder=" " required>
              <label for="password2">Repetir contraseña</label>
              <button type="button" class="password-toggle" id="togglePassword2">
                <i class="bi bi-eye"></i>
              </button>
              <div class="invalid-feedback">Las contraseñas no coinciden.</div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-floating">
              <input id="dni" name="dni" type="text" class="form-control" placeholder=" " inputmode="numeric" maxlength="8" required>
              <label for="dni">DNI (8 dígitos)</label>
              <div class="invalid-feedback">Debe tener 8 dígitos.</div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-floating">
              <select id="sexo" name="sexo" class="form-select" required>
                <option value="" selected disabled>Seleccione…</option>
                <option>Femenino</option>
                <option>Masculino</option>
                <option>Otro</option>
              </select>
              <label for="sexo">Sexo</label>
              <div class="invalid-feedback">Seleccione una opción.</div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-floating">
              <input id="telefono" name="telefono" type="tel" class="form-control" placeholder=" " required>
              <label for="telefono">Teléfono</label>
              <div class="invalid-feedback">Teléfono inválido.</div>
            </div>
          </div>
        </div>

        <div class="d-grid d-sm-flex gap-2 justify-content-end mt-4">
          <button id="btnReset" type="reset" class="btn btn-outline-secondary">Limpiar</button>
          <button id="btnEnviar" type="submit" class="btn btn-primary">Crear cuenta</button>
        </div>

        <div id="formMsg" class="mt-3 small"></div>
      </form>
    </div>
  </div>
</section>

<?php include dirname(__DIR__) . '/layouts/footer.php'; ?>

<script>
// Script para mostrar/ocultar contraseña
document.addEventListener('DOMContentLoaded', function() {
    // Función para alternar la visibilidad de la contraseña
    function setupPasswordToggle(toggleButtonId, passwordInputId) {
        const toggleButton = document.getElementById(toggleButtonId);
        const passwordInput = document.getElementById(passwordInputId);
        const icon = toggleButton.querySelector('i');
        
        if (toggleButton && passwordInput) {
            toggleButton.addEventListener('click', function() {
                // Cambiar el tipo de input
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Cambiar el icono
                if (type === 'text') {
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                    toggleButton.setAttribute('aria-label', 'Ocultar contraseña');
                } else {
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                    toggleButton.setAttribute('aria-label', 'Mostrar contraseña');
                }
            });
            
            // Mejorar accesibilidad - permitir activar con espacio/enter
            toggleButton.addEventListener('keydown', function(e) {
                if (e.key === ' ' || e.key === 'Enter' || e.key === 'Spacebar') {
                    e.preventDefault();
                    toggleButton.click();
                }
            });
        }
    }
    
    // Configurar ambos campos de contraseña
    setupPasswordToggle('togglePassword', 'password');
    setupPasswordToggle('togglePassword2', 'password2');
});

// Limpiar campos al hacer reset
document.getElementById('btnReset')?.addEventListener('click', function() {
    // Restaurar los ojitos a su estado original
    const toggleButtons = document.querySelectorAll('.password-toggle');
    toggleButtons.forEach(button => {
        const icon = button.querySelector('i');
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    });
    
    const passwordInputs = document.querySelectorAll('input[type="password"], input[type="text"]');
    passwordInputs.forEach(input => {
        if (input.id === 'password' || input.id === 'password2') {
            input.setAttribute('type', 'password');
        }
    });
});
</script>

<script src="./js/signIn_validaciones.js" defer></script>
<script src="./js/SignIn_Fetch.js" defer></script>
<script src="https://kit.fontawesome.com/accf4898f4.js" crossorigin="anonymous"></script>  
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script src="../layouts/JS/OcultarBoton.js"></script>

</body>
</html>