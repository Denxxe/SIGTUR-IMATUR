/**
 * SIGTUR-IMATUR — botón «ver contraseña» (el ojito) en todo <input type="password">.
 *
 * Independiente a propósito: lo cargan el layout (footer.php) y también las
 * pantallas sueltas de acceso (login, restablecer), que no cargan
 * sigtur-validations.js ni Bootstrap Icons. Por eso el ícono es SVG en línea y
 * el estilo se inyecta desde aquí.
 *
 * Opt-out: `data-no-toggle` en el input. Idempotente: se puede volver a llamar
 * con `window.initVerPassword()` tras abrir un modal o inyectar un formulario.
 */
(function () {
    var OJO = '<svg viewBox="0 0 16 16" width="18" height="18" fill="currentColor" aria-hidden="true">'
        + '<path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.17 8a13 13 0 0 1 1.66-2.04C4.12 4.67 5.88 3.5 8 3.5s3.87 1.17 5.17 2.46A13 13 0 0 1 14.83 8q-.09.13-.2.3c-.33.47-.81 1.1-1.46 1.74C11.88 11.33 10.12 12.5 8 12.5s-3.87-1.17-5.17-2.46A13 13 0 0 1 1.17 8"/>'
        + '<path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/></svg>';
    var OJO_TACHADO = '<svg viewBox="0 0 16 16" width="18" height="18" fill="currentColor" aria-hidden="true">'
        + '<path d="m10.79 12.91-1.64-1.64a3.5 3.5 0 0 1-4.42-4.42L2.68 4.8C1.39 5.95.5 7.27 0 8c0 0 3 5.5 8 5.5a7 7 0 0 0 2.79-.59M5.21 3.09A7 7 0 0 1 8 2.5c5 0 8 5.5 8 5.5s-.89 1.31-2.18 2.47l-2.07-2.07a3.5 3.5 0 0 0-4.42-4.42z"/>'
        + '<path d="M5.53 7.47a2.5 2.5 0 0 0 2.95 2.95zm4.94 1.06L7.47 5.53a2.5 2.5 0 0 1 2.95 2.95z"/>'
        + '<path d="m13.65 14.35-12-12 .7-.7 12 12z"/></svg>';

    function estilos() {
        if (document.getElementById('sigtur-pw-css')) return;
        var s = document.createElement('style');
        s.id = 'sigtur-pw-css';
        s.textContent =
            '.pw-wrap{position:relative;display:block;width:100%}'
            + '.pw-wrap>input{padding-right:44px!important;width:100%}'
            + '.pw-toggle{position:absolute;top:0;right:0;height:100%;width:42px;display:flex;align-items:center;'
            + 'justify-content:center;background:none;border:0;padding:0;margin:0;cursor:pointer;color:#64748b;border-radius:0 8px 8px 0}'
            + '.pw-toggle:hover{color:#1e293b}'
            + '.pw-toggle:focus-visible{outline:2px solid #2563eb;outline-offset:-2px}'
            + '[data-theme="dark"] .pw-toggle{color:#94a3b8}[data-theme="dark"] .pw-toggle:hover{color:#e2e8f0}';
        document.head.appendChild(s);
    }

    function initVerPassword(root) {
        estilos();
        (root || document).querySelectorAll('input[type="password"]:not([data-no-toggle]):not([data-pw-toggle])').forEach(function (inp) {
            inp.setAttribute('data-pw-toggle', '1');
            var wrap = document.createElement('span');
            wrap.className = 'pw-wrap';
            inp.parentNode.insertBefore(wrap, inp);
            wrap.appendChild(inp);

            var btn = document.createElement('button');
            btn.type = 'button';               // nunca envía el formulario
            btn.className = 'pw-toggle';
            btn.tabIndex = 0;
            btn.setAttribute('aria-label', 'Mostrar contraseña');
            btn.title = 'Mostrar contraseña';
            btn.innerHTML = OJO;
            btn.addEventListener('click', function () {
                var ver = inp.type === 'password';
                inp.type = ver ? 'text' : 'password';
                btn.innerHTML = ver ? OJO_TACHADO : OJO;
                btn.setAttribute('aria-label', ver ? 'Ocultar contraseña' : 'Mostrar contraseña');
                btn.title = btn.getAttribute('aria-label');
                inp.focus();
            });
            wrap.appendChild(btn);

            // Al enviar, volver a ocultarla: que no quede en texto plano si el
            // navegador guarda el formulario o la página vuelve atrás.
            if (inp.form) inp.form.addEventListener('submit', function () { inp.type = 'password'; });
        });
    }

    window.initVerPassword = initVerPassword;
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function () { initVerPassword(); });
    else initVerPassword();
    document.addEventListener('shown.bs.modal', function () { initVerPassword(); });
})();
