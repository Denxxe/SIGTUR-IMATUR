/**
 * SIGTUR-IMATUR Validador Global de Formularios
 * Este script intercepta e inyecta reglas de validación y formateo en tiempo real a todos los formularios.
 */

document.addEventListener('DOMContentLoaded', () => {
    initSigturValidations();

    // Listener asíncrono para Modales (Bootstrap) en caso de que los forms se generen dinámicamente
    document.addEventListener('shown.bs.modal', function() {
        initSigturValidations();
        sigturRefreshButtons();
    });
});

/**
 * Recalcula el estado (habilitado/deshabilitado) de los botones submit de todos
 * los formularios según su validez. Útil tras cambios programáticos o navegación.
 */
function sigturRefreshButtons() {
    document.querySelectorAll('form.needs-validation').forEach(f => {
        if (typeof f.__sigToggle === 'function') f.__sigToggle();
    });
}
window.sigturRefreshButtons = sigturRefreshButtons;

function initSigturValidations() {
    // 1. Inicializar Validaciones de Boostrap (Submits)
    const forms = document.querySelectorAll('.needs-validation, form:not(.no-validate)');
    
    Array.from(forms).forEach(form => {
        // Enforce Bootstrap validation style
        form.classList.add('needs-validation');
        form.setAttribute('novalidate', '');
        
        // Evitar duplicar el listener
        if(form.dataset.validationAttached) return;
        
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);

        // Botón submit deshabilitado hasta que el formulario sea válido (campos requeridos OK).
        // Excepción: forms marcados data-no-toggle (p. ej. filtros) no se tocan.
        if (!form.hasAttribute('data-no-toggle')) {
            const toggleBtns = () => {
                // Validez considerando sólo campos visibles y habilitados:
                // evita que un required dentro de un bloque oculto (display:none)
                // deje el botón bloqueado para siempre.
                let ok = true;
                form.querySelectorAll('input, select, textarea').forEach(el => {
                    if (el.disabled) return;
                    if (el.offsetParent === null) return; // no visible (incluye type=hidden)
                    if (!el.checkValidity()) ok = false;
                });
                form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(b => {
                    b.disabled = !ok;
                });
            };
            form.__sigToggle = toggleBtns;
            form.addEventListener('input', toggleBtns);
            form.addEventListener('change', toggleBtns);
        }

        form.dataset.validationAttached = 'true';
    });

    // 2. Interceptar el tipeo (Input Formatting)
    const inputs = document.querySelectorAll('input, textarea');
    
    inputs.forEach(input => {
        if(input.dataset.formatAttached) return;

        // -- REGLAS POR NAME O ID --
        const name = (input.name || '').toLowerCase();
        const id = (input.id || '').toLowerCase();
        const type = input.type;
        
        // CÉDULAS — solo números, máximo 8 dígitos
        // Excepción: campos "libre" (ID escolar / extranjeros sin cédula venezolana)
        // que pueden contener letras u otra longitud.
        if ((name.includes('cedula') || id.includes('cedula')) && !name.includes('libre') && !id.includes('libre')) {
            input.setAttribute('pattern', '^\\d{6,8}$');
            input.setAttribute('maxlength', '8');
            input.setAttribute('inputmode', 'numeric');
            input.title = "Solo números (6 a 8 dígitos).";
            input.addEventListener('input', formatCedula);
            if(input.value) formatCedula({target: input}); // Format existing
        }
        
        // NOMBRES Y APELLIDOS
        if (name.includes('nombre') || name.includes('apellido') || id.includes('nombre') || id.includes('apellido')) {
            // Permitir solo letras y espacios
            input.setAttribute('pattern', '^[a-zA-ZáéíóúÁÉÍÓÚñÑ\\s]+$');
            input.title = "Solo puede contener letras y espacios.";
            input.addEventListener('input', formatNombreApellido);
        }

        // TELÉFONOS — prefijo venezolano (select) + 7 dígitos
        if (name.includes('telefono') || id.includes('telefono') || type === 'tel') {
            initTelefonoInput(input);
        }

        // FECHAS DE NACIMIENTO
        if (type === 'date' && (name.includes('nacimiento') || id.includes('nacimiento') || input.classList.contains('js-edad'))) {
            // No permitir fechas futuras
            const today = new Date().toISOString().split('T')[0];
            input.setAttribute('max', today);
            // Cálculo de edad + validación (opt-in con clase .js-edad)
            if (input.classList.contains('js-edad')) {
                initEdadInput(input);
            }
        }

        input.dataset.formatAttached = 'true';
    });

    // Estado inicial de los botones submit (tras enganchar validaciones/realces)
    sigturRefreshButtons();
}

