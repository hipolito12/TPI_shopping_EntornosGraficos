// Maneja submit y errores

(function () {
  'use strict';

  // ——— utilidades ———
  const $ = (sel, root = document) => root.querySelector(sel);
  const $all = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  const form =
    $('#form-crear-tienda') ||
    document.forms[0] ||
    $('form');

  if (!form) return;

  const submitBtn = form.querySelector('button[type="submit"]');
  const msgBox = document.getElementById('server-messages');

  const clearAlerts = () => { if (msgBox) msgBox.innerHTML = ''; };

  const showAlert = (message, type = 'danger') => {
    if (!msgBox) return;
    clearAlerts();
    const div = document.createElement('div');
    div.className = `alert alert-${type} alert-dismissible fade show`;
    div.setAttribute('role', 'alert');
    div.textContent = String(message || 'Ocurrió un error.');
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn-close';
    btn.setAttribute('data-bs-dismiss', 'alert');
    btn.setAttribute('aria-label', 'Close');
    div.appendChild(btn);
    msgBox.appendChild(div);
  };

  const clearFieldValidation = () => {
    $all('.is-invalid, .is-valid', form).forEach(el => {
      el.classList.remove('is-invalid', 'is-valid');
    });
    // Limpiar validaciones
    $all('.invalid-feedback', form).forEach(fb => { fb.textContent = ''; });
  };

  const ensureFeedback = (input) => {
    // Busca un .invalid-feedback dentro del mismo "grupo" del campo;
    // si no existe, lo crea inmediatamente después del input.
    let fb = null;
    const group = input.closest('.mb-3, .form-group') || input.parentElement;
    if (group) {
      fb = $('.invalid-feedback', group);
    }
    if (!fb) {
      fb = document.createElement('div');
      fb.className = 'invalid-feedback';
      input.insertAdjacentElement('afterend', fb);
    }
    return fb;
  };

  const findField = (name) => {
    // 1) por id exacto
    let el = form.querySelector(`#${CSS && CSS.escape ? CSS.escape(name) : name}`);
    if (el) return el;
    // 2) por name exacto
    el = form.querySelector(`[name="${name}"]`);
    if (el) return el;
    // 3) por casos comunes (guiones/underscores intercambiados)
    el = form.querySelector(`#${name.replace(/-/g, '_')}, [name="${name.replace(/-/g, '_')}"]`);
    if (el) return el;
    el = form.querySelector(`#${name.replace(/_/g, '-')}, [name="${name.replace(/_/g, '-')}"]`);
    return el;
  };

  const setFieldError = (name, message) => {
    const input = findField(name);
    if (!input) return false;
    input.classList.remove('is-valid');
    input.classList.add('is-invalid');
    const fb = ensureFeedback(input);
    fb.textContent = Array.isArray(message) ? message.join(' ') : String(message || 'Campo inválido.');
    return true;
  };

  const focusFirstError = () => {
    const first = form.querySelector('.is-invalid');
    if (!first) return;
    first.focus({ preventScroll: false });
    first.scrollIntoView({ behavior: 'smooth', block: 'center' });
  };

  const setLoading = (isLoading) => {
    if (!submitBtn) return;
    if (isLoading) {
      submitBtn.dataset._label = submitBtn.textContent;
      submitBtn.textContent = 'Enviando...';
      submitBtn.disabled = true;
    } else {
      submitBtn.textContent = submitBtn.dataset._label || 'Enviar';
      submitBtn.disabled = false;
    }
  };

  const parseJsonSafe = async (resp) => {
    // Parsear JSON seguro
    try {
      const ct = resp.headers.get('content-type') || '';
      if (ct.includes('application/json')) return await resp.json();
      // Si no es JSON, intentamos texto para depurar
      const txt = await resp.text();
      try { return JSON.parse(txt); } catch { return { _raw: txt }; }
    } catch {
      return {};
    }
  };

  // ——— submit ———
  form.addEventListener('submit', async (ev) => {
    ev.preventDefault();
    clearAlerts();
    clearFieldValidation();
    setLoading(true);

    try {
      const resp = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { 'Accept': 'application/json' }
      });

      const data = await parseJsonSafe(resp);

      // 422: errores de validación
      if (resp.status === 422) {
        const errors = (data && data.errors) || {};
        let atLeastOne = false;

        // Marca campo por campo
        Object.entries(errors).forEach(([name, msg]) => {
          const marked = setFieldError(name, msg);
          atLeastOne = atLeastOne || marked;
        });

        // Si el backend mandó un mensaje general, mostralo
        showAlert(data.message || 'Revisá los campos marcados.', 'danger');

        // Si no se pudo mapear ningún campo (nombres no coinciden), marcamos el form
        if (!atLeastOne) {
          showAlert('Hay errores de validación, verificá los datos.', 'danger');
        }

        focusFirstError();
        return;
      }

      // Otros errores del servidor (500, 405, etc.)
      if (!resp.ok || data?.ok === false) {
        const msg = data?.message || `Error ${resp.status || ''}. Intentalo nuevamente.`;
        showAlert(msg, 'danger');
        return;
      }

      // Éxito (201/200)
      showAlert(data.message || 'Operación exitosa.', 'success');
      form.reset();
      clearFieldValidation();

      // Redirección opcional
      if (data.redirect) {
        window.location.assign(data.redirect);
      }
    } catch (err) {
      console.error('Error en fetch:', err);
      showAlert('No se pudo contactar al servidor. Verificá tu conexión e intentá otra vez.', 'danger');
    } finally {
      setLoading(false);
    }
  });


})();
