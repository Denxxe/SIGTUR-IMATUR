<?php
/**
 * Controlador AsistenciasController
 */
class AsistenciasController extends Controller {

    public function index() {
        $hoy = date('Y-m-d');
        $porPagina = 12;
        $pagina    = max(1, (int)($_GET['p'] ?? 1));
        $filtros   = [
            'buscar'      => trim($_GET['buscar']      ?? ''),
            'fecha_desde' => trim($_GET['fecha_desde'] ?? ''),
            'fecha_hasta' => trim($_GET['fecha_hasta'] ?? ''),
        ];
        try {
            $res          = Asistencia::paginate($pagina, $porPagina, $filtros);
            $asistencias  = $res['items'];
            $totalReg     = $res['total'];
            $totalPaginas = max(1, (int)ceil($totalReg / $porPagina));
            if ($pagina > $totalPaginas) $pagina = $totalPaginas;
            $empleados   = Empleado::all();
            $tolerancia  = Asistencia::toleranciaPuntualidad();

            // Resumen del día
            $presentesHoy = Asistencia::presentesDia($hoy);
            $enActividad  = Asistencia::empleadosEnActividad($hoy);   // [id_empleado]
            $idsPresentes = array_map(fn($a) => (int)$a->id_empleado, $presentesHoy);
            $idsActividad = array_map('intval', $enActividad);

            $impuntuales = 0;
            foreach ($presentesHoy as $a) {
                if ($a->minutos_tarde !== null && (int)$a->minutos_tarde > $tolerancia) $impuntuales++;
            }

            // Ausentes = activos que ni marcaron ni están en actividad
            $ausentes = [];
            $actividadDetalle = [];
            foreach ($empleados as $e) {
                $eid = (int)$e->id;
                if (in_array($eid, $idsActividad, true)) {
                    $actividadDetalle[] = $e;
                } elseif (!in_array($eid, $idsPresentes, true)) {
                    $ausentes[] = $e;
                }
            }

            $resumen = [
                'activos'      => count($empleados),
                'presentes'    => count($idsPresentes),
                'impuntuales'  => $impuntuales,
                'en_actividad' => count($idsActividad),
                'ausentes'     => count($ausentes),
            ];
        } catch (Exception $e) {
            $asistencias = []; $empleados = []; $tolerancia = 15;
            $totalReg = 0; $totalPaginas = 1;
            $resumen = ['activos'=>0,'presentes'=>0,'impuntuales'=>0,'en_actividad'=>0,'ausentes'=>0];
            $ausentes = []; $actividadDetalle = [];
            flash('global_msg', 'Error al cargar datos: ' . $e->getMessage(), 'danger');
        }

        $data = [
            'titulo'           => 'Control de Asistencia',
            'asistencias'      => $asistencias,
            'empleados'        => $empleados,
            'tolerancia'       => $tolerancia,
            'resumen'          => $resumen,
            'ausentes'         => $ausentes,
            'actividadDetalle' => $actividadDetalle,
            'pagina'           => $pagina,
            'total_paginas'    => $totalPaginas,
            'total'            => $totalReg,
            'por_pagina'       => $porPagina,
            'filtros'          => $filtros,
        ];

        $this->view('asistencias/index', $data);
    }

    /**
     * Procesar Marcaje (Entrada o Salida automática)
     */
    public function marcar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_empleado = (int)$_POST['id_empleado'];
            $user_id     = $this->getUserId();

            try {
                $asistenciaAbierta = Asistencia::findOpen($id_empleado);

                if ($asistenciaAbierta) {
                    $horaSalida  = date('H:i:s');
                    $observacion = 'Marcaje de salida automático';

                    $empleado = Empleado::find($id_empleado);
                    if (!empty($empleado->hora_salida)) {
                        $programada  = strtotime(date('Y-m-d') . ' ' . $empleado->hora_salida);
                        $ahora       = strtotime(date('Y-m-d') . ' ' . $horaSalida);
                        $tolSalida   = Asistencia::toleranciaSalidaTemprana();
                        $minAnticipo = $programada !== false ? (int) floor(($programada - $ahora) / 60) : 0;
                        if ($minAnticipo > $tolSalida) {
                            $motivo = trim($_POST['motivo_temprano'] ?? '');
                            if ($motivo === '') {
                                flash('global_msg', 'Debe indicar el motivo de la salida antes de su horario (' . substr($empleado->hora_salida, 0, 5) . ').', 'danger');
                                header('Location: ' . URL_ROOT . '/asistencias/index');
                                exit;
                            }
                            $observacion .= ' — Salida anticipada: ' . $motivo;
                        }
                    }

                    $data = [
                        'id'          => $asistenciaAbierta->id,
                        'hora_salida' => $horaSalida,
                        'observacion' => $observacion,
                    ];
                    $asistencia = new Asistencia($data);
                    $asistencia->save($user_id);
                    flash('global_msg', 'Salida registrada correctamente.');
                } else {
                    $horaEntrada = date('H:i:s');
                    $minutosTarde = Asistencia::calcularMinutosTarde($id_empleado, $horaEntrada);
                    $tol = Asistencia::toleranciaPuntualidad();
                    $obs = 'Marcaje de entrada automático';
                    if ($minutosTarde !== null && $minutosTarde > $tol) {
                        $obs .= ' — Impuntual (' . $minutosTarde . ' min)';
                    }
                    $data = [
                        'id_empleado'   => $id_empleado,
                        'fecha'         => date('Y-m-d'),
                        'hora_entrada'  => $horaEntrada,
                        'observacion'   => $obs,
                        'minutos_tarde' => $minutosTarde,
                    ];
                    $asistencia = new Asistencia($data);
                    $asistencia->save($user_id);
                    if ($minutosTarde !== null && $minutosTarde > $tol) {
                        flash('global_msg', 'Entrada registrada — impuntual (' . $minutosTarde . ' min de retraso).', 'warning');
                    } else {
                        flash('global_msg', 'Entrada registrada correctamente.');
                    }
                }
            } catch (Exception $e) {
                flash('global_msg', 'Error al registrar marcaje: ' . $e->getMessage(), 'danger');
            }

            header('Location: ' . URL_ROOT . '/asistencias/index');
        }
    }

    /**
     * Estado del marcaje de un empleado (AJAX): indica si el próximo marcaje
     * sería una salida antes de su hora de horario, para pedir el motivo
     * en el formulario antes de enviar.
     */
    public function estadoMarcaje() {
        header('Content-Type: application/json');
        $id = (int)($_GET['id'] ?? 0);
        $requiereMotivo = false;
        $horaProgramada = null;

        if ($id) {
            $abierta = Asistencia::findOpen($id);
            if ($abierta) {
                $empleado = Empleado::find($id);
                if (!empty($empleado->hora_salida)) {
                    $horaProgramada = substr($empleado->hora_salida, 0, 5);
                    $programada  = strtotime(date('Y-m-d') . ' ' . $empleado->hora_salida);
                    $ahora       = strtotime(date('Y-m-d H:i:s'));
                    $tolSalida   = Asistencia::toleranciaSalidaTemprana();
                    $minAnticipo = $programada !== false ? (int) floor(($programada - $ahora) / 60) : 0;
                    if ($minAnticipo > $tolSalida) $requiereMotivo = true;
                }
            }
        }

        echo json_encode([
            'requiere_motivo' => $requiereMotivo,
            'hora_programada' => $horaProgramada,
        ]);
        exit;
    }
}
