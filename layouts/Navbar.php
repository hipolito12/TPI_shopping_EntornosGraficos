<?php
  $BASE = '/TPIShopping/';

  // Verificación de sesión segura
  if (session_status() === PHP_SESSION_NONE) {
      session_start();
  }

  $textoBoton = "Ingresar";
  $linkBoton = $BASE . "View/login.php";
  $iconoBoton = "bi-box-arrow-in-right";
  $idBoton = "btnLogin"; 

  if (isset($_SESSION['IDusuario']) && isset($_SESSION['Rol'])) {
      
      
      $nombreUsuario = htmlspecialchars($_SESSION['Nombre'] ?? '');
      $textoBoton = "Menu $nombreUsuario"; 

      $iconoBoton = "bi-person-circle";
      $idBoton = "btnDashboard"; 

      // Rutas según Rol
      switch ($_SESSION['Rol']) {
          case 'Administrador':
              $linkBoton = $BASE . "View/DashboardAdministrador.php";
              break;
          case 'Comerciante': 
              $linkBoton = $BASE . "View/DashboardTienda.php";
              break;
          case 'Usuario':     
              $linkBoton = $BASE . "View/DashBoardCliente.php";
              break;
          default:
              $linkBoton = $BASE . "index.php"; 
              break;
      }
  }
?>

<style><?php include dirname(__DIR__) . '/layouts/css/Navbar.css'; ?></style>

<header class="hero-header">
  <nav class="navbar navbar-expand-lg justify-content-evenly" data-bs-theme="dark" style="--bs-navbar-bg:#2A2668;">
    <div class="container align-items d-flex flex-row mx-3">
      
      <a class="navbar-brand fw-bold fs-4" href="<?= $BASE ?>index.php">
        <i class="bi bi-shop me-2 fs-1"></i>Shopping UTN
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
              aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav mx-auto gap-4 px-3 fs-5">
          <li class="nav-item"><a class="nav-link active" href="<?= $BASE ?>index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= $BASE ?>View/promociones.php">Promociones</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= $BASE ?>View/Tienda.php">Tiendas</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= $BASE ?>View/Novedades.php">Novedades</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= $BASE ?>View/Comer.html">Comer</a></li>
        </ul>

        <a class="btn btn-outline-light btn-lg me-3" href="<?= $linkBoton ?>" id="<?= $idBoton ?>">
          <i class="bi <?= $iconoBoton ?> mx-1 me-2"></i><?= $textoBoton ?>
        </a>
      </div>
    </div>
  </nav>
</header>