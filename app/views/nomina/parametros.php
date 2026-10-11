<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">RRHH · Talento Humano · Nómina</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Parámetros del mes'; ?></h1>
        <p class="page__subtitle">
            Todo lo que interviene en el cálculo se edita desde aquí: nada está fijo en el programa.
            La cesta ticket y la tasa del dólar se guardan con su mes; los porcentajes de las primas,
            cuando cambie la contratación colectiva.
        </p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/nomina/quincenal" class="btn-sig btn-sig--ghost"><i class="bi bi-cash-stack"></i> Nómina quincenal</a>
        <a href="<?php echo URL_ROOT; ?>/nomina/liquidaciones" class="btn-sig btn-sig--ghost"><i class="bi bi-briefcase"></i> Liquidaciones</a>
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalMes" onclick="nuevoMes()">
            <i class="bi bi-plus-lg"></i> Cargar mes
        </button>
    </div>
</div>

<div class="sig-alert sig-alert--info anim-slide-up u-mb-sp-5">
    <i class="bi bi-shield-check"></i>
    <div>
        <strong>Cambiar un parámetro no altera una nómina ya calculada.</strong>
        Cada quincena guarda los porcentajes, la cesta ticket y la tasa con los que se hizo, así que
        una quincena <em>cerrada</em> conserva sus números para siempre. Una quincena en
        <em>borrador</em> sí toma los valores nuevos cuando se pulsa <em>Recalcular</em> — que es
        justamente para lo que sirve.
    </div>
</div>

