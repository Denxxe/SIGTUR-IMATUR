<?php require_once '../app/views/inc/header.php';
/**
 * Ficha Institucional de una salida (T-G, mig. 080).
 *
 * Reproduce en pantalla el formato que IMATUR llena a mano
 * (`docs/formatos/rutas_ficha_institucional_IMATUR.jpeg`): cabecera derivada,
 * renglones por institución con el bloque de NIÑOS, acompañantes y las
 * instituciones de apoyo. El total se calcula en vivo, para que quien captura
 * vea la misma cifra que va a firmar.
 */
$ejec   = $data['ejecucion'];
$ruta   = $data['ruta'];
$ficha  = $data['ficha'];
$grupos = $data['grupos'] ?? [];
$tot    = $data['totales'];
$cerrada = $ficha && $ficha->estado === RutaFicha::EST_CERRADA;
$sug     = $data['sugerencia'] ?? ['femenino' => 0, 'masculino' => 0, 'edad_min' => null, 'edad_max' => null];

// Si la ficha aún no existe, se ofrece un renglón con la institución solicitante:
// es lo que el papel siempre trae en la primera línea.
if (empty($grupos)) {
    $grupos = [(object)[
        'tipo'      => RutaFicha::TIPO_INSTITUCION,
        'nombre'    => $ejec->institucion_nombre ?? '',
        'femenino'  => $sug['femenino'],
        'masculino' => $sug['masculino'],
        'edad_min'  => $sug['edad_min'],
        'edad_max'  => $sug['edad_max'],
    ]];
}
$v = fn($x) => htmlspecialchars((string)$x);
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/rutas/detalle/<?php echo (int)$ejec->id; ?>" style="color:inherit;text-decoration:none;">
                <?php echo $v($ruta->nombre); ?>
            </a> · <?php echo date('d/m/Y', strtotime($ejec->fecha)); ?>
        </div>
        <h1 class="page__title">Ficha Institucional</h1>
        <p class="page__subtitle">
            El cierre de la salida: el conteo del grupo atendido, en el formato que se entrega
            a la Dirección de Promoción Turística.
        </p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/rutas/detalle/<?php echo (int)$ejec->id; ?>" class="btn-sig btn-sig--ghost">
            <i class="bi bi-arrow-left"></i> Volver a la salida
        </a>
        <?php if ($ficha): ?>
            <a href="<?php echo URL_ROOT; ?>/rutas/ficha/<?php echo (int)$ejec->id; ?>" class="btn-sig btn-sig--primary" target="_blank">
                <i class="bi bi-printer"></i> Imprimir ficha
            </a>
            <a href="<?php echo URL_ROOT; ?>/rutas/exportarInformeCsv/<?php echo (int)$ejec->id; ?>" class="btn-sig btn-sig--ghost">
                <i class="bi bi-file-earmark-spreadsheet"></i> Excel
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if ($cerrada): ?>
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-5);border-left:3px solid var(--success-500);">
    <div class="sig-card__body" style="padding:var(--sp-4);display:flex;align-items:center;gap:var(--sp-4);flex-wrap:wrap;">
        <div style="flex:1;min-width:280px;">
            <strong><i class="bi bi-check2-circle" style="color:var(--success-600);"></i> Ficha cerrada</strong>
            <?php if (!empty($ficha->fecha_cierre)): ?>
                el <?php echo date('d/m/Y \a \l\a\s H:i', strtotime($ficha->fecha_cierre)); ?>.
            <?php endif; ?>
            <div style="font-size:12px;color:var(--text-secondary);margin-top:2px;">
                Ya no se edita. Si hay que corregir un conteo, reábrala: queda registrado en la bitácora.
            </div>
        </div>
        <form method="POST" action="<?php echo URL_ROOT; ?>/rutas/informe/<?php echo (int)$ejec->id; ?>" style="margin:0;">
            <input type="hidden" name="accion" value="reabrir">
            <button type="submit" class="btn-sig btn-sig--ghost btn-sig--sm">
                <i class="bi bi-unlock"></i> Reabrir para corregir
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<form method="POST" action="<?php echo URL_ROOT; ?>/rutas/informe/<?php echo (int)$ejec->id; ?>" id="formFicha">
<input type="hidden" name="accion" value="guardar" id="ficha_accion">

