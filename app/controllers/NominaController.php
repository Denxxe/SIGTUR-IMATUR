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
        ]);
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

    /** Vista del período: las 4 secciones por tipo de personal + resumen. */
    public function verPeriodo($id) {
        $periodo = BonoVacacional::find((int)$id);
        if (!$periodo) {
            flash('global_msg', 'Período no encontrado.', 'danger');
            header('Location: ' . URL_ROOT . '/nomina/index');
            return;
        }
        $this->view('nomina/periodo', [
            'titulo'  => 'Bono Vacacional — ' . $periodo->periodo,
            'periodo' => $periodo,
            'grupos'  => BonoVacacional::detallePorPeriodo((int)$id),
            'resumen' => BonoVacacional::resumen((int)$id),
        ]);
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
            'Días Vacaciones', 'Grado/Escala', 'Sueldo Básico', 'Prima Profesional', 'Prima Antigüedad',
            'N° Hijos', 'Monto Hijo', 'Prima por Hijo', 'Bono Transporte', 'Prima Discapacidad',
            'Caja de Ahorro', 'Sueldo Integral', 'Cuenta Bancaria', 'Monto Cesta Ticket', 'Alícuotas',
            'Total Bono Vacacional',
        ];
        $ncol = count($encabezados);

        $fmt = fn($v) => number_format((float)$v, 2, ',', '.');

        $xlsx = new XlsxMultiSheet();
        foreach (BonoVacacional::TIPOS as $tipo) {
            $filas = $grupos[$tipo] ?? [];
            $xlsx->nuevaHoja($tipo);
            $xlsx->membrete('NÓMINA DE CÁLCULO BONO VACACIONAL — ' . strtoupper($tipo) . ' — ' . $periodo->periodo, $ncol);
            $xlsx->filaCeldas($encabezados, XlsxMultiSheet::S_HEADER);
            foreach ($filas as $i => $f) {
                $xlsx->filaCeldas([
                    $i + 1,
                    $f->cedula,
                    trim($f->apellido . ' ' . $f->nombre),
                    $f->genero,
                    $f->cargo,
                    $f->fecha_ingreso_administracion ?: $f->fecha_ingreso,
                    $f->dias_vacaciones,
                    $f->grado_escala,
                    $fmt($f->sueldo_basico),
                    $fmt($f->prima_profesional),
                    $fmt($f->prima_antiguedad),
                    $f->n_hijos,
                    $fmt($f->monto_hijo),
                    $fmt($f->prima_por_hijo),
                    $fmt($f->bono_transporte),
                    $fmt($f->prima_discapacidad),
                    $fmt($f->caja_ahorro),
                    $fmt($f->sueldo_integral),
                    $f->cuenta_bancaria,
                    $fmt($f->monto_cesta_ticket),
                    $fmt($f->alicuotas),
                    $f->total_bono_vacacional !== null ? $fmt($f->total_bono_vacacional) : '',
                ], ($i % 2 ? XlsxMultiSheet::S_ZEBRA : XlsxMultiSheet::S_DATA));
            }
            $totalTipo = array_sum(array_map(fn($f) => (float)($f->total_bono_vacacional ?? 0), $filas));
            $xlsx->filaFusionada('TOTAL ' . strtoupper($tipo) . ': ' . count($filas) . ' empleado(s) · ' . $fmt($totalTipo), $ncol, XlsxMultiSheet::S_TOTAL);
        }

        $xlsx->nuevaHoja('Cuadro Resumen');
        $xlsx->membrete('CUADRO RESUMEN — IMPACTO BONO VACACIONAL — ' . $periodo->periodo, 3);
        $xlsx->filaCeldas(['Tipo de Personal', 'Cantidad de Trabajadores', 'Monto Total Bono Vacacional'], XlsxMultiSheet::S_HEADER);
        $totalGeneral = 0.0; $cantidadGeneral = 0;
        foreach ($resumen as $tipo => $r) {
            $xlsx->filaCeldas([$tipo, $r['cantidad'], $fmt($r['total'])], XlsxMultiSheet::S_DATA);
            $totalGeneral += $r['total'];
            $cantidadGeneral += $r['cantidad'];
        }
        $xlsx->filaCeldas(['TOTAL', $cantidadGeneral, $fmt($totalGeneral)], XlsxMultiSheet::S_TOTAL);

        $xlsx->descargar('bono_vacacional_' . $periodo->periodo);
    }
}
