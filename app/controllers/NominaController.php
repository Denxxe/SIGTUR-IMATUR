<?php
/**
 * NominaController — Bono Vacacional (R-11, 1ra entrega).
 * RBAC: rol 2 (RRHH) + admin '*' vía permisos_rol (mig.059).
 * v1 = "registro + reporte": el sistema organiza sueldo/primas/días ya
 * capturados y exporta el .xlsx multi-hoja; el total final es una celda de
 * captura/verificación manual (BonoVacacional::actualizarDetalle) hasta que
 * el cliente confirme un mes ya calculado para calibrar la fórmula exacta.
 * Liquidación de Prestaciones Sociales queda para una 2da entrega.
 */
class NominaController extends Controller {

    private function requireRoles(array $roles) {
        $rol = (int)($_SESSION['user_rol'] ?? 0);
        if (!in_array($rol, $roles)) {
            flash('global_msg', 'No tienes permiso para acceder a este módulo.', 'danger');
            header('Location: ' . URL_ROOT . '/nomina/index');
            exit;
        }
    }

    /** Listado de períodos generados. */
    public function index() {
        $this->view('nomina/index', [
            'titulo'   => 'Nómina — Bono Vacacional',
            'periodos' => BonoVacacional::periodos(),
            // Generar un período exige el mes cargado: la cesta ticket entra en
            // el sueldo diario y en la alícuota (mig. 073).
            'meses'    => Nomina::parametrosMesTodos(),
        ]);
    }

    // =====================================================================
    // Parámetros del mes (cesta ticket y tasa del dólar)
    // =====================================================================

    /**
     * Cesta ticket y tasa del dólar cambian TODOS los meses (la primera la
     * publica la UNAPRE; la segunda es el tipo de cambio con el que se paga
     * el bono de responsabilidad, pactado en divisas). Se llevan con
     * histórico para que un período cerrado se pueda reconstruir.
     */
    public function parametros() {
        $this->requireRoles([1, 2]);
        $this->view('nomina/parametros', [
            'titulo'    => 'Nómina — Parámetros del mes',
            'meses'     => Nomina::parametrosMesTodos(),
            'escalares' => Nomina::params(),
            'grados'    => Nomina::grados(),
            'escala'    => Nomina::escalaAntiguedad(),
        ]);
    }

    public function guardarParametros() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/nomina/parametros'); return; }
        $this->requireRoles([1, 2]);
        $_POST = $this->sanitizePost();
        try {
            Nomina::guardarParametrosMes(
                trim($_POST['periodo'] ?? ''),
                (float)str_replace(',', '.', $_POST['monto_cesta_ticket'] ?? '0'),
                (float)str_replace(',', '.', $_POST['tasa_dolar'] ?? '0'),
                $_POST['observaciones'] ?? null,
                $this->getUserId()
            );
            flash('global_msg', 'Parámetros del mes guardados.');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/nomina/parametros');
    }

    // =====================================================================
    // Nómina quincenal regular (fase N-C)
    // =====================================================================

    /** Listado de quincenas generadas. */
    public function quincenal() {
        $this->requireRoles([1, 2]);
        $this->view('nomina/quincenal', [
            'titulo'   => 'Nómina quincenal',
            'periodos' => Nomina::periodos(),
            'meses'    => Nomina::parametrosMesTodos(),
            'semanas'  => (int)Nomina::params()['nomina_semanas_default'],
        ]);
    }

    /** Genera una quincena: calcula el snapshot de todo el personal activo. */
    public function nuevaQuincena() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/nomina/quincenal'); return; }
        $this->requireRoles([1, 2]);
        $_POST = $this->sanitizePost();
        try {
            $id = Nomina::generarPeriodo(
                trim($_POST['periodo'] ?? ''),
                (int)($_POST['quincena'] ?? 0),
                (int)($_POST['semanas'] ?? 0) ?: null,
                $this->getUserId()
            );
            flash('global_msg', 'Quincena generada. Revise las advertencias antes de cerrarla.');
            header('Location: ' . URL_ROOT . '/nomina/verQuincena/' . $id);
            return;
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/nomina/quincenal');
    }