<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
    <div class="sig-card__head"><div class="sig-card__title"><i class="bi bi-calendar-month"></i> Cesta ticket y tasa del dólar por mes</div></div>
    <div class="sig-card__body u-p-0">
        <div class="sig-table-wrap" data-tabla-buscable data-por-pagina="12">
            <table class="sig-table">
                <thead>
                    <tr>
                        <th>Mes</th>
                        <th class="text-end">Cesta ticket</th>
                        <th class="text-end">Tasa del dólar</th>
                        <th>Origen de la tasa</th>
                        <th>Observaciones</th>
                        <th class="col-actions">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['meses'])): ?>
                        <tr><td colspan="6" class="sig-table-empty">
                            Ningún mes cargado. Sin la cesta ticket y la tasa del dólar del mes no se puede generar la nómina.
                        </td></tr>
                    <?php else: foreach ($data['meses'] as $m): ?>
                        <tr>
                            <td class="cell-strong"><?php echo htmlspecialchars($m->periodo); ?></td>
                            <td class="text-end u-num"><?php echo number_format((float)$m->monto_cesta_ticket, 2, ',', '.'); ?></td>
                            <td class="text-end u-num"><?php echo number_format((float)$m->tasa_dolar, 4, ',', '.'); ?></td>
                            <td class="u-fs-12px">
                                <?php if (($m->tasa_fuente ?? '') === 'BCV'): ?>
                                    <span class="sig-badge sig-badge--info"><i class="bi bi-bank"></i> BCV</span>
                                    <?php if (!empty($m->tasa_fecha_valor)): ?>
                                        <div class="u-c-text-tertiary u-mt-2px">
                                            Fecha valor <?php echo date('d/m/Y', strtotime($m->tasa_fecha_valor)); ?>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="sig-badge sig-badge--neutral"><i class="bi bi-pencil"></i> Manual</span>
                                <?php endif; ?>
                            </td>
                            <td class="u-fs-12px u-c-text-secondary"><?php echo htmlspecialchars($m->observaciones ?? '—'); ?></td>
                            <td class="col-actions">
                                <button class="row-action row-action--edit"
                                        onclick='editarMes(<?php echo json_encode($m, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-mortarboard"></i> Prima de profesionalización</div>
                <div class="u-ai-center u-gap-10px" style="display:flex;">
                    <span class="u-fs-12px u-c-text-tertiary">% sobre el sueldo base quincenal</span>
                    <button type="button" class="btn-sig btn-sig--ghost btn-sig--sm" onclick="nuevoGrado()">
                        <i class="bi bi-plus-lg"></i> Agregar
                    </button>
                </div>
            </div>
            <div class="sig-card__body u-p-0">
                <div class="sig-table-wrap" data-no-export>
                    <table class="sig-table">
                        <thead><tr><th>Código</th><th>Grado de instrucción</th><th class="text-end">%</th><th class="col-actions">Acciones</th></tr></thead>
                        <tbody>
                            <?php foreach ($data['grados'] ?? [] as $g): ?>
                                <tr<?php echo $g->is_active ? '' : ' style="opacity:.55;"'; ?>>
                                    <td class="cell-strong">
                                        <?php echo htmlspecialchars($g->codigo); ?>
                                        <?php if (!$g->is_active): ?>
                                            <span class="sig-badge sig-badge--neutral">De baja</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($g->nombre); ?></td>
                                    <td class="text-end u-num"><?php echo number_format((float)$g->porcentaje, 2, ',', '.'); ?></td>
                                    <td class="col-actions">
                                        <button class="row-action row-action--edit"
                                                onclick='editarGrado(<?php echo json_encode($g, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                            <i class="bi bi-pencil"></i> Editar
                                        </button>
                                        <?php if ($g->is_active): ?>
                                            <form action="<?php echo URL_ROOT; ?>/nomina/desactivarGrado" method="POST" style="display:inline"
                                                  onsubmit="return confirm('¿Dar de baja el grado <?php echo htmlspecialchars($g->codigo); ?>?\n\nEl personal que lo tenga registrado pasará a cobrar 0 % de prima de profesionalización, y aparecerá con una advertencia en la próxima nómina.');">
                                                <input type="hidden" name="codigo" value="<?php echo htmlspecialchars($g->codigo); ?>">
                                                <button type="submit" class="row-action row-action--del">
                                                    <i class="bi bi-slash-circle"></i> Dar de baja
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-graph-up-arrow"></i> Prima de antigüedad</div>
                <div class="u-ai-center u-gap-10px" style="display:flex;">
                    <span class="u-fs-12px u-c-text-tertiary">Años en la administración pública</span>
                    <button type="button" class="btn-sig btn-sig--ghost btn-sig--sm" onclick="nuevoTramo()">
                        <i class="bi bi-plus-lg"></i> Agregar
                    </button>
                </div>
            </div>
            <div class="sig-card__body u-p-0">
                <div class="sig-table-wrap" data-no-export style="max-height:340px;overflow-y:auto;">
                    <table class="sig-table">
                        <thead><tr><th>Años</th><th class="text-end">%</th><th class="col-actions">Acciones</th></tr></thead>
                        <tbody>
                            <?php foreach ($data['escala'] ?? [] as $t): ?>
                                <tr>
                                    <td class="cell-strong">
                                        <?php echo (int)$t->anios; ?>
                                        <?php if ($t->es_tope): ?><span class="sig-badge sig-badge--info">y más — tope</span><?php endif; ?>
                                    </td>
                                    <td class="text-end u-num"><?php echo number_format((float)$t->porcentaje, 2, ',', '.'); ?></td>
                                    <td class="col-actions">
                                        <button class="row-action row-action--edit"
                                                onclick="editarTramo(<?php echo (int)$t->anios; ?>, '<?php echo number_format((float)$t->porcentaje, 3, '.', ''); ?>')">
                                            <i class="bi bi-pencil"></i> Editar
                                        </button>
                                        <form action="<?php echo URL_ROOT; ?>/nomina/eliminarAntiguedad" method="POST" style="display:inline"
                                              onsubmit="return confirm('¿Eliminar el tramo de <?php echo (int)$t->anios; ?> año(s)?<?php echo $t->es_tope ? '\n\nEs el TOPE de la escala: al quitarlo, el tope pasa al año más alto que quede y quienes superen esa antigüedad cobrarán ese porcentaje.' : ''; ?>');">
                                            <input type="hidden" name="anios" value="<?php echo (int)$t->anios; ?>">
                                            <button type="submit" class="row-action row-action--del">
                                                <i class="bi bi-trash"></i> Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="u-p-10px-14px u-bdt-1px-solid-border-subtle u-fs-12px u-c-text-tertiary">
                    <i class="bi bi-info-circle"></i>
                    El <strong>último tramo rige para todos los años siguientes</strong>: por eso el tope
                    no se marca a mano, se calcula solo al guardar.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="sig-card anim-slide-up">
    <div class="sig-card__head">
        <div class="sig-card__title"><i class="bi bi-sliders"></i> Montos y porcentajes del cálculo</div>
        <button type="button" class="btn-sig btn-sig--ghost btn-sig--sm" onclick="editarEscalares()">
            <i class="bi bi-pencil"></i> Editar
        </button>
    </div>
    <div class="sig-card__body">
        <p class="u-fs-13px u-c-text-secondary u-mb-sp-3">
            Ninguno está escrito en el código: son parámetros de contratación colectiva y se editan aquí.
        </p>
        <div class="row">
            <?php
            $etiquetas = [
                'nomina_bono_transporte_mensual' => 'Bono de transporte (mensual)',
                'nomina_monto_por_hijo'          => 'Prima por hijo (quincenal)',
                'nomina_becas_por_hijo'          => 'Becas por hijo',
                'nomina_semanas_default'         => 'Semanas (SSO/LRPPF/aportes)',
                'nomina_pct_sso_trabajador'      => 'SSO trabajador %',
                'nomina_pct_faov_trabajador'     => 'FAOV trabajador %',
                'nomina_pct_lrppf_trabajador'    => 'LRPPF trabajador %',
                'nomina_pct_sso_patronal'        => 'SSO patronal %',
                'nomina_pct_faov_patronal'       => 'FAOV patronal %',
                'nomina_pct_rpe_patronal'        => 'RPE patronal %',
                'nomina_dias_bono_fin_anio'      => 'Días bono fin de año',
                'nomina_dias_base_anio'          => 'Días base del año',
                'nomina_dias_mes'                => 'Días del mes (salario diario)',
                'nomina_pct_bono_50'             => 'Bono 50 % — porcentaje',
            ];
            foreach ($etiquetas as $clave => $label):
                $val = $data['escalares'][$clave] ?? null;
            ?>
                <div class="col-md-3 col-sm-6 u-mb-sp-3">
                    <div class="u-fs-11px u-tt-uppercase u-ls-_06em u-c-text-tertiary"><?php echo $label; ?></div>
                    <div class="u-fw-700 u-fvn-tabular-nums"><?php echo $val === null ? '—' : rtrim(rtrim(number_format((float)$val, 2, ',', '.'), '0'), ','); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <hr class="u-m-sp-4-0 u-bdt-1px-solid-border-subtle" style="border:none;">

        <div class="u-fs-13px u-fw-700 u-mb-sp-2">
            <i class="bi bi-calendar2-check"></i> Días base del bono vacacional, por tipo de personal
        </div>
        <p class="u-fs-12px u-c-text-tertiary u-mb-sp-3">
            Beneficio de <strong>contrato colectivo</strong>, superior al mínimo de la LOTTT (15 + 1 por año, tope 30).
        </p>
        <div class="row">
            <?php foreach (($data['diasBono'] ?? []) as $tipo => $dias): ?>
                <div class="col-md-3 col-sm-6 u-mb-sp-3">
                    <div class="u-fs-11px u-tt-uppercase u-ls-_06em u-c-text-tertiary"><?php echo htmlspecialchars($tipo); ?></div>
                    <div class="u-fw-700 u-fvn-tabular-nums">
                        <?php echo ($dias === null || $dias === '') ? '—' : (int)$dias . ' días'; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <hr class="u-m-sp-4-0 u-bdt-1px-solid-border-subtle" style="border:none;">

        <div class="u-fs-13px u-fw-700 u-mb-sp-2">
            <i class="bi bi-briefcase"></i> Liquidación de prestaciones sociales y vacaciones
        </div>
        <p class="u-fs-12px u-c-text-tertiary u-mb-sp-3">
            Las cifras de la LOTTT (arts. 142 y 190) también son parámetros: si cambia la ley o el contrato,
            se corrigen aquí y el sistema sigue calculando.
        </p>
        <?php
        $liqP = $data['liq'] ?? [];
        $num  = fn($v) => rtrim(rtrim(number_format((float)$v, 2, ',', '.'), '0'), ',');
        $mostrarLiq = [
            'Días de garantía por trimestre'      => $num($liqP['liq_dias_garantia_trimestre'] ?? 0),
            'Días adicionales por año'            => $num($liqP['liq_dias_adicionales_anio'] ?? 0)
                                                     . ' desde el año ' . $num($liqP['liq_dias_adicionales_desde_anio'] ?? 0),
            'Tope de días adicionales'            => $num($liqP['liq_dias_adicionales_tope'] ?? 0),
            'Retroactivo: días por año'           => $num($liqP['liq_dias_retroactivo_anio'] ?? 0),
            'Fracción que cuenta como año'        => 'más de ' . $num($liqP['liq_meses_fraccion_retroactivo'] ?? 0) . ' meses',
            'Trimestres de la garantía'           => ($liqP['liq_trimestre_modo'] ?? '') === 'ingreso'
                                                     ? 'Desde el ingreso' : 'Calendario (mar · jun · sep · dic)',
            'Vacaciones: días del 1.er año'       => (int)($data['vacRegla']['base'] ?? 15) . ' + 1 por año',
            'Vacaciones: tope'                    => (int)($data['vacRegla']['tope'] ?? 30) . ' días',
        ];
        ?>
        <div class="row">
            <?php foreach ($mostrarLiq as $label => $valor): ?>
                <div class="col-md-3 col-sm-6 u-mb-sp-3">
                    <div class="u-fs-11px u-tt-uppercase u-ls-_06em u-c-text-tertiary"><?php echo $label; ?></div>
                    <div class="u-fw-700 u-fvn-tabular-nums"><?php echo htmlspecialchars($valor); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Tasas del BCV para la liquidación (mig. 086) -->
