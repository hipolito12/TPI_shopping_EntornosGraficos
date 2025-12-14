(() => {
  'use strict';

  const form = document.getElementById('regForm');
  const btn  = document.getElementById('btnEnviar');
  const msg  = document.getElementById('formMsg');

 
  const f = {
    nombre:    document.getElementById('nombre'),
    apellido:  document.getElementById('apellido'),
    email:     document.getElementById('email'),
    email2:    document.getElementById('email2'),
    pass:      document.getElementById('password'),
    pass2:     document.getElementById('password2'),
    dni:       document.getElementById('dni'),
    sexo:      document.getElementById('sexo'),
    telefono:  document.getElementById('telefono')
  };


  const RX = {
  nombre:   /^[A-Za-zÁÉÍÓÚÜÄËÏÖÑáéíóúüäëïöñ' ]{2,50}$/, 
  email:    /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/,           
  pass:     /^(?=.*[A-Z])(?=.*\d).{8,}$/,               
  dni:      /^\d{8}$/,                                  
  telefono: /^\d{7,20}$/                                
};
  // Validadores
  const setValid = (input, ok) => {
    input.classList.toggle('is-valid', ok);
    input.classList.toggle('is-invalid', !ok);
  };

  const eq = (a, b) => a.value.trim() !== '' && a.value === b.value;


  const validators = {
    nombre:   () => setValid(f.nombre,   RX.nombre.test(f.nombre.value.trim())),
    apellido: () => setValid(f.apellido, RX.nombre.test(f.apellido.value.trim())),
    email:    () => setValid(f.email,    RX.email.test(f.email.value.trim())),
    email2:   () => setValid(f.email2,   RX.email.test(f.email2.value.trim()) && eq(f.email, f.email2)),
    pass:     () => setValid(f.pass,     RX.pass.test(f.pass.value)),
    pass2:    () => setValid(f.pass2,    RX.pass.test(f.pass2.value) && eq(f.pass, f.pass2)),
    dni:      () => setValid(f.dni,      RX.dni.test(f.dni.value)),
    sexo:     () => setValid(f.sexo,     !!f.sexo.value),
    telefono: () => setValid(f.telefono, RX.telefono.test(f.telefono.value.trim()))
  };


  Object.entries(validators).forEach(([k, fn]) => {
    const el = f[k];
    const ev = el.tagName === 'SELECT' ? 'change' : 'input';
    el.addEventListener(ev, fn);
  });


})();
(function () {
  function clearValidation(form, msg) {
    form.classList.remove('was-validated');
    form.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
      el.classList.remove('is-valid', 'is-invalid');
      el.removeAttribute('aria-invalid');
      if (typeof el.setCustomValidity === 'function') el.setCustomValidity('');
    });
    if (msg) { msg.textContent = ''; msg.className = 'mt-3 small'; }
  }

 
 
 //limpia el forulario
 
  function init() {
    const form = document.getElementById('regForm');
    if (!form) return;
    const msg = document.getElementById('formMsg');
    const btnReset = form.querySelector('#btnReset, [type="reset"]');

    form.addEventListener('reset', () => setTimeout(() => clearValidation(form, msg), 0));
    if (btnReset) btnReset.addEventListener('click', () => setTimeout(() => clearValidation(form, msg), 0));
  }

  if (document.readyState === 'loading')
    document.addEventListener('DOMContentLoaded', init, { once: true });
  else
    init();
})();
