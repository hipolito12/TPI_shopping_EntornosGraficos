<?php
session_start();
require_once '../Model/ContactoModel.php'; // Incluimos el modelo

$mensajeEnviado = false;
$errorEnvio = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Obtener y limpiar datos
    $asunto = trim($_POST['asunto'] ?? '');
    $problema = trim($_POST['problema'] ?? ''); // Este es el 'cuerpo'

    if (!empty($asunto) && !empty($problema)) {
        // 2. Intentar guardar en la BD
        if (registrarContacto($asunto, $problema)) {
            $mensajeEnviado = true;
        } else {
            $errorEnvio = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - ShoppingUTN</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        :root {
            var(--primary): #4A3BC7;
            --primary-rgb: 74, 59, 199;
            --subtle: #F3F1FF;
        }

        body {
            background-color: var(--subtle);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .btn-primary-custom {
            background-color: #4A3BC7; /* Hardcoded por si la variable falla */
            border-color: #4A3BC7;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-primary-custom:hover {
            background-color: #3A2BA7;
            border-color: #3A2BA7;
            transform: translateY(-1px);
        }

        .card-contacto {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            background: white;
        }

        .form-control:focus {
            border-color: #4A3BC7;
            box-shadow: 0 0 0 0.25rem rgba(74, 59, 199, 0.25);
        }
        
        .contact-icon {
            font-size: 3rem;
            color: #4A3BC7;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>

    <?php include __DIR__ . '/../layouts/Navbar.php'; ?>

    <main class="container py-5 flex-grow-1">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                
                <?php if ($mensajeEnviado): ?>
                    <div class="alert alert-success text-center shadow-sm border-0" role="alert">
                        <h4 class="alert-heading"><i class="bi bi-check-circle-fill me-2"></i>¡Consulta Registrada!</h4>
                        <p>Hemos guardado tu problema en nuestro sistema. Un administrador lo revisará pronto.</p>
                        <hr>
                        <a href="../index.php" class="btn btn-success btn-sm">Volver al Inicio</a>
                    </div>
                
                <?php else: ?>

                    <?php if ($errorEnvio): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Error:</strong> No se pudo guardar tu consulta. Inténtalo de nuevo.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card card-contacto p-4 p-md-5">
                        <div class="text-center mb-4">
                            <div class="contact-icon">
                                <i class="bi bi-envelope-paper"></i>
                            </div>
                            <h1 class="h3 fw-bold mb-2">Contacto y Soporte</h1>
                            <p class="text-muted">Cuéntanos qué problema tienes y te ayudaremos.</p>
                        </div>

                        <form action="" method="POST">
                            <div class="mb-4">
                                <label for="asunto" class="form-label fw-medium">Asunto</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-tag"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0 bg-light" id="asunto" name="asunto" placeholder="Ej: Problema con una compra" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="problema" class="form-label fw-medium">Descripción del Problema</label>
                                <textarea class="form-control bg-light" id="problema" name="problema" rows="5" placeholder="Describe detalladamente tu inconveniente..." required></textarea>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-between align-items-center mt-5">
                                <a href="../index.php" class="btn btn-outline-secondary px-4">
                                    <i class="bi bi-arrow-left me-2"></i>Volver
                                </a>

                                <button type="submit" class="btn btn-primary-custom px-5 py-2 fw-semibold">
                                    Enviar Mensaje <i class="bi bi-send ms-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>