<div class="sig-card anim-slide-up" id="tasas-bcv" style="margin-top:var(--sp-6);">
    <div class="sig-card__head">
        <div class="sig-card__title"><i class="bi bi-percent"></i> Tasa de interés de prestaciones sociales (BCV) por mes</div>
        <button type="button" class="btn-sig btn-sig--ghost btn-sig--sm" onclick="nuevaTasaBcv()">
            <i class="bi bi-plus-lg"></i> Cargar mes
        </button>
    </div>
    <div class="sig-card__body u-p-0">
        <div class="u-p-10px-14px u-fs-12_5px u-c-text-secondary u-bdb-1px-solid-border-subtle">
            <i class="bi bi-info-circle"></i>
            Es la tasa <strong>anual</strong> que publica el BCV para los intereses de las prestaciones
            (art. 143 LOTTT) — <strong>no</strong> es el tipo de cambio. La liquidación aplica cada mes la
            doceava parte, como la hoja INTERESES. La <em>tasa activa</em> es opcional: se usa para el interés
            de mora; si falta, se usa la de prestaciones.
        </div>
        <div class="sig-table-wrap" data-tabla-buscable data-por-pagina="12" data-titulo-export="Tasas de interés de prestaciones sociales (BCV)">
            <table class="sig-table">
                <thead>
                    <tr>
                        <th>Mes</th>
                        <th class="text-end">Prestaciones % anual</th>
                        <th class="text-end">% mensual</th>
                        <th class="text-end">Activa % anual (mora)</th>
                        <th>Observaciones</th>
                        <th class="col-actions">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['tasasBcv'])): ?>
                        <tr><td colspan="6" class="sig-table-empty">
                            Ninguna tasa cargada. Sin ellas, los intereses de las liquidaciones quedan en 0 y el sistema lo advierte.
                        </td></tr>
                    <?php else: foreach ($data['tasasBcv'] as $t): ?>
                        <tr>
                            <td class="cell-strong"><?php echo htmlspecialchars($t->periodo); ?></td>
                            <td class="text-end u-num"><?php echo number_format((float)$t->tasa_prestaciones, 2, ',', '.'); ?></td>
                            <td class="text-end u-num"><?php echo number_format((float)$t->tasa_prestaciones / 12, 4, ',', '.'); ?></td>
                            <td class="text-end u-num"><?php echo $t->tasa_activa === null ? '—' : number_format((float)$t->tasa_activa, 2, ',', '.'); ?></td>
                            <td class="u-fs-12px u-c-text-secondary"><?php echo htmlspecialchars($t->observaciones ?? '—'); ?></td>
                            <td class="col-actions">
                                <button class="row-action row-action--edit"
                                        onclick='editarTasaBcv(<?php echo json_encode($t, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                                <form action="<?php echo URL_ROOT; ?>/nomina/eliminarTasaBcv" method="POST" style="display:inline;"
                                      onsubmit="return confirm('¿Eliminar la tasa de <?php echo htmlspecialchars($t->periodo); ?>?');">
                                    <input type="hidden" name="periodo" value="<?php echo htmlspecialchars($t->periodo); ?>">
                                    <button type="submit" class="row-action row-action--del"><i class="bi bi-trash"></i> Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: tasa del BCV -->
