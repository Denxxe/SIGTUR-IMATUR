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

        // FECHAS FECHAS DE NACIMIENTO
        if (type === 'date' && (name.includes('nacimiento') || id.includes('nacimiento'))) {
            // No permitir fechas futuras
            const today = new Date().toISOString().split('T')[0];
            input.setAttribute('max', today);
        }

        input.dataset.formatAttached = 'true';
    });
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
    let val = e.target.value;
    // Eliminar números y caracteres prohibidos
    val = val.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
    
    // Capitalizar primera letra de cada palabra
    val = val.replace(/\b\w/g, c => c.toUpperCase());
    
    e.target.value = val;
}

/**
 * Lógica de formato de teléfono (Solo admite +, -, () y números)
 */
function formatTelefono(e) {
    let val = e.target.value;
    val = val.replace(/[^0-9+\-()\s]/g, '');
    e.target.value = val;
}