<!-- ── Cabecera: lo que el sistema ya sabe ───────────────────────────────── -->
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-5);">
    <div class="sig-card__head">
        <div class="sig-card__title"><i class="bi bi-card-heading" style="color:var(--teal-500);"></i> Encabezado</div>
        <span style="font-size:11px;color:var(--text-tertiary);">Se toma de la salida — no hay que reescribirlo</span>
    </div>
    <div class="sig-card__body" style="padding:var(--sp-4);">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="sig-field" style="margin:0;">
                    <label class="sig-field__label">Recorrido</label>
                    <input type="text" class="sig-input" value="<?php echo $v($ruta->nombre); ?>" readonly
                           style="background:var(--bg-muted-subtle);cursor:not-allowed;">
                </div>
            </div>
            <div class="col-md-2">
                <div class="sig-field" style="margin:0;">
                    <label class="sig-field__label">Fecha</label>
                    <input type="text" class="sig-input" value="<?php echo date('d/m/Y', strtotime($ejec->fecha)); ?>" readonly
                           style="background:var(--bg-muted-subtle);cursor:not-allowed;">
                </div>
            </div>
            <div class="col-md-3">
                <div class="sig-field" style="margin:0;">
                    <label class="sig-field__label">Encargado (IMATUR)</label>
                    <input type="text" class="sig-input"
                           value="<?php echo $v($data['encargado'] ?? ''); ?>"
                           placeholder="sin encargado asignado" readonly
                           style="background:var(--bg-muted-subtle);cursor:not-allowed;">
                </div>
            </div>
            <div class="col-md-4">
                <div class="sig-field" style="margin:0;">
                    <label class="sig-field__label" for="responsable_nombre">Responsable del grupo</label>
                    <input type="text" name="responsable_nombre" id="responsable_nombre" class="sig-input"
                           value="<?php echo $v($ficha->responsable_nombre ?? ''); ?>"
                           placeholder="Quien responde por el grupo visitante"
                           data-nombre-libre <?php echo $cerrada ? 'readonly' : ''; ?>>
                </div>
            </div>
        </div>
        <?php if (empty($data['encargado'])): ?>
        <div style="font-size:12px;color:var(--warning-600);margin-top:var(--sp-3);">
            <i class="bi bi-exclamation-triangle"></i>
            Esta salida no tiene personal de IMATUR asignado, así que la casilla «Encargado» de la ficha
            saldrá en blanco. Se asigna en el detalle de la salida.
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── Renglones por institución (bloque NIÑOS) ──────────────────────────── -->
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-5);border-top:3px solid var(--teal-500);">
    <div class="sig-card__head">
        <div class="sig-card__title"><i class="bi bi-people" style="color:var(--teal-500);"></i> Grupo atendido</div>
        <span style="font-size:11px;color:var(--text-tertiary);">Una línea por institución · el desglose es por sexo</span>
    </div>
    <div class="sig-table-wrap" data-no-export>
        <table class="sig-table" id="tblInstituciones">
            <thead>
                <tr>
                    <th style="min-width:220px;">Institución</th>
                    <th class="text-center" style="width:90px;">Niños F</th>
                    <th class="text-center" style="width:90px;">Niños M</th>
                    <th class="text-center" style="width:150px;">Edades</th>
                    <th class="text-center" style="width:80px;">Total</th>
                    <th class="col-actions" style="width:60px;"></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($grupos as $g): if ($g->tipo !== RutaFicha::TIPO_INSTITUCION) continue; ?>
                <tr class="fila-grupo">
                    <input type="hidden" name="grp_tipo[]" value="<?php echo RutaFicha::TIPO_INSTITUCION; ?>">
                    <td><input type="text" name="grp_nombre[]" class="sig-input" data-nombre-libre
                               value="<?php echo $v($g->nombre); ?>" placeholder="Colegio, comuna, brigada…"
                               <?php echo $cerrada ? 'readonly' : ''; ?>></td>
                    <td><input type="number" name="grp_femenino[]" class="sig-input text-center js-conteo" min="0" max="999"
                               value="<?php echo (int)$g->femenino; ?>" <?php echo $cerrada ? 'readonly' : ''; ?>></td>
                    <td><input type="number" name="grp_masculino[]" class="sig-input text-center js-conteo" min="0" max="999"
                               value="<?php echo (int)$g->masculino; ?>" <?php echo $cerrada ? 'readonly' : ''; ?>></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:4px;">
                            <input type="number" name="grp_edad_min[]" class="sig-input text-center" min="0" max="120" placeholder="de"
                                   value="<?php echo $g->edad_min !== null ? (int)$g->edad_min : ''; ?>" <?php echo $cerrada ? 'readonly' : ''; ?>>
                            <span style="font-size:11px;color:var(--text-tertiary);">a</span>
                            <input type="number" name="grp_edad_max[]" class="sig-input text-center" min="0" max="120" placeholder="a"
                                   value="<?php echo $g->edad_max !== null ? (int)$g->edad_max : ''; ?>" <?php echo $cerrada ? 'readonly' : ''; ?>>
                        </div>
                    </td>
                    <td class="text-center js-total-fila" style="font-weight:700;">0</td>
                    <td class="col-actions">
                        <?php if (!$cerrada): ?>
                        <button type="button" class="row-action row-action--del js-quitar-fila" title="Quitar">
                            <i class="bi bi-x-lg"></i>
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (!$cerrada): ?>
    <div class="sig-card__body" style="padding:var(--sp-3) var(--sp-4);">
        <button type="button" class="btn-sig btn-sig--ghost btn-sig--sm" id="btnAgregarInst">
            <i class="bi bi-plus-lg"></i> Agregar institución
        </button>
    </div>
    <?php endif; ?>