<div class="modal fade" id="modalTasaBcv" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/nomina/guardarTasaBcv" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTasaBcvLabel">Tasa del BCV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="sig-field mb-3">
                    <label class="sig-field__label" for="tb_periodo">Mes <span class="req">*</span></label>
                    <input type="month" name="periodo" id="tb_periodo" class="sig-input" required>
                </div>
                <div class="sig-field mb-3">
                    <label class="sig-field__label" for="tb_prest">Tasa de prestaciones sociales (% anual) <span class="req">*</span></label>
                    <input type="number" step="0.0001" min="0" name="tasa_prestaciones" id="tb_prest" class="sig-input" required placeholder="Ej: 58,35">
                </div>
                <div class="sig-field mb-3">
                    <label class="sig-field__label" for="tb_activa">Tasa activa (% anual, para la mora)</label>
                    <input type="number" step="0.0001" min="0" name="tasa_activa" id="tb_activa" class="sig-input">
                </div>
                <div class="sig-field">
                    <label class="sig-field__label" for="tb_obs">Observaciones</label>
                    <input type="text" name="observaciones" id="tb_obs" class="sig-input" maxlength="255" placeholder="Ej: publicación del BCV">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: montos, porcentajes y días base -->
<div class="modal fade" id="modalEscalares" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="<?php echo URL_ROOT; ?>/nomina/guardarEscalares" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Montos y porcentajes del cálculo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="u-fs-12_5px u-c-text-secondary u-mb-sp-3">
                    Cambiarlos no altera una quincena ya cerrada. Una en borrador los toma al recalcularla.
                </p>

                <div class="u-fs-12px u-fw-700 u-tt-uppercase u-ls-_06em u-c-text-tertiary u-mb-sp-2">
                    Montos
                </div>
                <div class="row g-3" style="margin-bottom:var(--sp-4);">
                    <?php
                    // Cada grupo lleva su paso: los montos en bolívares admiten
                    // céntimos; los porcentajes, tres decimales (hay tramos como
                    // 1,7 %); los días y las semanas son enteros.
                    $grupos = [
                        'montos' => ['paso' => '0.01', 'campos' => [
                            'nomina_bono_transporte_mensual' => 'Bono de transporte (mensual, Bs)',
                            'nomina_monto_por_hijo'          => 'Prima por hijo (quincenal, Bs)',
                            'nomina_becas_por_hijo'          => 'Becas por hijo (Bs)',
                        ]],
                        'porcentajes' => ['paso' => '0.001', 'campos' => [
                            'nomina_pct_sso_trabajador'   => 'SSO trabajador %',
                            'nomina_pct_faov_trabajador'  => 'FAOV trabajador %',
                            'nomina_pct_lrppf_trabajador' => 'LRPPF trabajador %',
                            'nomina_pct_sso_patronal'     => 'SSO patronal %',
                            'nomina_pct_faov_patronal'    => 'FAOV patronal %',
                            'nomina_pct_rpe_patronal'     => 'RPE patronal %',
                            'nomina_pct_bono_50'          => 'Bono 50 % — porcentaje del sueldo base',
                        ]],
                        'dias' => ['paso' => '1', 'campos' => [
                            'nomina_semanas_default'    => 'Semanas (SSO/LRPPF/aportes)',
                            'nomina_dias_bono_fin_anio' => 'Días bono fin de año (aguinaldo)',
                            'nomina_dias_base_anio'     => 'Días base del año',
                            'nomina_dias_mes'           => 'Días del mes (salario diario)',
                        ]],
                        'liquidacion' => ['paso' => '0.01', 'campos' => [
                            'liq_dias_garantia_trimestre'     => 'Días de garantía por trimestre (art. 142 a)',
                            'liq_dias_adicionales_anio'       => 'Días adicionales por año (art. 142 b)',
                            'liq_dias_adicionales_desde_anio' => 'Días adicionales desde el año',
                            'liq_dias_adicionales_tope'       => 'Tope de días adicionales',
                            'liq_dias_retroactivo_anio'       => 'Retroactivo: días por año (art. 142 c)',
                            'liq_meses_fraccion_retroactivo'  => 'Meses que debe superar la fracción',
                        ]],
                    ];
                    // Los valores de liquidación vienen de su propio modelo.
                    $data['escalares'] = ($data['escalares'] ?? []) + ($data['liq'] ?? []);
                    $pintar = function(array $campos, string $paso) use ($data) {
                        foreach ($campos as $clave => $label):
                            $val = $data['escalares'][$clave] ?? '';
                    ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="sig-field u-m-0">
                                <label class="sig-field__label" for="esc_<?php echo $clave; ?>"><?php echo $label; ?></label>
                                <input type="number" step="<?php echo $paso; ?>" min="0"
                                       name="<?php echo $clave; ?>" id="esc_<?php echo $clave; ?>"
                                       class="sig-input" value="<?php echo htmlspecialchars((string)$val); ?>">
                            </div>
                        </div>
                    <?php endforeach; };
                    $pintar($grupos['montos']['campos'], $grupos['montos']['paso']); ?>
                </div>

                <div class="u-fs-12px u-fw-700 u-tt-uppercase u-ls-_06em u-c-text-tertiary u-mb-sp-2">
                    Deducciones y aportes
                </div>
                <div class="row g-3" style="margin-bottom:var(--sp-4);">
                    <?php $pintar($grupos['porcentajes']['campos'], $grupos['porcentajes']['paso']); ?>
                </div>

                <div class="u-fs-12px u-fw-700 u-tt-uppercase u-ls-_06em u-c-text-tertiary u-mb-sp-2">
                    Días y semanas
                </div>
                <div class="row g-3" style="margin-bottom:var(--sp-4);">
                    <?php $pintar($grupos['dias']['campos'], $grupos['dias']['paso']); ?>
                </div>

                <div class="u-fs-12px u-fw-700 u-tt-uppercase u-ls-_06em u-c-text-tertiary u-mb-sp-2">
                    Días base del bono vacacional, según la condición del trabajador
                </div>
                <div class="row g-3">
                    <?php foreach (BonoVacacional::CONFIG_DIAS as $tipo => $clave): ?>
                        <div class="col-md-6">
                            <div class="sig-field u-m-0">
                                <label class="sig-field__label" for="esc_<?php echo $clave; ?>"><?php echo htmlspecialchars($tipo); ?></label>
                                <input type="number" step="1" min="0"
                                       name="<?php echo $clave; ?>" id="esc_<?php echo $clave; ?>"
                                       class="sig-input" value="<?php echo htmlspecialchars((string)($data['diasBono'][$tipo] ?? '')); ?>">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <small class="u-c-text-tertiary u-fs-12px u-mt-6px" style="display:block;">
                    A todos se les suma 1 día por año de servicio. El alto nivel y la comisión de servicio
                    toman los días de su condición (fijo o contratado). Se usan en el bono vacacional, en
                    la alícuota de la quincena y en el salario integral de la liquidación.
                </small>

                <div class="u-fs-12px u-fw-700 u-tt-uppercase u-ls-_06em u-c-text-tertiary u-m-sp-4-0-sp-2">
                    Liquidación de prestaciones sociales
                </div>
                <div class="row g-3">
                    <?php $pintar($grupos['liquidacion']['campos'], $grupos['liquidacion']['paso']); ?>
                    <div class="col-md-4 col-sm-6">
                        <div class="sig-field u-m-0">
                            <label class="sig-field__label" for="esc_liq_trimestre_modo">Trimestres de la garantía</label>
                            <select name="liq_trimestre_modo" id="esc_liq_trimestre_modo" class="sig-input">
                                <option value="calendario"<?php echo ($data['liq']['liq_trimestre_modo'] ?? '') !== 'ingreso' ? ' selected' : ''; ?>>Calendario (mar · jun · sep · dic)</option>
                                <option value="ingreso"<?php echo ($data['liq']['liq_trimestre_modo'] ?? '') === 'ingreso' ? ' selected' : ''; ?>>Contados desde el ingreso</option>
                            </select>
                        </div>
                    </div>
                </div>
                <small class="u-c-text-tertiary u-fs-12px u-mt-6px" style="display:block;">
                    Con 2 días desde el año 2 y tope 30: el 2.º aniversario deposita 2 días, el 3.º 4, y así hasta 30.
                    La hoja INTERESES del cliente deposita en marzo, junio, septiembre y diciembre (modo calendario).
                </small>

                <div class="u-fs-12px u-fw-700 u-tt-uppercase u-ls-_06em u-c-text-tertiary u-m-sp-4-0-sp-2">
                    Vacaciones (art. 190 LOTTT)
                </div>
                <div class="row g-3">
                    <div class="col-md-4 col-sm-6">
                        <div class="sig-field u-m-0">
                            <label class="sig-field__label" for="esc_vacaciones_dias_base">Días hábiles del primer año</label>
                            <input type="number" step="1" min="0" name="vacaciones_dias_base" id="esc_vacaciones_dias_base"
                                   class="sig-input" value="<?php echo (int)($data['vacRegla']['base'] ?? 15); ?>">
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="sig-field u-m-0">
                            <label class="sig-field__label" for="esc_vacaciones_dias_tope">Tope de días por año</label>
                            <input type="number" step="1" min="0" name="vacaciones_dias_tope" id="esc_vacaciones_dias_tope"
                                   class="sig-input" value="<?php echo (int)($data['vacRegla']['tope'] ?? 30); ?>">
                        </div>
                    </div>
                </div>
                <small class="u-c-text-tertiary u-fs-12px u-mt-6px" style="display:block;">
                    Se suma 1 día por año de servicio hasta el tope. Lo usan el módulo Vacaciones y la fracción de
                    vacaciones de la liquidación.
                </small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: grado de instrucción -->