    /** Detalle de una quincena: 5 secciones por tipo de personal + resumen. */
    public function verQuincena($id) {
        $this->requireRoles([1, 2]);
        $periodo = Nomina::find((int)$id);
        if (!$periodo) {
            flash('global_msg', 'Quincena no encontrada.', 'danger');
            header('Location: ' . URL_ROOT . '/nomina/quincenal');
            return;
        }
        $this->view('nomina/quincena', [
            'titulo'       => 'Quincena ' . $periodo->quincena . ' — ' . $periodo->periodo,
            'periodo'      => $periodo,
            'grupos'       => Nomina::detallePorPeriodo((int)$id),
            'resumen'      => Nomina::resumen((int)$id),
            'advertencias' => Nomina::advertencias((int)$id),
        ]);
    }

    /**
     * Recalcula una quincena en Borrador con los datos actuales de las fichas.
     * Es la vía para incorporar una corrección (un sueldo que faltaba, un
     * grado mal registrado) sin perder el número de período.
     */
    public function recalcularQuincena($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/nomina/quincenal'); return; }
        $this->requireRoles([1, 2]);
        try {
            $n = Nomina::recalcular((int)$id, $this->getUserId());
            flash('global_msg', 'Quincena recalculada: ' . $n . ' empleado(s).');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/nomina/verQuincena/' . $id);
    }

    public function cerrarQuincena($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/nomina/quincenal'); return; }
        $this->requireRoles([1, 2]);
        try {
            Nomina::cerrar((int)$id, $this->getUserId());
            flash('global_msg', 'Quincena cerrada. Ya no se puede recalcular ni editar.');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/nomina/verQuincena/' . $id);
    }

    /**
     * Exporta la quincena en el formato del cliente: una hoja por tipo de
     * personal (5) + RESUMEN, con `XlsxMultiSheet`.
     */
    public function exportarQuincena($id) {
        $this->requireRoles([1, 2]);
        $periodo = Nomina::find((int)$id);
        if (!$periodo) {
            flash('global_msg', 'Quincena no encontrada.', 'danger');
            header('Location: ' . URL_ROOT . '/nomina/quincenal');
            return;
        }
        $grupos  = Nomina::detallePorPeriodo((int)$id);
        $resumen = Nomina::resumen((int)$id);
        $fmt     = fn($v) => number_format((float)$v, 2, ',', '.');
        $etiqueta = 'QUINCENA ' . $periodo->quincena . ' — ' . $periodo->periodo;

        // Encabezados comunes. La hoja de Comisión de Servicio agrega dos
        // columnas al final (lo que paga la dependencia y la diferencia).
        $base = [
            'N°', 'Cédula', 'Apellidos y Nombres', 'Cargo', 'Departamento',
            'Fecha Ingreso Admón. Pública', 'Años', 'Grado', '% Prof.', '% Antig.',
            'Sueldo Base Mensual', 'Sueldo Base Quincenal',
            'Prima Profesionalización', 'Prima Antigüedad', 'Bono Transporte',
            'N° Hijos', 'Prima por Hijos', 'Total Asignaciones', 'Total Sueldo Normal',
            'SSO 2%', 'FAOV 1%', 'LRPPF 0,5%', 'Total Deducciones', 'Neto a Cobrar',
            'SSO Patronal 4%', 'FAOV Patronal 2%', 'RPE Patronal 1,7%', 'Total Aportes',
            'Sueldo Normal Diario', 'Alícuota Bono Vacacional', 'Alícuota Bono Fin de Año',
            'Sueldo Integral Diario', 'Días Hábiles', 'Becas', 'Bono 50%',
            'Bono Responsabilidad', 'Banco', 'Cuenta Nómina', 'Observaciones',
        ];

        $xlsx = new XlsxMultiSheet();
        foreach (Nomina::TIPOS as $tipo) {
            $filas = $grupos[$tipo] ?? [];
            $esComision = ($tipo === 'Comisión de Servicio');
            $encabezados = $esComision
                ? array_merge($base, ['Sueldo Dependencia Origen', 'Diferencia a Pagar'])
                : $base;
            $ncol = count($encabezados);

            // `nuevaHoja()` ya recorta a 31 caracteres y limpia los que Excel no admite.
            $xlsx->nuevaHoja($tipo);
            $xlsx->membrete('NÓMINA QUINCENAL — ' . mb_strtoupper($tipo, 'UTF-8') . ' — ' . $etiqueta, $ncol,
                'Cesta ticket: ' . $fmt($periodo->monto_cesta_ticket)
                . ' · Tasa del dólar: ' . number_format((float)$periodo->tasa_dolar, 4, ',', '.')
                . ' · Semanas: ' . (int)$periodo->semanas);
            $xlsx->filaCeldas($encabezados, XlsxMultiSheet::S_HEADER);

            foreach ($filas as $i => $f) {
                $fila = [
                    $i + 1,
                    $f->cedula,
                    trim($f->apellido . ' ' . $f->nombre),
                    $f->cargo,
                    $f->departamento ?: '—',
                    $f->fecha_ingreso_administracion ?: $f->fecha_ingreso,
                    (int)$f->anios_administracion,
                    $f->codigo_grado ?: '—',
                    number_format((float)$f->pct_profesionalizacion, 2, ',', '.'),
                    number_format((float)$f->pct_antiguedad, 2, ',', '.'),
                    $fmt($f->sueldo_base_mensual),
                    $fmt($f->sueldo_base_quincenal),
                    $fmt($f->prima_profesionalizacion),
                    $fmt($f->prima_antiguedad),
                    $fmt($f->bono_transporte),
                    (int)$f->n_hijos,
                    $fmt($f->prima_por_hijos),
                    $fmt($f->total_asignaciones),
                    $fmt($f->total_sueldo_normal),
                    $fmt($f->sso_trabajador),
                    $fmt($f->faov_trabajador),
                    $fmt($f->lrppf_trabajador),
                    $fmt($f->total_deducciones),
                    $fmt($f->neto_a_cobrar),
                    $fmt($f->sso_patronal),
                    $fmt($f->faov_patronal),
                    $fmt($f->rpe_patronal),
                    $fmt($f->total_aportes),
                    $fmt($f->sueldo_normal_diario),
                    $fmt($f->alicuota_bono_vac),
                    $fmt($f->alicuota_bono_fin_anio),
                    $fmt($f->sueldo_integral_diario),
                    (int)$f->dias_habiles_bono_vac,
                    $fmt($f->becas),
                    $fmt($f->bono_50),
                    $fmt($f->bono_responsabilidad),
                    $f->banco_nomina ?: '—',
                    $f->cuenta_nomina ?: '—',
                    $f->advertencias ?: '',
                ];
                if ($esComision) {
                    $fila[] = $fmt($f->sueldo_dependencia_origen);
                    $fila[] = $fmt($f->diferencia_comision);
                }
                $xlsx->filaCeldas($fila, ($i % 2 ? XlsxMultiSheet::S_ZEBRA : XlsxMultiSheet::S_DATA));
            }

            $r = $resumen[$tipo];
            $xlsx->filaFusionada(
                'TOTAL ' . mb_strtoupper($tipo, 'UTF-8') . ': ' . $r['cantidad'] . ' trabajador(es)'
                . ' · Sueldo normal ' . $fmt($r['total_sueldo'])
                . ' · Deducciones ' . $fmt($r['total_deducciones'])
                . ' · Neto ' . $fmt($r['total_neto'])
                . ' · Aportes patronales ' . $fmt($r['total_aportes']),
                $ncol, XlsxMultiSheet::S_TOTAL);
        }

        // ── Hoja RESUMEN (consolidado por tipo, igual que el formato) ──────
        $colsResumen = ['Tipo de Personal', 'Trabajadores', 'Total Sueldo Normal',
                        'SSO', 'FAOV', 'LRPPF', 'Total Deducciones', 'Neto a Cobrar', 'Aportes Patronales'];
        $xlsx->nuevaHoja('RESUMEN');
        $xlsx->membrete('RESUMEN CONSOLIDADO DE NÓMINA — ' . $etiqueta, count($colsResumen));
        $xlsx->filaCeldas($colsResumen, XlsxMultiSheet::S_HEADER);

        $tot = array_fill_keys(['cantidad','total_sueldo','sso','faov','lrppf','total_deducciones','total_neto','total_aportes'], 0);
        $i = 0;
        foreach ($resumen as $tipo => $r) {
            $xlsx->filaCeldas([
                $tipo, $r['cantidad'], $fmt($r['total_sueldo']), $fmt($r['sso']), $fmt($r['faov']),
                $fmt($r['lrppf']), $fmt($r['total_deducciones']), $fmt($r['total_neto']), $fmt($r['total_aportes']),
            ], ($i++ % 2 ? XlsxMultiSheet::S_ZEBRA : XlsxMultiSheet::S_DATA));
            foreach ($tot as $k => $v) $tot[$k] = $v + $r[$k];
        }
        $xlsx->filaCeldas([
            'TOTAL GENERAL', $tot['cantidad'], $fmt($tot['total_sueldo']), $fmt($tot['sso']), $fmt($tot['faov']),
            $fmt($tot['lrppf']), $fmt($tot['total_deducciones']), $fmt($tot['total_neto']), $fmt($tot['total_aportes']),
        ], XlsxMultiSheet::S_TOTAL);

        $xlsx->descargar('nomina_quincenal_' . $periodo->periodo . '_q' . $periodo->quincena);
    }

    /** Genera un período nuevo (snapshot de empleados activos). */
    public function nuevoPeriodo() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/nomina/index'); return; }
        $this->requireRoles([1, 2]);
        $_POST = $this->sanitizePost();
        $periodo     = trim($_POST['periodo'] ?? '');
        $fechaCorte  = trim($_POST['fecha_corte'] ?? '') ?: date('Y-m-d');
        try {
            $id = BonoVacacional::generarPeriodo($periodo, $fechaCorte, $this->getUserId());
            flash('global_msg', 'Período ' . $periodo . ' generado.');
            header('Location: ' . URL_ROOT . '/nomina/verPeriodo/' . $id);
            return;
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/nomina/index');
    }

    /** Vista del período: las 5 secciones por tipo de personal + resumen. */
    public function verPeriodo($id) {
        $periodo = BonoVacacional::find((int)$id);
        if (!$periodo) {
            flash('global_msg', 'Período no encontrado.', 'danger');
            header('Location: ' . URL_ROOT . '/nomina/index');
            return;
        }
        $this->view('nomina/periodo', [
            'titulo'       => 'Bono Vacacional — ' . $periodo->periodo,
            'periodo'      => $periodo,
            'grupos'       => BonoVacacional::detallePorPeriodo((int)$id),
            'resumen'      => BonoVacacional::resumen((int)$id),
            'advertencias' => BonoVacacional::advertencias((int)$id),
        ]);
    }

    /**
     * Recalcula el período en Borrador con los datos actuales de las fichas,
     * **preservando los totales ya confirmados** por Talento Humano.
     */
    public function recalcularPeriodo($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/nomina/index'); return; }
        $this->requireRoles([1, 2]);
        try {
            $n = BonoVacacional::recalcular((int)$id, $this->getUserId());
            flash('global_msg', 'Período recalculado: ' . $n . ' empleado(s). Los totales ya confirmados se conservaron.');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/nomina/verPeriodo/' . $id);
    }

    /**
     * Da por confirmados los totales calculados que aún estaban vacíos. Atajo
     * para validar la estimación en bloque, auditado igual que la captura.
     */
    public function aceptarCalculados($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/nomina/index'); return; }
        $this->requireRoles([1, 2]);
        try {
            $n = BonoVacacional::aceptarCalculados((int)$id, $this->getUserId());
            flash('global_msg', $n > 0
                ? 'Se tomaron como confirmados ' . $n . ' total(es) calculado(s).'
                : 'No había totales pendientes de confirmar.');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/nomina/verPeriodo/' . $id);
    }

    /** Guarda/corrige celdas de captura manual de una fila de detalle. */
    public function guardarDetalle() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/nomina/index'); return; }
        $_POST = $this->sanitizePost();
        $idDetalle = (int)($_POST['id_detalle'] ?? 0);
        $detalle = BonoVacacional::findDetalle($idDetalle);
        $idPeriodo = (int)($detalle->id_periodo ?? 0);
        try {
            BonoVacacional::actualizarDetalle($idDetalle, $_POST, $this->getUserId());
            flash('global_msg', 'Fila actualizada.');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/nomina/verPeriodo/' . $idPeriodo);
    }

    /** Cierra el período (bloquea edición futura del detalle). */
    public function cerrarPeriodo($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . URL_ROOT . '/nomina/index'); return; }
        $this->requireRoles([1, 2]);
        try {
            BonoVacacional::cerrar((int)$id, $this->getUserId());
            flash('global_msg', 'Período cerrado.');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/nomina/verPeriodo/' . $id);
    }

    /** Exporta el período completo en .xlsx (4 hojas por tipo + resumen). */
    public function exportarPeriodo($id) {
        $periodo = BonoVacacional::find((int)$id);
        if (!$periodo) {
            flash('global_msg', 'Período no encontrado.', 'danger');
            header('Location: ' . URL_ROOT . '/nomina/index');
            return;
        }
        $grupos  = BonoVacacional::detallePorPeriodo((int)$id);
        $resumen = BonoVacacional::resumen((int)$id);

        $encabezados = [
            'N°', 'Cédula', 'Apellidos y Nombres', 'Género', 'Cargo', 'Fecha Ingreso Admón. Pública',
            'Años Admón.', 'Días Vacaciones', 'Grado/Escala', 'Grado Instrucción', '% Prof.', '% Antig.',
            'Sueldo Básico Mensual', 'Sueldo Base Quincenal', 'Prima Profesionalización', 'Prima Antigüedad',
            'N° Hijos', 'Monto Hijo', 'Prima por Hijos', 'Bono Transporte', 'Prima Discapacidad',
            'Caja de Ahorro', 'Sueldo Integral Diario', 'Sueldo Normal Diario', 'Cuenta Bancaria',
            'Monto Cesta Ticket', 'Alícuota', 'Total Calculado', 'Total Confirmado', 'Diferencia',
            'Observaciones',
        ];
        $ncol = count($encabezados);

        $fmt = fn($v) => number_format((float)$v, 2, ',', '.');

        $xlsx = new XlsxMultiSheet();
        foreach (BonoVacacional::TIPOS as $tipo) {
            $filas = $grupos[$tipo] ?? [];
            $xlsx->nuevaHoja($tipo);
            $xlsx->membrete('NÓMINA DE CÁLCULO BONO VACACIONAL — ' . mb_strtoupper($tipo, 'UTF-8') . ' — ' . $periodo->periodo, $ncol,
                'Corte: ' . date('d/m/Y', strtotime($periodo->fecha_corte))
                . ' · Cesta ticket: ' . $fmt($periodo->monto_cesta_ticket ?? 0)
                . ' · "Total calculado" es la estimación del sistema; "Total confirmado" es la cifra oficial de Talento Humano');
            $xlsx->filaCeldas($encabezados, XlsxMultiSheet::S_HEADER);
            foreach ($filas as $i => $f) {
                $tConf = $f->total_bono_vacacional;
                $tCalc = $f->total_calculado;
                $dif   = ($tConf !== null && $tCalc !== null) ? ((float)$tConf - (float)$tCalc) : null;
                $xlsx->filaCeldas([
                    $i + 1,
                    $f->cedula,
                    trim($f->apellido . ' ' . $f->nombre),
                    $f->genero,
                    $f->cargo,
                    $f->fecha_ingreso_administracion ?: $f->fecha_ingreso,
                    (int)$f->anios_administracion,
                    (int)$f->dias_vacaciones,
                    $f->grado_escala ?: '—',
                    $f->codigo_grado ?: '—',
                    number_format((float)$f->pct_profesionalizacion, 2, ',', '.'),
                    number_format((float)$f->pct_antiguedad, 2, ',', '.'),
                    $fmt($f->sueldo_basico),
                    $fmt($f->sueldo_base_quincenal),
                    $fmt($f->prima_profesional),
                    $fmt($f->prima_antiguedad),
                    (int)$f->n_hijos,
                    $fmt($f->monto_hijo),
                    $fmt($f->prima_por_hijo),
                    $fmt($f->bono_transporte),
                    $fmt($f->prima_discapacidad),
                    $fmt($f->caja_ahorro),
                    $fmt($f->sueldo_integral),
                    $fmt($f->sueldo_normal_diario),
                    $f->cuenta_bancaria ?: '—',
                    $fmt($f->monto_cesta_ticket),
                    $fmt($f->alicuotas),
                    $tCalc !== null ? $fmt($tCalc) : '',
                    $tConf !== null ? $fmt($tConf) : '',
                    $dif !== null ? $fmt($dif) : '',
                    $f->advertencias ?: '',
                ], ($i % 2 ? XlsxMultiSheet::S_ZEBRA : XlsxMultiSheet::S_DATA));
            }
            $r = $resumen[$tipo];
            $xlsx->filaFusionada(
                'TOTAL ' . mb_strtoupper($tipo, 'UTF-8') . ': ' . $r['cantidad'] . ' empleado(s)'
                . ' · Calculado ' . $fmt($r['total_calculado'])
                . ' · Confirmado ' . $fmt($r['total'])
                . ($r['sin_confirmar'] > 0 ? ' · ' . $r['sin_confirmar'] . ' sin confirmar' : ''),
                $ncol, XlsxMultiSheet::S_TOTAL);
        }

        $colsResumen = ['Tipo de Personal', 'Cantidad de Trabajadores', 'Total Calculado', 'Total Confirmado', 'Diferencia', 'Sin Confirmar'];
        $xlsx->nuevaHoja('Cuadro Resumen');
        $xlsx->membrete('CUADRO RESUMEN — IMPACTO BONO VACACIONAL — ' . $periodo->periodo, count($colsResumen));
        $xlsx->filaCeldas($colsResumen, XlsxMultiSheet::S_HEADER);
        $tc = 0.0; $tf = 0.0; $cant = 0; $sc = 0;
        foreach ($resumen as $tipo => $r) {
            $xlsx->filaCeldas([
                $tipo, $r['cantidad'], $fmt($r['total_calculado']), $fmt($r['total']),
                $fmt($r['total'] - $r['total_calculado']), $r['sin_confirmar'],
            ], XlsxMultiSheet::S_DATA);
            $tc += $r['total_calculado']; $tf += $r['total'];
            $cant += $r['cantidad'];      $sc += $r['sin_confirmar'];
        }
        $xlsx->filaCeldas(['TOTAL', $cant, $fmt($tc), $fmt($tf), $fmt($tf - $tc), $sc], XlsxMultiSheet::S_TOTAL);

        $xlsx->descargar('bono_vacacional_' . $periodo->periodo);
    }
}