</div>

<div class="row g-4 anim-slide-up" style="margin-bottom:var(--sp-5);">
    <!-- ── Acompañantes ──────────────────────────────────────────────────── -->
    <div class="col-lg-5">
        <div class="sig-card h-100" style="border-top:3px solid #6366F1;">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-person-badge" style="color:#6366F1;"></i> Acompañantes</div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <table class="sig-table" style="margin:0;">
                    <thead>
                        <tr><th></th><th class="text-center" style="width:90px;">F</th>
                            <th class="text-center" style="width:90px;">M</th>
                            <th class="text-center" style="width:70px;">Total</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="cell-strong">Docentes</td>
                            <td><input type="number" name="docentes_f" class="sig-input text-center js-conteo" min="0" max="999"
                                       value="<?php echo (int)($ficha->docentes_f ?? 0); ?>" <?php echo $cerrada ? 'readonly' : ''; ?>></td>
                            <td><input type="number" name="docentes_m" class="sig-input text-center js-conteo" min="0" max="999"
                                       value="<?php echo (int)($ficha->docentes_m ?? 0); ?>" <?php echo $cerrada ? 'readonly' : ''; ?>></td>
                            <td class="text-center" style="font-weight:700;" id="totDocentes">0</td>
                        </tr>
                        <tr>
                            <td class="cell-strong">Representantes</td>
                            <td><input type="number" name="representantes_f" class="sig-input text-center js-conteo" min="0" max="999"
                                       value="<?php echo (int)($ficha->representantes_f ?? 0); ?>" <?php echo $cerrada ? 'readonly' : ''; ?>></td>
                            <td><input type="number" name="representantes_m" class="sig-input text-center js-conteo" min="0" max="999"
                                       value="<?php echo (int)($ficha->representantes_m ?? 0); ?>" <?php echo $cerrada ? 'readonly' : ''; ?>></td>
                            <td class="text-center" style="font-weight:700;" id="totRepres">0</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ── Instituciones de apoyo ────────────────────────────────────────── -->
    <div class="col-lg-7">
        <div class="sig-card h-100" style="border-top:3px solid #16A34A;">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-shield-check" style="color:#16A34A;"></i> Instituciones de apoyo</div>
                <span style="font-size:11px;color:var(--text-tertiary);">Protección Civil, PNB, bomberos…</span>
            </div>
            <div class="sig-table-wrap" data-no-export>
                <table class="sig-table" id="tblApoyo">
                    <thead>
                        <tr><th>Institución</th>
                            <th class="text-center" style="width:80px;">F</th>
                            <th class="text-center" style="width:80px;">M</th>
                            <th class="text-center" style="width:70px;">Total</th>
                            <th class="col-actions" style="width:60px;"></th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($grupos as $g): if ($g->tipo !== RutaFicha::TIPO_APOYO) continue; ?>
                        <tr class="fila-grupo">
                            <input type="hidden" name="grp_tipo[]" value="<?php echo RutaFicha::TIPO_APOYO; ?>">
                            <input type="hidden" name="grp_edad_min[]" value="">
                            <input type="hidden" name="grp_edad_max[]" value="">
                            <td><input type="text" name="grp_nombre[]" class="sig-input" data-nombre-libre
                                       value="<?php echo $v($g->nombre); ?>" placeholder="Ej: Protección Civil"
                                       <?php echo $cerrada ? 'readonly' : ''; ?>></td>
                            <td><input type="number" name="grp_femenino[]" class="sig-input text-center js-conteo" min="0" max="999"
                                       value="<?php echo (int)$g->femenino; ?>" <?php echo $cerrada ? 'readonly' : ''; ?>></td>
                            <td><input type="number" name="grp_masculino[]" class="sig-input text-center js-conteo" min="0" max="999"
                                       value="<?php echo (int)$g->masculino; ?>" <?php echo $cerrada ? 'readonly' : ''; ?>></td>
                            <td class="text-center js-total-fila" style="font-weight:700;">0</td>
                            <td class="col-actions">
                                <?php if (!$cerrada): ?>
                                <button type="button" class="row-action row-action--del js-quitar-fila" title="Quitar">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (!$cerrada): ?>
            <div class="sig-card__body" style="padding:var(--sp-3) var(--sp-4);">
                <button type="button" class="btn-sig btn-sig--ghost btn-sig--sm" id="btnAgregarApoyo">
                    <i class="bi bi-plus-lg"></i> Agregar institución de apoyo
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ── Total y notas ─────────────────────────────────────────────────────── -->
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-5);border-top:3px solid #D97706;">
    <div class="sig-card__body" style="padding:var(--sp-4);">
        <div class="row g-4 align-items-center">
            <div class="col-md-7">
                <div style="display:flex;gap:var(--sp-5);flex-wrap:wrap;">
                    <div><span style="font-size:11px;font-weight:700;color:var(--text-tertiary);text-transform:uppercase;">Niños</span>
                        <div style="font-size:22px;font-weight:800;" id="kpiNinos">0</div></div>
                    <div><span style="font-size:11px;font-weight:700;color:var(--text-tertiary);text-transform:uppercase;">Acompañantes</span>
                        <div style="font-size:22px;font-weight:800;" id="kpiAcomp">0</div></div>
                    <div><span style="font-size:11px;font-weight:700;color:var(--text-tertiary);text-transform:uppercase;">Apoyo</span>
                        <div style="font-size:22px;font-weight:800;" id="kpiApoyo">0</div></div>
                    <div style="border-left:2px solid var(--border-subtle);padding-left:var(--sp-5);">
                        <span style="font-size:11px;font-weight:700;color:var(--text-tertiary);text-transform:uppercase;">Total general</span>
                        <div style="font-size:26px;font-weight:800;color:var(--success-600);" id="kpiTotal">0</div></div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="sig-field" style="margin:0;">
                    <label class="sig-field__label" for="lugar_exacto">Lugar exacto</label>
                    <input type="text" name="lugar_exacto" id="lugar_exacto" class="sig-input" data-nombre-libre
                           value="<?php echo $v($ficha->lugar_exacto ?? ($ruta->nombre ?? '')); ?>"
                           <?php echo $cerrada ? 'readonly' : ''; ?>>
                </div>
            </div>
        </div>
        <div class="row g-4" style="margin-top:var(--sp-2);">
            <div class="col-md-6">
                <div class="sig-field" style="margin:0;">
                    <label class="sig-field__label" for="resumen_visita">Reseña de la visita</label>
                    <textarea name="resumen_visita" id="resumen_visita" class="sig-input" rows="3"
                              placeholder="Qué se hizo, cómo salió…" <?php echo $cerrada ? 'readonly' : ''; ?>><?php echo $v($ficha->resumen_visita ?? ''); ?></textarea>
                </div>
            </div>
            <div class="col-md-6">
                <div class="sig-field" style="margin:0;">
                    <label class="sig-field__label" for="observaciones">Observaciones <span style="color:var(--text-tertiary);font-weight:400;">(opcional)</span></label>
                    <textarea name="observaciones" id="observaciones" class="sig-input" rows="3"
                              <?php echo $cerrada ? 'readonly' : ''; ?>><?php echo $v($ficha->observaciones ?? ''); ?></textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!$cerrada): ?>