<div class="modal fade" id="modalGrado" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/nomina/guardarGrado" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalGradoLabel">Grado de instrucción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="sig-field mb-3">
                    <label class="sig-field__label" for="gr_codigo">Código <span class="req">*</span></label>
                    <input type="text" name="codigo" id="gr_codigo" class="sig-input" required maxlength="10"
                           style="text-transform:uppercase" placeholder="Ej: TSU">
                    <small id="gr_codigo_ayuda" style="color:var(--text-tertiary);font-size:12px;">
                        Sin espacios. Es la clave con la que las fichas del personal apuntan a este grado.
                    </small>
                </div>
                <div class="sig-field mb-3">
                    <label class="sig-field__label" for="gr_nombre">Grado de instrucción <span class="req">*</span></label>
                    <input type="text" name="nombre" id="gr_nombre" class="sig-input" required data-nombre-libre maxlength="80"
                           placeholder="Ej: Técnico Superior Universitario">
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="sig-field">
                            <label class="sig-field__label" for="gr_pct">% de prima <span class="req">*</span></label>
                            <input type="number" step="0.001" min="0" max="100" name="porcentaje" id="gr_pct" class="sig-input" required>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="sig-field">
                            <label class="sig-field__label" for="gr_orden">Orden en la lista</label>
                            <input type="number" step="1" min="0" name="orden" id="gr_orden" class="sig-input" value="0">
                        </div>
                    </div>
                </div>
                <div class="sig-field u-mt-sp-3">
                    <label class="u-ai-center u-gap-8px u-fs-13px" style="display:flex;">
                        <input type="checkbox" name="is_active" id="gr_activo" value="1" checked>
                        Vigente (se usa para calcular la prima)
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: tramo de antigüedad -->
<div class="modal fade" id="modalTramo" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/nomina/guardarAntiguedad" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTramoLabel">Tramo de antigüedad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="sig-field mb-3">
                    <label class="sig-field__label" for="tr_anios">Años de servicio <span class="req">*</span></label>
                    <input type="number" step="1" min="1" max="60" name="anios" id="tr_anios" class="sig-input" required>
                    <small class="u-c-text-tertiary u-fs-12px">
                        Si el año ya existe en la escala, se actualiza su porcentaje.
                    </small>
                </div>
                <div class="sig-field">
                    <label class="sig-field__label" for="tr_pct">% de prima <span class="req">*</span></label>
                    <input type="number" step="0.001" min="0" max="100" name="porcentaje" id="tr_pct" class="sig-input" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: cargar/editar mes -->
