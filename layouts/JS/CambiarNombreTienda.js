(function activarMenuTienda() {
  function run() {
    const el = document.getElementById('btnLogin');
    if (!el) return;

    el.setAttribute('href', '/TPIShopping/View/DashboardTienda.php');

    const icon = el.querySelector('i');
    if (icon) {
      el.innerHTML = icon.outerHTML + ' MENU Tienda';
    } else {
      el.textContent = 'MENU tienda';
    }

    el.id = 'btnDashboard';

    if (el.tagName.toLowerCase() !== 'a') {
      el.addEventListener('click', (e) => {
        e.preventDefault();
        location.assign('/TPIShopping/');
      });
    }
  }

  if (document.readyState === 'loading')
    document.addEventListener('DOMContentLoaded', run, { once: true });
  else
    run();
})();
