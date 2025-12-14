// Validación tienda

(() => {
  'use strict';

  const byId = (id) => document.getElementById(id);
  const form = byId('form-crear-tienda') || document.querySelector('form');
  if (!form) return;

  // Campos
  const F = {
    nombre:        byId('nombre'),
    apellido:      byId('apellido'),
    email:         byId('email'),
    password:      byId('password'),
    telefono:      byId('telefono'),
    sexo:          byId('sexo'),
    dni:           byId('dni'),
    cuil:          byId('cuil'),
    rubro:         byId('rubro'),
    nombre_local:  byId('nombre_local'),
    lugar:         byId('lugar')
  };

  // Reglas
  const RX = {
    nombre:   /^[A-Za-zÁÉÍÓÚÜÄËÏÖÑáéíóúüäëïöñ' ]{2,50}$/,
    email:    /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/,
    pass:     /^(?=.*[A-Z])(?=.*\d).{8,}$/,
    dni:      /^\d{7,8}$/,
    cuil:     /^\d{11}$/,
    telefono: /^\d{7,20}$/,
    texto:    /.{2,}/
  };

  // Utilidades UI
  const ensureFeedback = (input) => {
    if (!input) return null;
    // Asegurar feedback
    let fb = input.nextElementSibling;
    if (!(fb && fb.classList && fb.classList.contains('invalid-feedback'))) {
      fb = document.createElement('div');
      fb.className = 'invalid-feedback';
      input.insertAdjacentElement('afterend', fb);
    }
    return fb;
  };

  const setState = (input, ok, msg = '') => {
    if (!input) return true;
    input.classList.toggle('is-valid', !!ok);
    input.classList.toggle('is-invalid', !ok);
    const fb = ensureFeedback(input);
    if (fb) fb.textContent = ok ? '' : String(msg);
    return !!ok;
  };

  // Chequeo CUIL
  const cuilCheckDigitOK = (val) => {
    const num = String(val || '').replace(/\D/g, '');
    if (!/^\d{11}$/.test(num)) return false;
    const w = [5,4,3,2,7,6,5,4,3,2];
    const sum = w.reduce((acc, wi, i) => acc + wi * parseInt(num[i], 10), 0);
    let dv = 11 - (sum % 11);
    if (dv === 11) dv = 0;
    else if (dv === 10) dv = 9;
    return dv === parseInt(num[10], 10);
  };

  // Validadores campo a campo
  const V = {
    nombre: () =>
      setState(F.nombre, RX.nombre.test(F.nombre?.value?.trim() || ''), 'Ingresá un nombre válido (2–50 letras).'),

    apellido: () =>
      setState(F.apellido, RX.nombre.test(F.apellido?.value?.trim() || ''), 'Ingresá un apellido válido (2–50 letras).'),

    email: () =>
      setState(F.email, RX.email.test(F.email?.value?.trim() || ''), 'Email inválido.'),

    password: () =>
      setState(F.password, RX.pass.test(F.password?.value || ''), 'Mín. 8, con 1 mayúscula y 1 número.'),

    telefono: () => {
      const val = F.telefono?.value?.trim() || '';
      // Teléfono opcional
      if (val === '') return setState(F.telefono, true, '');
      return setState(F.telefono, RX.telefono.test(val), 'Solo dígitos (7–20).');
    },

    sexo: () =>
      setState(F.sexo, (F.sexo?.value || '') !== '', 'Seleccioná una opción.'),

    dni: () =>
      setState(F.dni, RX.dni.test(F.dni?.value?.trim() || ''), 'DNI inválido (7–8 dígitos).'),

    cuil: () => {
      const val = F.cuil?.value?.trim() || '';
      if (!RX.cuil.test(val)) return setState(F.cuil, false, 'CUIL inválido (11 dígitos).');
      if (!cuilCheckDigitOK(val)) return setState(F.cuil, false, 'CUIL inválido (dígito verificador).');
      return setState(F.cuil, true, '');
    },

    rubro: () =>
      setState(F.rubro, RX.texto.test(F.rubro?.value?.trim() || ''), 'Indicá el rubro.'),

    nombre_local: () =>
      setState(F.nombre_local, RX.texto.test(F.nombre_local?.value?.trim() || ''), 'Indicá el nombre del local.'),

    lugar: () =>
      setState(F.lugar, (F.lugar?.value || '') !== '', 'Seleccioná una ubicación.')
  };

  // Valida un campo por nombre
  const validateField = (name) => (V[name] ? V[name]() : true);

  // Valida todos
  const validateAll = () => {
    let ok = true;
    for (const name of Object.keys(V)) {
      ok = validateField(name) && ok;
    }
    return ok;
  };

  // Listeners "en vivo"
  const attach = (name, el) => {
    if (!el) return;
    const evt = el.tagName === 'SELECT' ? 'change' : 'input';
    el.addEventListener(evt, () => validateField(name));
    el.addEventListener('blur', () => validateField(name));
  };
  Object.entries(F).forEach(([name, el]) => attach(name, el));

  // Export validate
  window.tiendaValidateForm = validateAll; 

})();
