<?php
/**
 * Ficha Institucional de una salida de ruta — fase T-G (mig. 080).
 *
 * Es el documento de cierre que IMATUR entrega a la Directora de Promoción
 * Turística. El formato está en `docs/formatos/rutas_ficha_institucional_IMATUR.jpeg`
 * y lo que lleva es un **conteo demográfico**, no una lista de personas: del
 * grupo visitante IMATUR no registra nombre por nombre (R-23/R-24/R-29).
 *
 * Vive sobre `ruta_informes` porque la ficha **es** el informe: R-43 («planilla
 * del día»), R-47 («informe de cierre») y R-48 resultaron ser la misma hoja.
 * Extender esa tabla, en vez de crear otra, deja intactos los reportes que ya
 * suman `total_atendidos`.
 *
 * Las cuatro columnas demográficas viejas (mujeres/hombres/niñas/niños) pasan a
 * ser **derivadas**: se recalculan desde los renglones en cada guardado, igual
 * que `taller_informes.total_atendidas`. Nunca se capturan a mano.
 */
class RutaFicha extends Model {

    const TIPO_INSTITUCION = 'Institucion';
    const TIPO_APOYO       = 'Apoyo';

    const EST_BORRADOR = 'Borrador';
    const EST_CERRADA  = 'Cerrada';

    const ESTADOS = [self::EST_BORRADOR, self::EST_CERRADA];

    const ESTADO_BADGES = [
        self::EST_BORRADOR => 'sig-badge--warning',
        self::EST_CERRADA  => 'sig-badge--success',
    ];

    // ── Lectura ──────────────────────────────────────────────────────────────

    /** La ficha de una salida, o null si todavía no se ha generado. */
    public static function porEjecucion(int $idEjecucion): ?object {
        $db = new Database();
        $db->query("SELECT * FROM ruta_informes WHERE id_ejecucion = :id");
        $db->bind(':id', $idEjecucion);
        return $db->single() ?: null;
    }

    public static function find(int $id): ?object {
        $db = new Database();
        $db->query("SELECT * FROM ruta_informes WHERE id = :id");
        $db->bind(':id', $id);
        return $db->single() ?: null;
    }

    /** Renglones de la ficha, en el orden en que se dibujan en el papel. */
    public static function grupos(int $idInforme, ?string $tipo = null): array {
        $db = new Database();
        $sql = "SELECT * FROM ruta_ficha_grupos
                 WHERE id_informe = :id AND is_active = TRUE";
        if ($tipo !== null) $sql .= " AND tipo = :t";
        $sql .= " ORDER BY tipo DESC, orden ASC, id ASC";
        $db->query($sql);
        $db->bind(':id', $idInforme);
        if ($tipo !== null) $db->bind(':t', $tipo);
        return $db->resultSet();
    }