<div class="modal fade" id="modalMes" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/nomina/guardarParametros" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMesLabel">Parámetros del mes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="sig-field mb-3">
                    <label class="sig-field__label" for="pm_periodo">Mes <span class="req">*</span></label>
                    <input type="month" name="periodo" id="pm_periodo" class="sig-input" required>
                </div>
                <div class="sig-field mb-3">
                    <label class="sig-field__label" for="pm_cesta">Monto de cesta ticket <span class="req">*</span></label>
                    <input type="number" step="0.01" min="0" name="monto_cesta_ticket" id="pm_cesta" class="sig-input" required>
                    <small class="u-c-text-tertiary u-fs-12px">Escriba el monto que trae la <strong>maqueta de nómina</strong> que reciben cada mes.</small>
                </div>
                <div class="sig-field mb-3">
                    <label class="sig-field__label" for="pm_tasa">Tasa del dólar <span class="req">*</span></label>
                    <div class="u-gap-8px u-ai-flex-start" style="display:flex;">
                        <input type="number" step="0.0001" min="0" name="tasa_dolar" id="pm_tasa" class="sig-input" required style="flex:1;">
                        <button type="button" class="btn-sig btn-sig--ghost" id="btnConsultarBcv"
                                onclick="consultarBcv()" style="white-space:nowrap;">
                            <i class="bi bi-cloud-arrow-down"></i> Consultar BCV
                        </button>
                    </div>
                    <!-- Lo que sugirió el BCV, para que el servidor pueda saber si
                         el usuario guardó ese valor tal cual o lo corrigió. -->
                    <input type="hidden" name="tasa_sugerida"      id="pm_tasa_sugerida">
                    <input type="hidden" name="tasa_fecha_valor"   id="pm_tasa_fecha_valor">
                    <input type="hidden" name="tasa_consultada_at" id="pm_tasa_consultada_at">
                    <small id="pm_tasa_ayuda" style="color:var(--text-tertiary);font-size:12px;display:block;margin-top:4px;">
                        Escriba la tasa de la <strong>maqueta</strong> del mes. «Consultar BCV» es solo una referencia:
                        el cálculo usa el valor que quede escrito aquí.
                    </small>
                    <div id="pm_tasa_aviso" style="display:none;font-size:12px;margin-top:6px;"></div>
                </div>
                <div class="sig-field">
                    <label class="sig-field__label" for="pm_obs">Observaciones</label>
                    <input type="text" name="observaciones" id="pm_obs" class="sig-input" maxlength="255" placeholder="Ej: gaceta o fuente del monto">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
    // La sugerencia del BCV no sobrevive a reabrir el modal: si se limpiara solo
    // el campo visible, el servidor seguiría viendo la sugerencia anterior y
    // marcaría como "del BCV" una tasa tecleada a mano.
    function limpiarSugerenciaBcv() {
        ['pm_tasa_sugerida', 'pm_tasa_fecha_valor', 'pm_tasa_consultada_at']
            .forEach(function (id) { document.getElementById(id).value = ''; });
        document.getElementById('pm_tasa_aviso').style.display = 'none';
    }

    function editarEscalares() {
        new bootstrap.Modal(document.getElementById('modalEscalares')).show();
    }

    // ── Tasas del BCV (liquidación) ──────────────────────────────────────
    function nuevaTasaBcv() {
        document.getElementById('modalTasaBcvLabel').innerText = 'Cargar tasa del BCV';
        document.querySelector('#modalTasaBcv form').reset();
        document.getElementById('tb_periodo').readOnly = false;
        new bootstrap.Modal(document.getElementById('modalTasaBcv')).show();
    }

    function editarTasaBcv(t) {
        document.getElementById('modalTasaBcvLabel').innerText = 'Editar ' + t.periodo;
        document.getElementById('tb_periodo').value = t.periodo;
        document.getElementById('tb_periodo').readOnly = true;   // el mes es la clave
        document.getElementById('tb_prest').value  = t.tasa_prestaciones;
        document.getElementById('tb_activa').value = t.tasa_activa || '';
        document.getElementById('tb_obs').value    = t.observaciones || '';
        new bootstrap.Modal(document.getElementById('modalTasaBcv')).show();
    }

    // ── Grados de instrucción ────────────────────────────────────────────
    function nuevoGrado() {
        document.getElementById('modalGradoLabel').innerText = 'Agregar grado de instrucción';
        document.querySelector('#modalGrado form').reset();
        var cod = document.getElementById('gr_codigo');
        cod.readOnly = false;
        cod.value = '';
        document.getElementById('gr_activo').checked = true;
        document.getElementById('gr_codigo_ayuda').innerText =
            'Sin espacios. Es la clave con la que las fichas del personal apuntan a este grado.';
        new bootstrap.Modal(document.getElementById('modalGrado')).show();
    }

    function editarGrado(g) {
        document.getElementById('modalGradoLabel').innerText = 'Editar ' + g.codigo;
        var cod = document.getElementById('gr_codigo');
        cod.value = g.codigo;
        // El código es la clave que usan las fichas del personal: renombrarlo
        // dejaría huérfano a quien lo tenga registrado, así que no se edita.
        cod.readOnly = true;
        document.getElementById('gr_codigo_ayuda').innerText =
            'El código no se puede cambiar: las fichas del personal apuntan a él.';
        document.getElementById('gr_nombre').value = g.nombre;
        document.getElementById('gr_pct').value    = g.porcentaje;
        document.getElementById('gr_orden').value  = g.orden;
        document.getElementById('gr_activo').checked = (g.is_active === true || g.is_active === 't' || g.is_active === 1);
        new bootstrap.Modal(document.getElementById('modalGrado')).show();
    }

    // ── Escala de antigüedad ─────────────────────────────────────────────
    function nuevoTramo() {
        document.getElementById('modalTramoLabel').innerText = 'Agregar tramo de antigüedad';
        document.querySelector('#modalTramo form').reset();
        document.getElementById('tr_anios').readOnly = false;
        new bootstrap.Modal(document.getElementById('modalTramo')).show();
    }

    function editarTramo(anios, pct) {
        document.getElementById('modalTramoLabel').innerText = 'Editar el tramo de ' + anios + ' año(s)';
        document.getElementById('tr_anios').value = anios;
        document.getElementById('tr_anios').readOnly = true;   // los años son la clave del tramo
        document.getElementById('tr_pct').value = pct;
        new bootstrap.Modal(document.getElementById('modalTramo')).show();
    }

    function nuevoMes() {
        document.getElementById('modalMesLabel').innerText = 'Cargar parámetros del mes';
        document.querySelector('#modalMes form').reset();
        document.getElementById('pm_periodo').readOnly = false;
        limpiarSugerenciaBcv();
    }

    /**
     * Pide la tasa oficial al BCV y la ofrece como sugerencia. No guarda nada:
     * rellena el campo para que Talento Humano la confirme o la corrija.
     */
    function consultarBcv() {
        var btn    = document.getElementById('btnConsultarBcv');
        var aviso  = document.getElementById('pm_tasa_aviso');
        var previo = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Consultando…';
        aviso.style.display = 'none';

        fetch('<?php echo URL_ROOT; ?>/nomina/consultarTasa', { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.ok) { throw new Error(d.error || 'No se pudo consultar el BCV.'); }

                document.getElementById('pm_tasa').value                = d.tasa;
                document.getElementById('pm_tasa_sugerida').value       = d.tasa;
                document.getElementById('pm_tasa_fecha_valor').value    = d.fecha_valor || '';
                document.getElementById('pm_tasa_consultada_at').value  = d.consultada_at || '';

                // La fecha valor es el dato que evita el malentendido: el BCV no
                // publica "la tasa de hoy", publica la del próximo día hábil.
                var fecha = d.fecha_valor
                    ? d.fecha_valor.split('-').reverse().join('/')
                    : 'sin fecha publicada';
                aviso.style.color = 'var(--success-700, #15803d)';
                aviso.innerHTML = '<i class="bi bi-check-circle"></i> Tasa oficial del BCV con <strong>fecha valor '
                    + fecha + '</strong>, solo como referencia. Compárela con la de la maqueta del mes y, si difiere, '
                    + 'escriba la de la maqueta (se guardará como manual).';
                aviso.style.display = 'block';
            })
            .catch(function (e) {
                aviso.style.color = 'var(--danger-700, #b91c1c)';
                aviso.innerHTML = '<i class="bi bi-exclamation-triangle"></i> ' + e.message
                    + ' Puede cargar la tasa a mano.';
                aviso.style.display = 'block';
            })
            .finally(function () {
                btn.disabled = false;
                btn.innerHTML = previo;
            });
    }
    function editarMes(m) {
        document.getElementById('modalMesLabel').innerText = 'Editar ' + m.periodo;
        document.getElementById('pm_periodo').value = m.periodo;
        document.getElementById('pm_periodo').readOnly = true;   // el mes es la clave
        document.getElementById('pm_cesta').value = m.monto_cesta_ticket;
        document.getElementById('pm_tasa').value  = m.tasa_dolar;
        document.getElementById('pm_obs').value   = m.observaciones || '';
        limpiarSugerenciaBcv();
        // Una tasa que ya venía del BCV conserva su procedencia si no se toca.
        if (m.tasa_fuente === 'BCV') {
            document.getElementById('pm_tasa_sugerida').value      = m.tasa_dolar;
            document.getElementById('pm_tasa_fecha_valor').value   = m.tasa_fecha_valor || '';
            document.getElementById('pm_tasa_consultada_at').value = m.tasa_consultada_at || '';
        }
        new bootstrap.Modal(document.getElementById('modalMes')).show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
