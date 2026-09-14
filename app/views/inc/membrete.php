<?php
/**
 * MEMBRETE INSTITUCIONAL — partial compartido por TODO documento imprimible.
 *
 * Es el formato que el cliente dio por bueno (el del oficio de rutas):
 * logo de la **Alcaldía a la izquierda**, bloque central de cinco líneas en
 * mayúsculas, logo de **IMATUR a la derecha** y una línea de separación.
 *
 *     [Alcaldía]   REPÚBLICA BOLIVARIANA DE VENEZUELA        [IMATUR]
 *                  ALCALDÍA BOLIVARIANA DEL MUNICIPIO SUCRE
 *                  INSTITUTO MUNICIPAL AUTÓNOMO DE TURISMO (IMATUR-SUCRE)
 *                  CUMANÁ, ESTADO SUCRE
 *                  RIF. G-20008498-7
 *     ─────────────────────────────────────────────────────────────────
 *
 * POR QUÉ EXISTE: el membrete estaba copiado a mano en una docena de
 * documentos, cada uno con su tamaño de logo, su tipografía y sus líneas —
 * varios con un solo logo, otros con el de IMATUR repetido a ambos lados, y
 * ninguno con el de la Alcaldía (ver ConfigSistema::LOGO_ALCALDIA). Un solo
 * partial evita que vuelvan a divergir.
 *
 * USO (los estilos van en línea a propósito: estas vistas son standalone,
 * no cargan el CSS del sistema, y además tienen que sobrevivir al
 * `window.print()`):
 *
 *     <?php $mb = ['alto_logo' => 60]; require '../app/views/inc/membrete.php'; ?>
 *
 * Opciones de $mb (todas opcionales):
 *   alto_logo   px de alto de los logos           (default 68)
 *   fuente      familia del bloque de texto        (default Arial)
 *   tamano      tamaño del bloque de texto         (default 9.5pt)
 *   sin_linea   true para omitir el <hr>           (default false)
 *   margen_inf  margen bajo el <hr>                (default 28px)
 *   dependencia línea extra al pie del bloque (ej. "Dirección de Talento Humano")
 */

$__mb          = isset($mb) && is_array($mb) ? $mb : [];
$__altoLogo    = (int)($__mb['alto_logo'] ?? 68);
$__fuente      = $__mb['fuente']     ?? "Arial,Helvetica,sans-serif";
$__tamano      = $__mb['tamano']     ?? '9.5pt';
$__sinLinea    = !empty($__mb['sin_linea']);
$__margenInf   = $__mb['margen_inf'] ?? '28px';
$__dependencia = trim((string)($__mb['dependencia'] ?? ''));
?>
<div style="display:flex; align-items:center; gap:12px; margin-bottom:6px;">
    <img src="<?php echo ConfigSistema::urlLogoAlcaldia(); ?>" alt="Alcaldía de Cumaná"
         style="height:<?php echo $__altoLogo; ?>px; width:auto; object-fit:contain; flex-shrink:0;"
         onerror="this.style.visibility='hidden'">
    <div style="flex:1; text-align:center; font-family:<?php echo $__fuente; ?>;">
        <p style="font-size:<?php echo $__tamano; ?>; font-weight:700; text-transform:uppercase;
                  line-height:1.7; color:#111; letter-spacing:.01em; margin:0;">
            República Bolivariana de Venezuela<br>
            Alcaldía Bolivariana del Municipio Sucre<br>
            Instituto Municipal Autónomo de Turismo (IMATUR-SUCRE)<br>
            Cumaná, Estado Sucre<br>
            RIF. <?php echo htmlspecialchars(ConfigSistema::rif()); ?>
            <?php if ($__dependencia !== ''): ?>
                <br><?php echo htmlspecialchars($__dependencia); ?>
            <?php endif; ?>
        </p>
    </div>
    <img src="<?php echo ConfigSistema::urlLogoImatur(); ?>" alt="IMATUR"
         style="height:<?php echo $__altoLogo; ?>px; width:auto; object-fit:contain; flex-shrink:0;"
         onerror="this.style.visibility='hidden'">
</div>
<?php if (!$__sinLinea): ?>
<hr style="border:none; border-top:1px solid #111; margin:6px 0 <?php echo $__margenInf; ?>;">
<?php endif; ?>
<?php
// No dejar $mb colgando: si el documento incluye el membrete dos veces (como
// las listas de asistencia, que repiten encabezado por página), la segunda
// heredaría las opciones de la primera sin que nadie lo pidiera.
unset($mb, $__mb, $__altoLogo, $__fuente, $__tamano, $__sinLinea, $__margenInf, $__dependencia);
