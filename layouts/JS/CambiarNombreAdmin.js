(function activarMenuAdmin() {
  function run() {
    const el = document.getElementById('btnLogin');
    if (!el) return;

    el.setAttribute('href', '/TPIShopping/View/DashboardAdministrador.php');

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
        location.assign('/TPIShopping/');
      });
    }
  }

  if (document.readyState === 'loading')
    document.addEventListener('DOMContentLoaded', run, { once: true });
  else
    run();
})();