/**
 * Calcula la edad (años cumplidos) a partir de una fecha 'YYYY-MM-DD'.
 * Devuelve null si la fecha es vacía o inválida.
 */
function sigturEdad(fechaStr) {
    if (!fechaStr) return null;
    const f = new Date(fechaStr + 'T00:00:00');
    if (isNaN(f.getTime())) return null;
    const h = new Date();
    let edad = h.getFullYear() - f.getFullYear();
    const m = h.getMonth() - f.getMonth();
    if (m < 0 || (m === 0 && h.getDate() < f.getDate())) edad--;
    return edad;
}
// Expuesto por si algún módulo lo necesita (no pisa definiciones locales existentes).
if (typeof window.sigturEdad === 'undefined') window.sigturEdad = sigturEdad;

/**
 * Conecta un <input type="date" class="js-edad"> con cálculo de edad en vivo
 * y validación opcional de rango (data-edad-min / data-edad-max).
 * Muestra "N años" bajo el campo (o en data-edad-target) y bloquea fuera de rango.
 */
function initEdadInput(input) {
    if (input.dataset.edadAttached) return;
    input.dataset.edadAttached = 'true';

    // Elemento donde mostrar la edad: data-edad-target, #<id>_edad, o uno creado al vuelo
    let badge = null;
    if (input.dataset.edadTarget) badge = document.getElementById(input.dataset.edadTarget);
    if (!badge && input.id) badge = document.getElementById(input.id + '_edad');
    if (!badge) {
        badge = document.createElement('small');
        if (input.id) badge.id = input.id + '_edad';
        badge.style.display = 'block';
        badge.style.marginTop = '4px';
        badge.style.fontWeight = '600';
        input.insertAdjacentElement('afterend', badge);
    }

    // update() relee data-edad-min/max en cada cambio → permite ajustar el rango en vivo
    const update = () => {
        const min = input.dataset.edadMin ? parseInt(input.dataset.edadMin, 10) : null;
        const max = input.dataset.edadMax ? parseInt(input.dataset.edadMax, 10) : null;
        const hoy = new Date();
        // Restricciones nativas del datepicker (refuerzan la validación)
        if (min !== null) {
            input.setAttribute('max', new Date(hoy.getFullYear() - min, hoy.getMonth(), hoy.getDate()).toISOString().split('T')[0]);
        } else {
            input.setAttribute('max', hoy.toISOString().split('T')[0]); // sin futuras
        }
        if (max !== null) {
            input.setAttribute('min', new Date(hoy.getFullYear() - max - 1, hoy.getMonth(), hoy.getDate() + 1).toISOString().split('T')[0]);
        } else {
            input.removeAttribute('min');
        }
        const edad = sigturEdad(input.value);
        if (edad === null) { badge.textContent = ''; input.setCustomValidity(''); return; }
        let msg = '';
        if (min !== null && edad < min) msg = 'Debe tener al menos ' + min + ' años.';
        else if (max !== null && edad > max) msg = 'No puede superar ' + max + ' años.';
        input.setCustomValidity(msg);
        badge.textContent = edad + ' año' + (edad === 1 ? '' : 's') + (msg ? ' — ' + msg : '');
        badge.style.color = msg ? 'var(--danger, #EF4444)' : 'var(--text-secondary)';
    };
    input.addEventListener('input', update);
    input.addEventListener('change', update);
    // Permite forzar revalidación tras cambiar data-edad-* externamente
    input.addEventListener('edad:refresh', update);
    update();
}

/** 
 * Lógica de Formateo de Cédulas: solo dígitos, máximo 8 (sin letras ni símbolos).
 */
function formatCedula(e) {
    // Quita todo lo que no sea dígito y limita a 8 caracteres
    e.target.value = (e.target.value || '').replace(/\D/g, '').slice(0, 8);
}

/**
 * Lógica para Capitalizar palabras y eliminar números de Nombres y Apellidos
 */
function formatNombreApellido(e) {
    const input = e.target;
    const pos = input.selectionStart;
    let val = input.value;

    // Eliminar números y caracteres prohibidos
    val = val.replace(/[^a-zA-ZáéíóúüÁÉÍÓÚÜñÑ\s]/g, '');

    // Capitalizar primera letra de cada palabra respetando caracteres acentuados
    val = val.replace(/(^|[\s])([a-záéíóúüñ])/gi, (_, sep, c) => sep + c.toUpperCase());

    input.value = val;
    // Restaurar posición del cursor para no saltar al final al editar en medio
    try { input.setSelectionRange(pos, pos); } catch(_) {}
}