    /**
     * El ENCARGADO de la cabecera. No se guarda en la ficha: es el trabajador de
     * IMATUR que encabeza la salida (R-31), y esa relación ya existe desde T-A.
     */
    public static function encargado(int $idEjecucion): ?string {
        $db = new Database();
        $db->query("SELECT TRIM(p.nombre || ' ' || p.apellido) AS nombre
                      FROM ruta_ejecucion_empleados em
                      INNER JOIN empleados e ON em.id_empleado = e.id
                      INNER JOIN personas  p ON e.id_persona   = p.id
                     WHERE em.id_ejecucion = :id AND em.is_active = TRUE
                     ORDER BY em.es_encargado DESC, p.nombre ASC
                     LIMIT 1");
        $db->bind(':id', $idEjecucion);
        $row = $db->single();
        return $row->nombre ?? null;
    }

    // ── Totales (puros: la vista y el guardado usan el MISMO cálculo) ────────

    /**
     * Cuadra la hoja igual que el papel: el TOTAL del pie es la suma de los tres
     * bloques. Verificado contra el ejemplo real del cliente (Cumaná Histórica,
     * 28-08-2026): 22 niños + 9 docentes + 2 de Protección Civil = 33.
     */
    public static function totales(object $cab, array $grupos): array {
        $ninas = 0; $ninos = 0; $apoyoF = 0; $apoyoM = 0;
        foreach ($grupos as $g) {
            if ($g->tipo === self::TIPO_INSTITUCION) {
                $ninas += (int)$g->femenino;
                $ninos += (int)$g->masculino;
            } else {
                $apoyoF += (int)$g->femenino;
                $apoyoM += (int)$g->masculino;
            }
        }
        $docF = (int)($cab->docentes_f ?? 0);       $docM = (int)($cab->docentes_m ?? 0);
        $repF = (int)($cab->representantes_f ?? 0); $repM = (int)($cab->representantes_m ?? 0);

        // «mujeres»/«hombres» = todos los ADULTOS de la hoja. Es lo que esperan
        // los reportes que ya existen, y coincide con el formato: los menores
        // van en su propio bloque.
        $mujeres = $docF + $repF + $apoyoF;
        $hombres = $docM + $repM + $apoyoM;

        return [
            'ninas'           => $ninas,
            'ninos'           => $ninos,
            'ninos_total'     => $ninas + $ninos,
            'docentes'        => $docF + $docM,
            'representantes'  => $repF + $repM,
            'apoyo_f'         => $apoyoF,
            'apoyo_m'         => $apoyoM,
            'apoyo_total'     => $apoyoF + $apoyoM,
            'mujeres'         => $mujeres,
            'hombres'         => $hombres,
            'acompanantes'    => $docF + $docM + $repF + $repM,
            'total'           => $ninas + $ninos + $mujeres + $hombres,
        ];
    }

    // ── Generación automática (R-50) ─────────────────────────────────────────

    /**
     * Crea la ficha de una salida si todavía no existe. La llama el paso a
     * «Ejecutado», que es cuando el cliente dice que se genera (R-50): al volver
     * a la oficina la hoja ya está esperando, con lo que el sistema sabe.
     *
     * Devuelve el id de la ficha (nueva o la que ya había).
     */
    public static function generarDesdeEjecucion(int $idEjecucion, $userId = null): int {
        $ya = self::porEjecucion($idEjecucion);
        if ($ya) return (int)$ya->id;

        $ejec = RutaEjecucion::find($idEjecucion);
        if (!$ejec) throw new Exception('La salida no existe.');

        $db = new Database();
        $db->beginTransaction();
        try {
            $db->query("INSERT INTO ruta_informes
                            (id_ejecucion, lugar_exacto, mujeres, hombres, ninas, ninos,
                             total_atendidos, estado, created_by)
                        VALUES (:e, :lugar, 0, 0, 0, 0, 0, :est, :u)
                        RETURNING id");
            $db->bind(':e',     $idEjecucion);
            $db->bind(':lugar', $ejec->ruta_nombre ?? '');
            $db->bind(':est',   self::EST_BORRADOR);
            $db->bind(':u',     $userId);
            $idFicha = (int)$db->single()->id;

            // Un renglón de arranque con la institución solicitante: en el papel
            // ese primer renglón nunca va vacío, y su nombre ya está registrado.
            $inst = trim((string)($ejec->institucion_nombre ?? ''));
            if ($inst !== '') {
                $sug = self::sugerenciaDesdeParticipantes($idEjecucion);
                $db->query("INSERT INTO ruta_ficha_grupos
                                (id_informe, tipo, nombre, femenino, masculino, edad_min, edad_max, orden, created_by)
                            VALUES (:f, :t, :n, :fem, :mas, :emin, :emax, 0, :u)");
                $db->bind(':f',    $idFicha);
                $db->bind(':t',    self::TIPO_INSTITUCION);
                $db->bind(':n',    $inst);
                $db->bind(':fem',  $sug['femenino']);
                $db->bind(':mas',  $sug['masculino']);
                $db->bind(':emin', $sug['edad_min']);
                $db->bind(':emax', $sug['edad_max']);
                $db->bind(':u',    $userId);
                $db->execute();
            }

            $db->endTransaction();
            self::auditStatic('ruta_informes', 'INSERT', $idFicha, null,
                ['id_ejecucion' => $idEjecucion, 'origen' => 'automatica'], $userId);
            // Los derivados se ponen al día con lo que acaba de sembrarse.
            self::recalcular($idFicha);
            return $idFicha;
        } catch (Throwable $e) {
            $db->cancelTransaction();
            throw $e;
        }
    }

    /**
     * Lo poco que el sistema puede adivinar del grupo: si hubo participantes
     * registrados (el particular de pago, R-22/R-62), su sexo y su rango de edad.
     * Con un grupo escolar esto da 0 y lo captura Turismo — que es justo lo que
     * pasa hoy en papel.
     */
    public static function sugerenciaDesdeParticipantes(int $idEjecucion): array {
        $db = new Database();
        $db->query("SELECT
                        COUNT(CASE WHEN COALESCE(p.genero, pr.genero_libre) = 'F' THEN 1 END) AS femenino,
                        COUNT(CASE WHEN COALESCE(p.genero, pr.genero_libre) = 'M' THEN 1 END) AS masculino,
                        MIN(EXTRACT(YEAR FROM age(COALESCE(p.fecha_nacimiento, pr.fecha_nac_libre)))) AS emin,
                        MAX(EXTRACT(YEAR FROM age(COALESCE(p.fecha_nacimiento, pr.fecha_nac_libre)))) AS emax
                      FROM participantes_ruta pr
                      LEFT JOIN personas p ON pr.id_persona = p.id
                     WHERE pr.id_ejecucion = :id AND pr.is_active = TRUE");
        $db->bind(':id', $idEjecucion);
        $r = $db->single();
        return [
            'femenino'  => (int)($r->femenino  ?? 0),
            'masculino' => (int)($r->masculino ?? 0),
            'edad_min'  => $r && $r->emin !== null ? (int)$r->emin : null,
            'edad_max'  => $r && $r->emax !== null ? (int)$r->emax : null,
        ];
    }

    // ── Escritura ────────────────────────────────────────────────────────────

    /**
     * Guarda la ficha completa: cabecera + renglones, en una transacción.
     *
     * Los renglones se reemplazan en bloque (borrar e insertar) en vez de
     * conciliarlos uno a uno: son pocos, no los referencia nadie y el formulario
     * es una tabla que el usuario edita entera. Conciliar por id habría añadido
     * un caso de borde —renglón que desaparece del POST— sin ganar nada.
     */
    public static function guardar(int $idFicha, array $cab, array $grupos, $userId = null): bool {
        $ficha = self::find($idFicha);
        if (!$ficha) throw new Exception('La ficha no existe.');
        if ($ficha->estado === self::EST_CERRADA) {
            throw new Exception('La ficha está cerrada. Reábrala para poder modificarla.');
        }

        $limpios = self::normalizarGrupos($grupos);
        if (empty($limpios)) {
            throw new Exception('La ficha debe tener al menos un renglón de institución con personas.');
        }

        $db = new Database();
        $db->beginTransaction();
        try {
            $db->query("DELETE FROM ruta_ficha_grupos WHERE id_informe = :f");
            $db->bind(':f', $idFicha);
            $db->execute();

            foreach ($limpios as $i => $g) {
                $db->query("INSERT INTO ruta_ficha_grupos
                                (id_informe, tipo, nombre, femenino, masculino, edad_min, edad_max, orden, created_by)
                            VALUES (:f, :t, :n, :fem, :mas, :emin, :emax, :o, :u)");
                $db->bind(':f',    $idFicha);
                $db->bind(':t',    $g['tipo']);
                $db->bind(':n',    $g['nombre']);
                $db->bind(':fem',  $g['femenino']);
                $db->bind(':mas',  $g['masculino']);
                $db->bind(':emin', $g['edad_min']);
                $db->bind(':emax', $g['edad_max']);
                $db->bind(':o',    $i);
                $db->bind(':u',    $userId);
                $db->execute();
            }

            $db->query("UPDATE ruta_informes
                           SET responsable_nombre = :resp,
                               lugar_exacto       = :lugar,
                               docentes_f         = :df,  docentes_m       = :dm,
                               representantes_f   = :rf,  representantes_m = :rm,
                               observaciones      = :obs, resumen_visita   = :res,
                               updated_at = CURRENT_TIMESTAMP, updated_by = :u
                         WHERE id = :id");
            $db->bind(':resp',  $cab['responsable_nombre'] ?: null);
            $db->bind(':lugar', $cab['lugar_exacto'] ?? '');
            $db->bind(':df',    max(0, (int)($cab['docentes_f'] ?? 0)));
            $db->bind(':dm',    max(0, (int)($cab['docentes_m'] ?? 0)));
            $db->bind(':rf',    max(0, (int)($cab['representantes_f'] ?? 0)));
            $db->bind(':rm',    max(0, (int)($cab['representantes_m'] ?? 0)));
            $db->bind(':obs',   $cab['observaciones'] ?: null);
            $db->bind(':res',   $cab['resumen_visita'] ?? '');
            $db->bind(':u',     $userId);
            $db->bind(':id',    $idFicha);
            $db->execute();

            $db->endTransaction();
        } catch (Throwable $e) {
            $db->cancelTransaction();
            throw $e;
        }

        self::recalcular($idFicha);
        self::auditStatic('ruta_informes', 'UPDATE', $idFicha, null, $cab, $userId);
        return true;
    }

    /**
     * Valida y limpia los renglones que vienen del formulario. Descarta los que
     * están del todo vacíos (la tabla del papel tiene 8 renglones y casi nunca
     * se llenan todos) y exige nombre cuando hay gente contada.
     */
    private static function normalizarGrupos(array $grupos): array {
        $out = [];
        foreach ($grupos as $g) {
            $nombre = trim((string)($g['nombre'] ?? ''));
            $fem    = max(0, (int)($g['femenino']  ?? 0));
            $mas    = max(0, (int)($g['masculino'] ?? 0));
            if ($nombre === '' && $fem === 0 && $mas === 0) continue;   // renglón en blanco
            if ($nombre === '') {
                throw new Exception('Hay un renglón con personas pero sin nombre de institución.');
            }
            $tipo = ($g['tipo'] ?? '') === self::TIPO_APOYO ? self::TIPO_APOYO : self::TIPO_INSTITUCION;

            $emin = ($g['edad_min'] ?? '') === '' ? null : (int)$g['edad_min'];
            $emax = ($g['edad_max'] ?? '') === '' ? null : (int)$g['edad_max'];
            if ($tipo === self::TIPO_APOYO) { $emin = null; $emax = null; }  // son adultos
            foreach ([$emin, $emax] as $e) {
                if ($e !== null && ($e < 0 || $e > 120)) {
                    throw new Exception("Las edades de «{$nombre}» deben estar entre 0 y 120 años.");
                }
            }
            if ($emin !== null && $emax !== null && $emin > $emax) {
                throw new Exception("En «{$nombre}» la edad mínima no puede ser mayor que la máxima.");
            }

            $out[] = ['tipo' => $tipo, 'nombre' => $nombre, 'femenino' => $fem,
                      'masculino' => $mas, 'edad_min' => $emin, 'edad_max' => $emax];
        }
        return $out;
    }

    /** Pone al día las columnas derivadas desde los renglones guardados. */
    public static function recalcular(int $idFicha): void {
        $cab = self::find($idFicha);
        if (!$cab) return;
        $t = self::totales($cab, self::grupos($idFicha));

        $db = new Database();
        $db->query("UPDATE ruta_informes
                       SET mujeres = :m, hombres = :h, ninas = :ni, ninos = :no,
                           total_atendidos = :tot
                     WHERE id = :id");
        $db->bind(':m',   $t['mujeres']);
        $db->bind(':h',   $t['hombres']);
        $db->bind(':ni',  $t['ninas']);
        $db->bind(':no',  $t['ninos']);
        $db->bind(':tot', $t['total']);
        $db->bind(':id',  $idFicha);
        $db->execute();
    }

    // ── Cierre ───────────────────────────────────────────────────────────────

    public static function cerrar(int $idFicha, $userId = null): bool {
        $ficha = self::find($idFicha);
        if (!$ficha) throw new Exception('La ficha no existe.');
        if ($ficha->estado === self::EST_CERRADA) throw new Exception('La ficha ya está cerrada.');
        if ((int)$ficha->total_atendidos === 0) {
            throw new Exception('No se puede cerrar una ficha sin personas atendidas.');
        }

        $db = new Database();
        $db->query("UPDATE ruta_informes
                       SET estado = :est, fecha_cierre = CURRENT_TIMESTAMP,
                           updated_at = CURRENT_TIMESTAMP, updated_by = :u
                     WHERE id = :id");
        $db->bind(':est', self::EST_CERRADA);
        $db->bind(':u',   $userId);
        $db->bind(':id',  $idFicha);
        $ok = $db->execute();
        self::auditStatic('ruta_informes', 'UPDATE', $idFicha, null, ['estado' => self::EST_CERRADA], $userId);
        return $ok;
    }

    /**
     * Reabrir NO borra nada ni deshace el documento ya entregado: solo devuelve
     * la ficha a edición para corregir un conteo. Queda en la bitácora.
     */
    public static function reabrir(int $idFicha, $userId = null): bool {
        $ficha = self::find($idFicha);
        if (!$ficha) throw new Exception('La ficha no existe.');
        if ($ficha->estado !== self::EST_CERRADA) throw new Exception('La ficha no está cerrada.');

        $db = new Database();
        $db->query("UPDATE ruta_informes
                       SET estado = :est, fecha_cierre = NULL,
                           updated_at = CURRENT_TIMESTAMP, updated_by = :u
                     WHERE id = :id");
        $db->bind(':est', self::EST_BORRADOR);
        $db->bind(':u',   $userId);
        $db->bind(':id',  $idFicha);
        $ok = $db->execute();
        self::auditStatic('ruta_informes', 'UPDATE', $idFicha, null, ['estado' => self::EST_BORRADOR], $userId);
        return $ok;
    }
}
