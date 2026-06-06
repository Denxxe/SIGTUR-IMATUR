/**
 * SIGTUR-IMATUR Validador Global de Formularios
 * Este script intercepta e inyecta reglas de validación y formateo en tiempo real a todos los formularios.
 */

document.addEventListener('DOMContentLoaded', () => {
    initSigturValidations();
    
    // Listener asíncrono para Modales (Bootstrap) en caso de que los forms se generen dinámicamente
    document.addEventListener('shown.bs.modal', function() {
        initSigturValidations();
    });
});

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
        
        // CÉDULAS
        if (name.includes('cedula') || id.includes('cedula')) {
            input.setAttribute('pattern', '^[VEve]-\\d{7,9}$');
            input.title = "Formato admitido: V-12345678 o E-12345678";
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

        // TELÉFONOS
        if (name.includes('telefono') || type === 'tel') {
            input.setAttribute('pattern', '^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\\s\\./0-9]*$');
            input.addEventListener('input', formatTelefono);
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
 * Lógica de Formateo de Cédulas: Fuerza comienzo V- o E- y bloquea texto 
 */
function formatCedula(e) {
    let val = e.target.value.toUpperCase();
    
    // Quita todo lo que no sea V, E, un guion o un dígito
    val = val.replace(/[^VE0-9-]/g, '');
    
    // Si la persona inserta números directamente, anteponer V-
    if (/^[0-9]/.test(val)) {
        val = 'V-' + val;
    }
    // Si la persona inserta solo la V o E, agregar el guion
    else if (/^[VE][0-9]/.test(val)) {
        val = val.charAt(0) + '-' + val.slice(1);
    }
    
    // Arreglar duplicación de guiones (V--123)
    val = val.replace(/-+/g, '-');
    
    // Limitar longitud
    if (val.length > 11) {
        val = val.slice(0, 11);
    }
    
    e.target.value = val;
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
 * Lógica de formato de teléfono (Solo admite +, -, () y números)
 */
function formatTelefono(e) {
    let val = e.target.value;
    val = val.replace(/[^0-9+\-()\s]/g, '');
    e.target.value = val;
}
