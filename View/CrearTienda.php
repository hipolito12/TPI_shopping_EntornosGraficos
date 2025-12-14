<?php
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="página que simula el funcionamiento de un shopping.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/layouts/css/index.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        .btn-primary.btn-lg {
            transition: background 0.3s, transform 0.4s, box-shadow 0.2s;
        }

        .btn-primary.btn-lg:hover,
        .btn-primary.btn-lg:focus {
            background: linear-gradient(90deg, #0d6efd 60%, #4f8cff 100%);
            transform: translateY(-2px) scale(1.04);
            box-shadow: 0 6px 24px rgba(13, 110, 253, 0.18);
        }
        
        .password-input-group {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #6c757d;
            cursor: pointer;
            padding: 5px;
            z-index: 3;
        }
        
        .password-toggle:hover {
            color: #495057;
        }
        
        .password-toggle:focus {
            outline: none;
            box-shadow: none;
        }
        
        .password-field {
            padding-right: 40px !important;
        }
        
        .required:after {
            content: " *";
            color: #dc3545;
        }
        
        body {
            background-color: #eeecfd;
        }
        
        .card {
            border-radius: 12px;
            border: none;
        }
        
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            border-radius: 12px 12px 0 0 !important;
            padding: 1.25rem 1.5rem;
        }
    </style>
    <title>Crear nueva tienda</title>
</head>

<body>
    <?php
    include '../layouts/Navbar.php';
    include "../Model/ProcesarTienda.php"; 
    $ubicaciones = getUbicaciones();
    ?>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-9 col-xl-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h1 class="h4 mb-0">Crear tienda</h1>
                    </div>
                    <div class="card-body">

                        <div id="server-messages" class="mb-3" aria-live="polite"></div>

                        <form id="form-crear-tienda"
                            action="../Controller/CrearTiendaController.php"
                            method="post"
                            autocomplete="off"
                            novalidate>

                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label for="nombre" class="form-label required">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" autocomplete="given-name" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6">
                                    <label for="apellido" class="form-label required">Apellido</label>
                                    <input type="text" class="form-control" id="apellido" name="apellido" autocomplete="family-name" required>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label required">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" autocomplete="email" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                                
                                <!-- Campo de contraseña con ojito -->
                                <div class="col-md-6">
                                    <label for="password" class="form-label required">Contraseña</label>
                                    <div class="password-input-group">
                                        <input type="password" class="form-control password-field" id="password" name="password" autocomplete="new-password" required>
                                        <button type="button" class="password-toggle" id="togglePassword">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Mínimo 8 caracteres, al menos 1 mayúscula y 1 número.</div>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" autocomplete="tel">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6">
                                    <label for="sexo" class="form-label required">Sexo</label>
                                    <select id="sexo" name="sexo" class="form-select" required>
                                        <option value="" selected disabled>Seleccioná una opción</option>
                                        <option value="Femenino">Femenino</option>
                                        <option value="Masculino">Masculino</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="dni" class="form-label required">DNI</label>
                                    <input type="text" class="form-control" id="dni" name="dni" inputmode="numeric" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6">
                                    <label for="cuil" class="form-label required">CUIL</label>
                                    <input type="text" class="form-control" id="cuil" name="cuil" inputmode="numeric" required>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="rubro" class="form-label required">Rubro</label>
                                    <input type="text" class="form-control" id="rubro" name="rubro" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6">
                                    <label for="nombre_local" class="form-label required">Nombre del local</label>
                                    <input type="text" class="form-control" id="nombre_local" name="nombre_local" required>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="ubicacion" class="form-label required">Ubicación / Lugar</label>
                                        <select id="ubicacion" name="ubicacion" class="form-select" required>
                                            <option value="" selected disabled>Seleccione una ubicación</option>

                                            <?php if (!empty($ubicaciones)): ?>
                                                <?php foreach ($ubicaciones as $u): ?>
                                                    <option value="<?= htmlspecialchars($u['IDubicacion']) ?>">
                                                        <?= htmlspecialchars($u['nombre']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <!-- Si no hay registros, dejamos solo la opción vacía -->
                                            <?php endif; ?>

                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">Crear tienda</button>
                                <a href="javascript:history.back()" class="btn btn-outline-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>

                <p class="text-muted small mt-3">
                    Los campos marcados con <span class="text-danger">*</span> son obligatorios.
                </p>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script para el ojito de contraseña -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordField = document.getElementById('password');
            
            if (togglePassword && passwordField) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordField.setAttribute('type', type);
                    
                    // Cambiar el icono
                    const icon = this.querySelector('i');
                    if (type === 'password') {
                        icon.classList.remove('bi-eye-slash');
                        icon.classList.add('bi-eye');
                    } else {
                        icon.classList.remove('bi-eye');
                        icon.classList.add('bi-eye-slash');
                    }
                });
            }
        });
    </script>

    <!-- Tus scripts existentes -->
    <script defer src="./js/TIenda_validacion.js"></script>
    <script defer src="./js/Tienda_Fetch.js"></script>
    
    <?php include '../layouts/footer.php'; ?>
</body>

</html>