<div style="display:flex;justify-content:flex-end;gap:var(--sp-3);padding-top:var(--sp-2);border-top:1px solid var(--border-subtle);"
     class="anim-slide-up">
    <button type="submit" class="btn-sig btn-sig--ghost"><i class="bi bi-save"></i> Guardar borrador</button>
    <button type="button" class="btn-sig btn-sig--primary" id="btnCerrarFicha" style="padding:0 var(--sp-6);">
        <i class="bi bi-check-lg"></i> Guardar y cerrar
    </button>
</div>
<?php endif; ?>

</form>

<script>
(function () {
    // Plantilla de fila: el formato tiene 8 renglones en blanco justamente
    // porque el número de instituciones cambia en cada salida.
    function filaInstitucion() {
        var tr = document.createElement('tr');
        tr.className = 'fila-grupo';
        tr.innerHTML =
            '<input type="hidden" name="grp_tipo[]" value="<?php echo RutaFicha::TIPO_INSTITUCION; ?>">' +
            '<td><input type="text" name="grp_nombre[]" class="sig-input" data-nombre-libre placeholder="Colegio, comuna, brigada…"></td>' +
            '<td><input type="number" name="grp_femenino[]" class="sig-input text-center js-conteo" min="0" max="999" value="0"></td>' +
            '<td><input type="number" name="grp_masculino[]" class="sig-input text-center js-conteo" min="0" max="999" value="0"></td>' +
            '<td><div style="display:flex;align-items:center;gap:4px;">' +
              '<input type="number" name="grp_edad_min[]" class="sig-input text-center" min="0" max="120" placeholder="de">' +
              '<span style="font-size:11px;color:var(--text-tertiary);">a</span>' +
              '<input type="number" name="grp_edad_max[]" class="sig-input text-center" min="0" max="120" placeholder="a">' +
            '</div></td>' +
            '<td class="text-center js-total-fila" style="font-weight:700;">0</td>' +
            '<td class="col-actions"><button type="button" class="row-action row-action--del js-quitar-fila" title="Quitar"><i class="bi bi-x-lg"></i></button></td>';
        return tr;
    }

    function filaApoyo() {
        var tr = document.createElement('tr');
        tr.className = 'fila-grupo';
        tr.innerHTML =
            '<input type="hidden" name="grp_tipo[]" value="<?php echo RutaFicha::TIPO_APOYO; ?>">' +
            '<input type="hidden" name="grp_edad_min[]" value="">' +
            '<input type="hidden" name="grp_edad_max[]" value="">' +
            '<td><input type="text" name="grp_nombre[]" class="sig-input" data-nombre-libre placeholder="Ej: Protección Civil"></td>' +
            '<td><input type="number" name="grp_femenino[]" class="sig-input text-center js-conteo" min="0" max="999" value="0"></td>' +
            '<td><input type="number" name="grp_masculino[]" class="sig-input text-center js-conteo" min="0" max="999" value="0"></td>' +
            '<td class="text-center js-total-fila" style="font-weight:700;">0</td>' +
            '<td class="col-actions"><button type="button" class="row-action row-action--del js-quitar-fila" title="Quitar"><i class="bi bi-x-lg"></i></button></td>';
        return tr;
    }

    var tbInst  = document.querySelector('#tblInstituciones tbody');
    var tbApoyo = document.querySelector('#tblApoyo tbody');
    var btnI = document.getElementById('btnAgregarInst');
    var btnA = document.getElementById('btnAgregarApoyo');

    function agregar(tb, fn) {
        tb.appendChild(fn());
        if (window.initRowActions) window.initRowActions();
        if (window.initSigturValidations) window.initSigturValidations();
        recalcular();
    }
    if (btnI) btnI.addEventListener('click', function () { agregar(tbInst, filaInstitucion); });
    if (btnA) btnA.addEventListener('click', function () { agregar(tbApoyo, filaApoyo); });

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.js-quitar-fila');
        if (!btn) return;
        var tr = btn.closest('tr');
        var tb = tr.parentNode;
        tr.remove();
        // La tabla de instituciones nunca queda sin filas: sin al menos un
        // renglón la ficha no se puede guardar.
        if (tb === tbInst && tb.querySelectorAll('tr').length === 0) agregar(tbInst, filaInstitucion);
        recalcular();
    });

    // El total del pie es la suma de los tres bloques — el mismo cálculo que
    // hace el servidor en RutaFicha::totales().
    function recalcular() {
        var ninos = 0, apoyo = 0;
        document.querySelectorAll('#tblInstituciones tbody tr').forEach(function (tr) {
            var t = num(tr, 'grp_femenino[]') + num(tr, 'grp_masculino[]');
            ninos += t;
            var c = tr.querySelector('.js-total-fila'); if (c) c.textContent = t;
        });
        document.querySelectorAll('#tblApoyo tbody tr').forEach(function (tr) {
            var t = num(tr, 'grp_femenino[]') + num(tr, 'grp_masculino[]');
            apoyo += t;
            var c = tr.querySelector('.js-total-fila'); if (c) c.textContent = t;
        });
        var doc = val('docentes_f') + val('docentes_m');
        var rep = val('representantes_f') + val('representantes_m');
        document.getElementById('totDocentes').textContent = doc;
        document.getElementById('totRepres').textContent   = rep;
        document.getElementById('kpiNinos').textContent = ninos;
        document.getElementById('kpiAcomp').textContent = doc + rep;
        document.getElementById('kpiApoyo').textContent = apoyo;
        document.getElementById('kpiTotal').textContent = ninos + doc + rep + apoyo;
    }
    function num(tr, name) {
        var el = tr.querySelector('[name="' + name + '"]');
        return el ? (parseInt(el.value, 10) || 0) : 0;
    }
    function val(name) {
        var el = document.querySelector('[name="' + name + '"]');
        return el ? (parseInt(el.value, 10) || 0) : 0;
    }

    document.addEventListener('input', function (e) {
        if (e.target.classList && e.target.classList.contains('js-conteo')) recalcular();
    });
    recalcular();

    var btnCerrar = document.getElementById('btnCerrarFicha');
    if (btnCerrar) {
        btnCerrar.addEventListener('click', function () {
            if (parseInt(document.getElementById('kpiTotal').textContent, 10) === 0) {
                showToast('Ficha vacía', 'No se puede cerrar una ficha sin personas atendidas.', 'warning');
                return;
            }
            document.getElementById('ficha_accion').value = 'cerrar';
            document.getElementById('formFicha').submit();
        });
    }
})();
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
