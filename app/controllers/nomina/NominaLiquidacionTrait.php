<?php
/**
 * Liquidación de Prestaciones Sociales (fase N-E, mig. 086) — acciones de
 * NominaController. Va en un trait aparte, como los reportes, para no inflar el
 * controlador; comparte su RBAC (`NominaController`) y sus URLs (`/nomina/...`).
 * El cálculo vive en el modelo `Liquidacion`; aquí solo se orquesta.
 */
trait NominaLiquidacionTrait
{
    /** Listado: liquidaciones hechas y egresados que aún no tienen la suya. */
    public function liquidaciones() {
        $this->requireModulo('NominaController');
        $this->view('nomina/liquidaciones', [
            'titulo'        => 'Liquidación de Prestaciones Sociales',
            'liquidaciones' => Liquidacion::todas(),
            'pendientes'    => Liquidacion::pendientes(),
        ]);
    }

    public function nuevaLiquidacion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/nomina/liquidaciones'); return; }
        $this->requireModulo('NominaController');
        $_POST = $this->sanitizePost();
        try {
            $id = Liquidacion::crear((int)($_POST['id_empleado'] ?? 0), $this->getUserId());
            flash('global_msg', 'Liquidación creada en borrador. Revise las advertencias y complete lo que el sistema no conoce antes de cerrarla.');
            header('Location: ' . URL_ROOT . '/nomina/verLiquidacion/' . $id);
            return;
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/nomina/liquidaciones');
    }

    public function verLiquidacion($id) {
        $this->requireModulo('NominaController');
        $liq = Liquidacion::find((int)$id);
        if (!$liq) {
            flash('global_msg', 'Liquidación no encontrada.', 'danger');
            header('Location: ' . URL_ROOT . '/nomina/liquidaciones');
            return;
        }
        $this->view('nomina/liquidacion', [
            'titulo' => 'Liquidación — ' . trim($liq->nombre . ' ' . $liq->apellido),
            'liq'    => $liq,
            'meses'  => Liquidacion::meses((int)$id),
            'params' => Liquidacion::params(),
        ]);
    }