/**
 * Lógica de formato de teléfono (Solo admite +, -, () y números) — legado, sin uso.
 */
function formatTelefono(e) {
    let val = e.target.value;
    val = val.replace(/[^0-9+\-()\s]/g, '');
    e.target.value = val;
}

// ── Teléfonos venezolanos: prefijo móvil (select) + 7 dígitos ────────────────
// Formato nacional: 0XXX + 7 dígitos = 11 dígitos.
// Solo se ofrecen prefijos MÓVILES; los fijos no se muestran. Si un registro
// guardado trae un prefijo distinto (p. ej. fijo legado), se agrega como opción
// al editar para no corromper el dato.
const TEL_MOVILES = ['0412', '0414', '0416', '0424', '0426'];
const TEL_PREFIJO_DEFAULT = '0414';
// Descriptor nativo de .value para detectar asignaciones programáticas (lookup AJAX).
const _telValueDesc = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value');

/**
 * Convierte un input de teléfono en: [select de prefijo] + [input de 7 dígitos].
 * El input original queda oculto y conserva el valor combinado (prefijo+7) para el POST.
 */
function initTelefonoInput(input) {
    if (input.dataset.telAttached) return;
    input.dataset.telAttached = 'true';

    const requerido = input.hasAttribute('required');
    input.removeAttribute('required');   // el visible (numero) llevará la validación
    input.removeAttribute('pattern');

    // Construir UI
    const combo = document.createElement('div');
    combo.style.display = 'flex';
    combo.style.gap = '6px';

    const sel = document.createElement('select');
    sel.className = 'sig-select';
    sel.style.maxWidth = '108px';
    sel.innerHTML = TEL_MOVILES.map(p => `<option value="${p}">${p}</option>`).join('');

    const num = document.createElement('input');
    num.type = 'text';
    num.className = 'sig-input';
    num.style.flex = '1';
    num.setAttribute('inputmode', 'numeric');
    num.setAttribute('maxlength', '7');
    num.setAttribute('placeholder', '7 dígitos');
    if (requerido) num.setAttribute('required', '');

    combo.appendChild(sel);
    combo.appendChild(num);
    input.insertAdjacentElement('afterend', combo);
    input.type = 'hidden';

    const setOriginal = (v) => { _telValueDesc.set.call(input, v); }; // sin disparar el sync

    const aplicar = () => {
        // Sanear los 7 dígitos
        num.value = num.value.replace(/\D/g, '').slice(0, 7);
        const completo = num.value.length === 7;
        if (num.value.length === 0) {
            setOriginal('');
            num.setCustomValidity(requerido ? 'Ingrese el número (7 dígitos).' : '');
        } else if (!completo) {
            setOriginal('');
            num.setCustomValidity('El número debe tener exactamente 7 dígitos.');
        } else {
            setOriginal(sel.value + num.value);
            num.setCustomValidity('');
        }
    };

    // Parsear el valor original (incluye prefijos no listados) hacia el combo
    const sincronizar = () => {
        const d = (_telValueDesc.get.call(input) || '').replace(/\D/g, '');
        let pref = TEL_PREFIJO_DEFAULT, resto = '';
        if (d.length === 11 && d[0] === '0') { pref = d.slice(0, 4); resto = d.slice(4, 11); }
        else if (d.length === 10) { pref = '0' + d.slice(0, 3); resto = d.slice(3, 10); }
        else if (d.length === 7) { resto = d; }
        else if (d.length > 0) { resto = d.slice(-7); }
        // Si el prefijo guardado no está entre los móviles ofrecidos (p. ej. fijo
        // legado), se agrega como opción para no perder el dato al editar.
        if (resto && !Array.from(sel.options).some(o => o.value === pref)) {
            const opt = document.createElement('option'); opt.value = pref; opt.textContent = pref; sel.appendChild(opt);
        }
        sel.value = Array.from(sel.options).some(o => o.value === pref) ? pref : TEL_PREFIJO_DEFAULT;
        num.value = resto;
        num.setCustomValidity('');
    };

    sel.addEventListener('change', aplicar);
    num.addEventListener('input', aplicar);

    // Interceptar asignaciones programáticas a .value (p. ej. autocompletar por cédula)
    Object.defineProperty(input, 'value', {
        configurable: true,
        get() { return _telValueDesc.get.call(this); },
        set(v) { _telValueDesc.set.call(this, v); sincronizar(); }
    });

    sincronizar(); // estado inicial (modo edición / valor precargado)
}
