(function () {
  function run() {
    const el = document.getElementById('btnLogin');
    if (!el) return;

    // Usar rutas relativas en lugar de absolutas
    el.setAttribute('href', './DashBoardCliente.php');

    const icon = el.querySelector('i');
    if (icon) {
      el.innerHTML = icon.outerHTML + ' MENU';
    } else {
      el.textContent = 'MENU';
    }

    el.id = 'btnDashboard';

    if (el.tagName.toLowerCase() !== 'a') {
      el.addEventListener('click', (e) => {
        e.preventDefault();
        // Usar ruta relativa para el redireccionamiento
        location.assign('/');
      });
    }
  }

  if (document.readyState === 'loading')
    document.addEventListener('DOMContentLoaded', run, { once: true });
  else
    run();
})();