    /** Fecha de pago, días corregidos de las fracciones y observaciones. */
    public function guardarLiquidacion($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/nomina/liquidaciones'); return; }
        $this->requireModulo('NominaController');
        $_POST = $this->sanitizePost();
        try {
            Liquidacion::guardarCabecera((int)$id, $_POST, $this->getUserId());
            flash('global_msg', 'Datos guardados y liquidación recalculada.');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/nomina/verLiquidacion/' . (int)$id);
    }

    /** Corrige un mes de la tabla de intereses (sueldo pasado, anticipo, días). */
    public function guardarMesLiquidacion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/nomina/liquidaciones'); return; }
        $this->requireModulo('NominaController');
        $_POST = $this->sanitizePost();
        $idMes = (int)($_POST['id_mes'] ?? 0);
        $mes = Liquidacion::findMes($idMes);
        $idLiq = (int)($mes->id_liquidacion ?? 0);
        try {
            Liquidacion::guardarMes($idMes, $_POST, $this->getUserId());
            flash('global_msg', 'Mes corregido. Se recalculó toda la cadena de intereses.');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/nomina/verLiquidacion/' . $idLiq . '#tabla-intereses');
    }

    public function recalcularLiquidacion($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/nomina/liquidaciones'); return; }
        $this->requireModulo('NominaController');
        try {
            Liquidacion::recalcular((int)$id, $this->getUserId());
            flash('global_msg', 'Liquidación recalculada con los parámetros y las tasas vigentes. Las correcciones manuales se conservaron.');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/nomina/verLiquidacion/' . (int)$id);
    }

    /** Vuelve a sembrar los meses desde la ficha: DESCARTA las correcciones manuales. */
    public function reconstruirLiquidacion($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/nomina/liquidaciones'); return; }
        $this->requireModulo('NominaController');
        try {
            Liquidacion::recalcular((int)$id, $this->getUserId(), true);
            flash('global_msg', 'Tabla mensual reconstruida desde la ficha del trabajador. Las correcciones manuales se descartaron.', 'warning');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/nomina/verLiquidacion/' . (int)$id);
    }

    public function cerrarLiquidacion($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/nomina/liquidaciones'); return; }
        $this->requireModulo('NominaController');
        try {
            $numero = Liquidacion::cerrar((int)$id, $this->getUserId());
            flash('global_msg', 'Liquidación cerrada con el número ' . $numero . '. Ya no se puede modificar.');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/nomina/verLiquidacion/' . (int)$id);
    }

    public function anularLiquidacion($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/nomina/liquidaciones'); return; }
        $this->requireModulo('NominaController');
        $_POST = $this->sanitizePost();
        try {
            Liquidacion::anular((int)$id, $_POST['motivo_anulacion'] ?? '', $this->getUserId());
            flash('global_msg', 'Liquidación anulada. El egreso queda libre para liquidarse de nuevo.', 'warning');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/nomina/verLiquidacion/' . (int)$id);
    }

    /** Hoja FORMATO imprimible (el documento que firman el trabajador y Talento Humano). */
    public function imprimirLiquidacion($id) {
        $this->requireModulo('NominaController');
        $liq = Liquidacion::find((int)$id);
        if (!$liq) {
            flash('global_msg', 'Liquidación no encontrada.', 'danger');
            header('Location: ' . URL_ROOT . '/nomina/liquidaciones');
            return;
        }
        $this->view('nomina/liquidacion_imprimible', ['liq' => $liq]);
    }

    /**
     * Las tres hojas del formato del cliente: FORMATO, INTERESES e INTERÉS DE
     * MORA, con `XlsxMultiSheet`.
     */
    public function exportarLiquidacion($id) {
        $this->requireModulo('NominaController');
        $liq = Liquidacion::find((int)$id);
        if (!$liq) {
            flash('global_msg', 'Liquidación no encontrada.', 'danger');
            header('Location: ' . URL_ROOT . '/nomina/liquidaciones');
            return;
        }
        $meses = Liquidacion::meses((int)$id);
        $f  = fn($v) => number_format((float)$v, 2, ',', '.');
        $f4 = fn($v) => number_format((float)$v, 4, ',', '.');
        $nombre = trim($liq->nombre . ' ' . $liq->apellido);
        $fecha  = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '—';
        $ident  = $nombre . ' · C.I. ' . $liq->cedula . ' · ' . ($liq->numero ?: 'BORRADOR');

        $xlsx = new XlsxMultiSheet();

        // ── FORMATO ───────────────────────────────────────────────────────
        $xlsx->nuevaHoja('FORMATO');
        $xlsx->membrete('LIQUIDACIÓN DE PRESTACIONES SOCIALES', 4, ' · ' . $ident);
        $xlsx->filaCeldas(['Nombres y apellidos', $nombre, 'Cédula', $liq->cedula], XlsxMultiSheet::S_DATA);
        $xlsx->filaCeldas(['Cargo', $liq->cargo ?: '—', 'Condición laboral', $liq->condicion ?: '—'], XlsxMultiSheet::S_DATA);
        $xlsx->filaCeldas(['Dirección o departamento', $liq->departamento ?: '—', 'Tipo de personal', $liq->tipo_personal ?: '—'], XlsxMultiSheet::S_DATA);
        $xlsx->filaCeldas(['Fecha de ingreso', $fecha($liq->fecha_ingreso), 'Fecha de egreso', $fecha($liq->fecha_egreso)], XlsxMultiSheet::S_DATA);
        $xlsx->filaCeldas(['Tiempo laboral', (int)$liq->anios . ' años, ' . (int)$liq->meses . ' meses, ' . (int)$liq->dias . ' días',
                           'Motivo del egreso', $liq->motivo_egreso ?: '—'], XlsxMultiSheet::S_DATA);
        $xlsx->filaVacia(6);

        $xlsx->filaCeldas(['Conceptos', 'Monto mensual', 'Monto diario', ''], XlsxMultiSheet::S_HEADER);
        $diasMes = (float)Liquidacion::params()['nomina_dias_mes'] ?: 30;
        foreach ([
            'Sueldo base' => $liq->sueldo_base, 'Prima profesional' => $liq->prima_profesional,
            'Prima responsabilidad' => $liq->prima_responsabilidad, 'Prima antigüedad' => $liq->prima_antiguedad,
            'Prima por hijo' => $liq->prima_hijos, 'Bono de transporte' => $liq->bono_transporte,
            'Prima FOND' => $liq->prima_fond, 'Prima discapacidad' => $liq->prima_discapacidad,
            'Caja de ahorro' => $liq->caja_ahorro,
        ] as $c => $v) {
            $xlsx->filaCeldas([$c, $f($v), $f((float)$v / $diasMes), ''], XlsxMultiSheet::S_DATA);
        }
        $xlsx->filaCeldas(['SUB-TOTAL SALARIO', $f($liq->subtotal_mensual), $f($liq->salario_normal_diario), ''], XlsxMultiSheet::S_TOTAL);
        $xlsx->filaCeldas(['Alíc. bono vacacional (' . (float)$liq->dias_alic_vac . ' días)', '', $f($liq->alic_vac_diaria), ''], XlsxMultiSheet::S_DATA);
        $xlsx->filaCeldas(['Alíc. bonif. fin de año (' . (float)$liq->dias_alic_fin . ' días)', '', $f($liq->alic_fin_diaria), ''], XlsxMultiSheet::S_DATA);
        $xlsx->filaCeldas(['TOTAL SALARIO INTEGRAL', '', $f($liq->salario_integral_diario), ''], XlsxMultiSheet::S_TOTAL);
        $xlsx->filaVacia(6);

        $xlsx->filaCeldas(['Conceptos', 'Tiempo (días)', 'Alícuota', 'Total'], XlsxMultiSheet::S_HEADER);
        foreach (self::conceptosLiquidacion($liq) as $c) {
            $xlsx->filaCeldas([$c['concepto'], $c['tiempo'] === null ? '' : $f($c['tiempo']),
                               $c['alicuota'] === null ? '' : $f($c['alicuota']), $f($c['total'])], XlsxMultiSheet::S_DATA);
        }
        $xlsx->filaCeldas(['SUB-TOTAL PRESTACIONES SOCIALES', '', '', $f($liq->subtotal)], XlsxMultiSheet::S_TOTAL);
        $xlsx->filaCeldas(['TOTAL DE INTERÉS DE MORA', '', '', $f($liq->interes_mora)], XlsxMultiSheet::S_TOTAL);
        $xlsx->filaCeldas(['TOTAL DE PRESTACIONES SOCIALES', '', '', $f($liq->total)], XlsxMultiSheet::S_TOTAL);
        $xlsx->filaFusionada('Antigüedad: art. 142 d LOTTT — se paga la mayor entre la garantía depositada ('
            . $f($liq->monto_garantia) . ') y el retroactivo de ' . (float)$liq->dias_retroactivo . ' días ('
            . $f($liq->monto_retroactivo) . ').', 4, XlsxMultiSheet::S_META);

        // ── INTERESES ─────────────────────────────────────────────────────
        $cols = ['Mes', 'Salario base', 'Prima profesional', 'Prima antigüedad', 'Bono transporte', 'Bono por hijos',
                 'Total mensual', 'Salario diario normal', 'Alíc. bono vacacional', 'Alíc. bono fin de año',
                 'Salario integral', 'Días prest. sociales', 'Días abonados (Bs)', 'Intereses + prest. + antigüedad',
                 'Tasa BCV mensual %', 'Intereses', 'Intereses + prestaciones', 'Anticipos otorgados'];
        $xlsx->nuevaHoja('INTERESES');
        $xlsx->membrete('CÁLCULO DE PRESTACIONES SOCIALES', count($cols), ' · ' . $ident);
        $xlsx->filaCeldas($cols, XlsxMultiSheet::S_HEADER);
        foreach ($meses as $i => $m) {
            $xlsx->filaCeldas([
                date('m/Y', strtotime($m->mes)), $f($m->salario_base), $f($m->prima_profesional), $f($m->prima_antiguedad),
                $f($m->bono_transporte), $f($m->bono_hijos), $f($m->total_mensual), $f4($m->salario_diario),
                $f4($m->alic_vac), $f4($m->alic_fin), $f4($m->salario_integral), $f($m->dias_deposito),
                $f($m->monto_deposito), $f($m->saldo_antes_interes),
                $m->tasa_mensual === null ? 'SIN TASA' : $f4($m->tasa_mensual),
                $f($m->interes), $f($m->saldo), $f($m->anticipo),
            ], $i % 2 ? XlsxMultiSheet::S_ZEBRA : XlsxMultiSheet::S_DATA);
        }
        $xlsx->filaFusionada('TOTALES: depósitos ' . $f($liq->monto_garantia) . ' (' . (float)$liq->dias_garantia . ' días) · intereses '
            . $f($liq->intereses) . ' · anticipos ' . $f($liq->anticipos) . ' · saldo '
            . $f((float)$liq->monto_garantia + (float)$liq->intereses - (float)$liq->anticipos), count($cols), XlsxMultiSheet::S_TOTAL);

        // ── INTERÉS DE MORA ───────────────────────────────────────────────
        $xlsx->nuevaHoja('INTERES DE MORA');
        $xlsx->membrete('INTERESES DE MORA', 3, ' · ' . $ident);
        $xlsx->filaCeldas(['Monto (sub-total de prestaciones)', $f($liq->subtotal), ''], XlsxMultiSheet::S_DATA);
        $xlsx->filaCeldas(['Fecha de egreso', $fecha($liq->fecha_egreso), ''], XlsxMultiSheet::S_DATA);
        $xlsx->filaCeldas(['Fecha de pago', $fecha($liq->fecha_pago), ''], XlsxMultiSheet::S_DATA);
        $xlsx->filaCeldas(['Mes', 'Tasa mensual %', 'Interés'], XlsxMultiSheet::S_HEADER);
        foreach (json_decode($liq->detalle_mora ?: '[]', true) as $i => $m) {
            $xlsx->filaCeldas([$m['periodo'], $f($m['tasa_mensual']), $f($m['interes'])],
                              $i % 2 ? XlsxMultiSheet::S_ZEBRA : XlsxMultiSheet::S_DATA);
        }
        $xlsx->filaCeldas(['TOTAL INTERÉS', '', $f($liq->interes_mora)], XlsxMultiSheet::S_TOTAL);

        $xlsx->descargar('liquidacion_' . preg_replace('/\D/', '', (string)$liq->cedula));
    }

    /**
     * Los renglones de conceptos del FORMATO, en su orden. Compartido por la
     * pantalla, el imprimible y el Excel para que no puedan discrepar.
     */
    public static function conceptosLiquidacion($liq): array
    {
        $anio = substr((string)$liq->fecha_egreso, 0, 4);
        $esGarantia = $liq->antiguedad_aplicada === 'garantia';
        $diasGar = (float)$liq->dias_garantia;
        return [
            [
                'concepto' => $esGarantia
                    ? 'Antigüedad — garantía depositada (Art. 142 literales A y B LOTTT)'
                    : 'Antigüedad (Art. 142 Literal C LOTTT)',
                'tiempo'   => $esGarantia ? $diasGar : (float)$liq->dias_retroactivo,
                'alicuota' => $esGarantia ? ($diasGar > 0 ? (float)$liq->monto_garantia / $diasGar : null) : (float)$liq->salario_integral_diario,
                'total'    => (float)$liq->monto_antiguedad,
            ],
            ['concepto' => 'Fracción no disfrutada de vacaciones (Art. 190 LOTTT)',
             'tiempo' => (float)$liq->dias_vacaciones, 'alicuota' => (float)$liq->salario_normal_diario, 'total' => (float)$liq->monto_vacaciones],
            ['concepto' => 'Fracción bono vacacional (Art. 192 LOTTT)',
             'tiempo' => (float)$liq->dias_bono_vac, 'alicuota' => (float)$liq->salario_normal_diario, 'total' => (float)$liq->monto_bono_vac],
            ['concepto' => 'Fracción bonificación de fin de año ' . $anio,
             'tiempo' => (float)$liq->dias_fin_anio, 'alicuota' => (float)$liq->salario_normal_diario, 'total' => (float)$liq->monto_fin_anio],
            ['concepto' => 'Intereses causados (Art. 143 LOTTT)', 'tiempo' => null, 'alicuota' => null, 'total' => (float)$liq->intereses],
            ['concepto' => 'Descuento de anticipos de prestaciones sociales (Art. 144 LOTTT)',
             'tiempo' => null, 'alicuota' => null, 'total' => -(float)$liq->anticipos],
        ];
    }

    // =====================================================================
    // Tasas del BCV (interés de prestaciones) — se cargan en Parámetros
    // =====================================================================

    public function guardarTasaBcv() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/nomina/parametros'); return; }
        $this->requireModulo('NominaController');
        $_POST = $this->sanitizePost();
        try {
            Liquidacion::guardarTasaBcv($_POST['periodo'] ?? '', $_POST['tasa_prestaciones'] ?? '',
                                        $_POST['tasa_activa'] ?? '', $_POST['observaciones'] ?? null, $this->getUserId());
            flash('global_msg', 'Tasa del BCV guardada. Las liquidaciones en borrador la toman al recalcularlas; las cerradas no cambian.');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/nomina/parametros#tasas-bcv');
    }

    public function eliminarTasaBcv() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/nomina/parametros'); return; }
        $this->requireModulo('NominaController');
        $_POST = $this->sanitizePost();
        try {
            Liquidacion::eliminarTasaBcv($_POST['periodo'] ?? '', $this->getUserId());
            flash('global_msg', 'Tasa eliminada.', 'warning');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/nomina/parametros#tasas-bcv');
    }
}
