<?php
/**
 * RutaEjecucion — una SALIDA: cada vez que se ejecuta una ruta del catálogo.
 *
 * Es la mitad que faltaba del módulo (fase T-A, mig. 078). Hasta ahora cada fila
 * de `rutas` era a la vez el recorrido y la salida, y el cliente lo desmintió
 * tres veces:
 *
 *   R-08  "Existe un catálogo… todo lleva un catálogo"
 *   R-09  Dos salidas de la misma ruta en una misma mañana, con guías distintos
 *   R-07  "Así sean la misma ruta, es considerada 2 salidas y en el registro
 *          son 2 rutas aplicadas"
 *
 * ESTADOS (R-14, con las palabras del cliente):
 *
 *     Programado ──► Ejecutado                    (terminal)
 *          │
 *          └──────► No ejecutado (+ motivo)       (terminal)
 *                        │
 *                        └──► reprogramar(): crea una salida NUEVA que apunta
 *                             a la que no se pudo hacer
 *
 * «Cancelada» no es un estado: da igual quién cancele —el solicitante o IMATUR
 * por clima, agua, gasolina— el resultado es *No ejecutado* con su motivo
 * (R-15: "si se cancela, el motivo siempre tiene que saberse").
 *
 * Y la reprogramación es una salida **nueva enlazada**, no un cambio de fecha
 * sobre la misma: si se reescribiera la fecha, la salida fallida desaparecería
 * del histórico y el conteo de ejecutadas quedaría inflado.
 */
class RutaEjecucion extends Model {

    const EST_PROGRAMADO    = 'Programado';
    const EST_EJECUTADO     = 'Ejecutado';
    const EST_NO_EJECUTADO  = 'No ejecutado';

    const ESTADOS = [self::EST_PROGRAMADO, self::EST_EJECUTADO, self::EST_NO_EJECUTADO];

    const ESTADO_BADGES = [
        self::EST_PROGRAMADO   => 'sig-badge--info',
        self::EST_EJECUTADO    => 'sig-badge--success',
        self::EST_NO_EJECUTADO => 'sig-badge--danger',
    ];

    /** Estados desde los que ya no se puede mover la salida. */
    const ESTADOS_TERMINALES = [self::EST_EJECUTADO, self::EST_NO_EJECUTADO];

    // R-11: la solicitud viene de un particular o de una institución pública.
    const ORIGENES = ['Particular', 'Institucional'];

    /**
     * R-33: "7-8 niños por guía; una salida de 35 personas irían 3 guías".
     * Se usa para SUGERIR cuántos guías hacen falta, no para impedir nada:
     * el cliente aclaró que "depende de la cantidad de guías disponibles y se
     * pueden hacer estrategias".
     */
    const PERSONAS_POR_GUIA = 8;

    public static function guiasSugeridos(int $personas): int {
        return $personas > 0 ? (int)ceil($personas / self::PERSONAS_POR_GUIA) : 0;
    }

    // ── Consultas ────────────────────────────────────────────────────────────

    private const SELECT_BASE = "
        SELECT e.*,
               r.nombre      AS ruta_nombre,
               r.tipo_ruta   AS ruta_tipo,
               r.duracion_estimada,
               d.nombre      AS departamento_nombre,
               orig.fecha    AS fecha_original,
               (SELECT COUNT(*) FROM participantes_ruta p
                 WHERE p.id_ejecucion = e.id AND p.is_active = TRUE) AS total_participantes,
               (SELECT COUNT(*) FROM ruta_ejecucion_empleados em
                 WHERE em.id_ejecucion = e.id AND em.is_active = TRUE) AS total_empleados
          FROM ruta_ejecuciones e
          INNER JOIN rutas r ON e.id_ruta = r.id
          LEFT  JOIN departamentos d ON r.id_departamento = d.id
          LEFT  JOIN ruta_ejecuciones orig ON e.id_reprogramada_de = orig.id
    ";

    public static function find(int $id) {
        $db = new Database();
        $db->query(self::SELECT_BASE . " WHERE e.id = :id AND e.is_active = TRUE");
        $db->bind(':id', $id);
        return $db->single() ?: null;
    }

    /** Salidas de una ruta del catálogo, de la más reciente a la más antigua. */
    public static function porRuta(int $idRuta) {
        $db = new Database();
        $db->query(self::SELECT_BASE . "
            WHERE e.id_ruta = :id AND e.is_active = TRUE
            ORDER BY e.fecha DESC, e.hora DESC NULLS LAST");
        $db->bind(':id', $idRuta);
        return $db->resultSet();
    }

    /**
     * Listado paginado con búsqueda y filtros. Sustituye a `Ruta::paginate()`
     * para todo lo que es "salidas": la fecha, el estado de ejecución y el
     * período ya no viven en el catálogo.
     */
    public static function paginate(int $pagina, int $porPagina, array $f = []): array {
        $db    = new Database();
        $binds = [];
        $where = "e.is_active = TRUE";

        if (!empty($f['buscar'])) {
            $where .= " AND (r.nombre ILIKE :q OR e.institucion_nombre ILIKE :q OR e.observaciones ILIKE :q)";
            $binds[':q'] = '%' . $f['buscar'] . '%';
        }
        if (!empty($f['estado'])) { $where .= " AND e.estado = :estado"; $binds[':estado'] = $f['estado']; }
        if (!empty($f['origen'])) { $where .= " AND e.origen = :origen"; $binds[':origen'] = $f['origen']; }
        if (!empty($f['ruta']))   { $where .= " AND e.id_ruta = :ruta";  $binds[':ruta']   = (int)$f['ruta']; }
        if (!empty($f['fecha_desde'])) { $where .= " AND e.fecha >= :fd"; $binds[':fd'] = $f['fecha_desde']; }
        if (!empty($f['fecha_hasta'])) { $where .= " AND e.fecha <= :fh"; $binds[':fh'] = $f['fecha_hasta']; }

        switch ($f['periodo'] ?? '') {
            case 'proximos': $where .= " AND e.fecha >= CURRENT_DATE"; break;
            case 'hoy':      $where .= " AND e.fecha = CURRENT_DATE"; break;
            case 'semana':   $where .= " AND e.fecha BETWEEN CURRENT_DATE AND (CURRENT_DATE + INTERVAL '7 days')"; break;
            case 'mes':      $where .= " AND date_trunc('month', e.fecha) = date_trunc('month', CURRENT_DATE)"; break;
            case 'pasados':  $where .= " AND e.fecha < CURRENT_DATE"; break;
        }

        $db->query("SELECT COUNT(*) AS total
                      FROM ruta_ejecuciones e
                      INNER JOIN rutas r ON e.id_ruta = r.id
                     WHERE {$where}");
        foreach ($binds as $k => $v) $db->bind($k, $v);
        $total = (int)($db->single()->total ?? 0);

        $db->query(self::SELECT_BASE . "
            WHERE {$where}
            ORDER BY e.fecha DESC, e.hora DESC NULLS LAST, e.id DESC
            LIMIT :lim OFFSET :off");
        foreach ($binds as $k => $v) $db->bind($k, $v);
        $db->bind(':lim', $porPagina);
        $db->bind(':off', ($pagina - 1) * $porPagina);

        return ['items' => $db->resultSet(), 'total' => $total];
    }

    /** Conteo por estado, para los tiles del listado. */
    public static function resumenPorEstado(): array {
        $db = new Database();
        $db->query("SELECT estado, COUNT(*) AS n FROM ruta_ejecuciones
                     WHERE is_active = TRUE GROUP BY estado");
        $out = [];
        foreach ($db->resultSet() as $r) $out[$r->estado] = (int)$r->n;
        return $out;
    }

    // ── Cupo diario (R-28) ───────────────────────────────────────────────────

    /**
     * Personas ya comprometidas para un día, sumando TODAS las salidas de esa
     * fecha. El cupo del cliente es **por día**, no por salida: *"se ha
     * implementado un cupo de 60 personas por día — esto es nuevo"*. Con dos
     * salidas la misma mañana (R-09), el tope se reparte entre ellas.
     */
    /** Tope de personas por día (0 = sin tope). Configurable desde /config. */
    public static function cupoDiario(): int {
        return (int)ConfigSistema::get('rutas_cupo_diario');
    }

    /**
     * ¿Cuántas personas caben todavía ese día? NULL si no hay tope.
     * Es una **advertencia**, no un bloqueo: el cliente dijo que el cupo de 60
     * «es nuevo», y en Talleres el cupo ya se trata como estimación de
     * planificación, no como límite rígido. Mismo criterio aquí.
     */
    public static function cupoRestanteDelDia(string $fecha, ?int $excluir = null): ?int {
        $tope = self::cupoDiario();
        if ($tope <= 0) return null;
        return $tope - self::personasEnFecha($fecha, $excluir);
    }

    public static function personasEnFecha(string $fecha, ?int $excluirEjecucion = null): int {
        $db = new Database();
        $sql = "SELECT COUNT(*) AS n
                  FROM participantes_ruta p
                  INNER JOIN ruta_ejecuciones e ON p.id_ejecucion = e.id
                 WHERE e.fecha = :f AND e.is_active = TRUE AND p.is_active = TRUE
                   AND e.estado <> :noejec";
        if ($excluirEjecucion !== null) $sql .= " AND e.id <> :ex";
        $db->query($sql);
        $db->bind(':f', $fecha);
        $db->bind(':noejec', self::EST_NO_EJECUTADO);
        if ($excluirEjecucion !== null) $db->bind(':ex', $excluirEjecucion);
        return (int)($db->single()->n ?? 0);
    }

    // ── Escritura ────────────────────────────────────────────────────────────

    /** Programa una salida nueva. Devuelve su id. */
    public static function crear(array $d, $user_id = null): int {
        $idRuta = (int)($d['id_ruta'] ?? 0);
        if ($idRuta <= 0)                  throw new Exception('Debe indicar a qué ruta pertenece la salida.');
        if (empty($d['fecha']))            throw new Exception('La fecha de la salida es obligatoria.');
        if (!Ruta::find($idRuta))          throw new Exception('La ruta indicada no existe.');

        $origen = in_array($d['origen'] ?? '', self::ORIGENES, true) ? $d['origen'] : 'Particular';
        if ($origen === 'Institucional' && trim($d['institucion_nombre'] ?? '') === '') {
            throw new Exception('Indique la institución que solicita la salida.');
        }

        $db = new Database();
        $db->query("INSERT INTO ruta_ejecuciones
                        (id_ruta, fecha, hora, cupo_maximo, estado, origen, institucion_nombre,
                         oficio_archivo, oficio_original, id_reprogramada_de, observaciones, created_by)
                    VALUES (:r, :f, :h, :cupo, :est, :org, :inst, :arch, :orig, :repro, :obs, :uid)
                    RETURNING id");
        $db->bind(':r',     $idRuta);
        $db->bind(':f',     $d['fecha']);
        $db->bind(':h',     ($d['hora'] ?? '') ?: null);
        $db->bind(':cupo',  ($d['cupo_maximo'] ?? '') !== '' ? (int)$d['cupo_maximo'] : null);
        $db->bind(':est',   self::EST_PROGRAMADO);
        $db->bind(':org',   $origen);
        $db->bind(':inst',  $origen === 'Institucional' ? trim($d['institucion_nombre']) : null);
        $db->bind(':arch',  $d['oficio_archivo']  ?? null);
        $db->bind(':orig',  $d['oficio_original'] ?? null);
        $db->bind(':repro', ($d['id_reprogramada_de'] ?? null) ?: null);
        $db->bind(':obs',   ($d['observaciones'] ?? '') ?: null);
        $db->bind(':uid',   $user_id);
        $id = (int)$db->single()->id;

        self::auditStatic('ruta_ejecuciones', 'INSERT', $id, null,
            ['id_ruta' => $idRuta, 'fecha' => $d['fecha']], $user_id);
        return $id;
    }

    /** Edita una salida que todavía está programada. */
    public static function actualizar(int $id, array $d, $user_id = null): bool {
        $previo = self::find($id);
        if (!$previo) throw new Exception('La salida no existe.');
        if (in_array($previo->estado, self::ESTADOS_TERMINALES, true)) {
            throw new Exception('Esta salida ya está «' . $previo->estado . '»: no se puede modificar. Si hay que rehacerla, repórtala como no ejecutada y reprográmala.');
        }
        if (empty($d['fecha'])) throw new Exception('La fecha de la salida es obligatoria.');

        $origen = in_array($d['origen'] ?? '', self::ORIGENES, true) ? $d['origen'] : $previo->origen;
        if ($origen === 'Institucional' && trim($d['institucion_nombre'] ?? '') === '') {
            throw new Exception('Indique la institución que solicita la salida.');
        }

        $db = new Database();
        $db->query("UPDATE ruta_ejecuciones
                       SET fecha = :f, hora = :h, cupo_maximo = :cupo,
                           origen = :org, institucion_nombre = :inst,
                           observaciones = :obs,
                           oficio_archivo  = COALESCE(:arch, oficio_archivo),
                           oficio_original = COALESCE(:orig, oficio_original),
                           updated_at = CURRENT_TIMESTAMP, updated_by = :uid
                     WHERE id = :id");
        $db->bind(':f',    $d['fecha']);
        $db->bind(':h',    ($d['hora'] ?? '') ?: null);
        $db->bind(':cupo', ($d['cupo_maximo'] ?? '') !== '' ? (int)$d['cupo_maximo'] : null);
        $db->bind(':org',  $origen);
        $db->bind(':inst', $origen === 'Institucional' ? trim($d['institucion_nombre']) : null);
        $db->bind(':obs',  ($d['observaciones'] ?? '') ?: null);
        $db->bind(':arch', $d['oficio_archivo']  ?? null);
        $db->bind(':orig', $d['oficio_original'] ?? null);
        $db->bind(':uid',  $user_id);
        $db->bind(':id',   $id);
        $ok = $db->execute();

        self::auditStatic('ruta_ejecuciones', 'UPDATE', $id, $previo, $d, $user_id);
        return $ok;
    }

    /** R-13: la aprobación la da la Presidencia. */
    public static function aprobar(int $id, $user_id = null): bool {
        $previo = self::find($id);
        if (!$previo)                  throw new Exception('La salida no existe.');
        if ($previo->fecha_aprobacion) throw new Exception('Esta salida ya está aprobada.');

        $db = new Database();
        $db->query("UPDATE ruta_ejecuciones
                       SET aprobada_por = :uid, fecha_aprobacion = CURRENT_DATE,
                           updated_at = CURRENT_TIMESTAMP, updated_by = :uid
                     WHERE id = :id");
        $db->bind(':uid', $user_id);
        $db->bind(':id',  $id);
        $ok = $db->execute();
        self::auditStatic('ruta_ejecuciones', 'UPDATE', $id, $previo, ['accion' => 'APROBAR'], $user_id);
        return $ok;
    }

    /**
     * Cambia el estado. `Ejecutado` cierra la salida; `No ejecutado` EXIGE
     * motivo (R-15) — da igual si canceló el grupo o IMATUR por clima.
     */
    public static function cambiarEstado(int $id, string $estado, ?string $motivo, $user_id = null): bool {
        if (!in_array($estado, self::ESTADOS, true)) throw new Exception('Estado no válido.');

        $previo = self::find($id);
        if (!$previo) throw new Exception('La salida no existe.');
        if ($previo->estado === $estado) throw new Exception('La salida ya está en ese estado.');
        if (in_array($previo->estado, self::ESTADOS_TERMINALES, true)) {
            throw new Exception('Esta salida ya está «' . $previo->estado . '» y no puede cambiar de estado.');
        }
        if ($estado === self::EST_NO_EJECUTADO && trim((string)$motivo) === '') {
            throw new Exception('Indique por qué no se ejecutó: el motivo es obligatorio.');
        }
        if ($estado === self::EST_EJECUTADO && $previo->fecha > date('Y-m-d')) {
            throw new Exception('No se puede marcar como ejecutada una salida con fecha futura.');
        }

        $db = new Database();
        $db->query("UPDATE ruta_ejecuciones
                       SET estado = :est, motivo_no_ejecucion = :mot,
                           updated_at = CURRENT_TIMESTAMP, updated_by = :uid
                     WHERE id = :id");
        $db->bind(':est', $estado);
        $db->bind(':mot', $estado === self::EST_NO_EJECUTADO ? trim((string)$motivo) : null);
        $db->bind(':uid', $user_id);
        $db->bind(':id',  $id);
        $ok = $db->execute();

        self::auditStatic('ruta_ejecuciones', 'UPDATE', $id, $previo,
            ['estado' => $estado, 'motivo' => $motivo], $user_id);

        // R-50: la Ficha Institucional «se genera automáticamente» al cerrar la
        // salida. Aquí es donde se cierra, así que aquí nace la ficha — en
        // Borrador y con lo que el sistema ya sabe (recorrido, fecha, encargado,
        // institución). Turismo solo completa los conteos al volver a la oficina
        // (R-46: el informe se hace en la oficina, no en campo).
        if ($ok && $estado === self::EST_EJECUTADO) {
            try {
                RutaFicha::generarDesdeEjecucion($id, $user_id);
            } catch (Throwable $e) {
                // Que falle la ficha no puede deshacer el cambio de estado: la
                // salida SÍ se ejecutó. Se puede generar luego desde la pantalla.
                error_log('No se pudo generar la Ficha Institucional de la salida ' . $id . ': ' . $e->getMessage());
            }
        }
        return $ok;
    }

    /**
     * La salida que reemplazó a esta, si se reprogramó. El enlace inverso de
     * `id_reprogramada_de`: la original conserva su registro y apunta a la nueva.
     */
    public static function reprogramadaComo(int $id): ?object {
        $db = new Database();
        $db->query(self::SELECT_BASE . " WHERE e.id_reprogramada_de = :id AND e.is_active = TRUE LIMIT 1");
        $db->bind(':id', $id);
        return $db->single() ?: null;
    }

    /**
     * R-16: reprogramar una salida que no se pudo ejecutar. Crea una salida
     * NUEVA enlazada a la original, conservando grupo, origen y cupo.
     * La original **no se toca**: sigue siendo el registro de lo que no ocurrió.
     */
    public static function reprogramar(int $id, string $fecha, ?string $hora, $user_id = null): int {
        $orig = self::find($id);
        if (!$orig) throw new Exception('La salida no existe.');
        if ($orig->estado !== self::EST_NO_EJECUTADO) {
            throw new Exception('Solo se reprograma una salida que quedó como «No ejecutado». Repórtala primero, con su motivo.');
        }
        if ($fecha === '') throw new Exception('Indique la fecha nueva.');
        if ($fecha < date('Y-m-d')) throw new Exception('La fecha nueva no puede estar en el pasado.');

        $db = new Database();
        $db->query("SELECT id FROM ruta_ejecuciones WHERE id_reprogramada_de = :id AND is_active = TRUE");
        $db->bind(':id', $id);
        if ($db->single()) throw new Exception('Esta salida ya fue reprogramada.');

        return self::crear([
            'id_ruta'            => (int)$orig->id_ruta,
            'fecha'              => $fecha,
            'hora'               => $hora ?: $orig->hora,
            'cupo_maximo'        => $orig->cupo_maximo,
            'origen'             => $orig->origen,
            'institucion_nombre' => $orig->institucion_nombre,
            'oficio_archivo'     => $orig->oficio_archivo,
            'oficio_original'    => $orig->oficio_original,
            'id_reprogramada_de' => $id,
            'observaciones'      => $orig->observaciones,
        ], $user_id);
    }

    /** R-45: incidencias del día. */
    public static function guardarIncidencias(int $id, ?string $texto, $user_id = null): bool {
        $previo = self::find($id);
        if (!$previo) throw new Exception('La salida no existe.');
        $db = new Database();
        $db->query("UPDATE ruta_ejecuciones SET incidencias = :t,
                           updated_at = CURRENT_TIMESTAMP, updated_by = :uid WHERE id = :id");
        $db->bind(':t', ($texto ?? '') !== '' ? $texto : null);
        $db->bind(':uid', $user_id);
        $db->bind(':id', $id);
        return $db->execute();
    }

    public static function delete(int $id, $user_id = null): bool {
        $previo = self::find($id);
        if (!$previo) throw new Exception('La salida no existe.');
        if ($previo->estado === self::EST_EJECUTADO) {
            throw new Exception('Una salida ya ejecutada no se elimina: es parte del histórico.');
        }
        $db = new Database();
        $db->query("UPDATE ruta_ejecuciones SET is_active = FALSE,
                           deleted_at = CURRENT_TIMESTAMP, deleted_by = :uid WHERE id = :id");
        $db->bind(':uid', $user_id);
        $db->bind(':id', $id);
        $ok = $db->execute();
        self::auditStatic('ruta_ejecuciones', 'DELETE', $id, $previo, null, $user_id);
        return $ok;
    }

    // ── Empleados que van en la salida (R-31/R-33) ───────────────────────────

    public static function empleados(int $id) {
        $db = new Database();
        $db->query("SELECT em.id, em.id_empleado, em.es_encargado,
                           TRIM(p.nombre || ' ' || p.apellido) AS nombre,
                           c.nombre AS cargo
                      FROM ruta_ejecucion_empleados em
                      INNER JOIN empleados e ON em.id_empleado = e.id
                      INNER JOIN personas  p ON e.id_persona   = p.id
                      LEFT  JOIN cargos    c ON e.id_cargo     = c.id
                     WHERE em.id_ejecucion = :id AND em.is_active = TRUE
                     ORDER BY em.es_encargado DESC, p.nombre ASC");
        $db->bind(':id', $id);
        return $db->resultSet();
    }

    /**
     * Suma un empleado a la salida. El **encargado** es único: R-31 dice que la
     * salida "siempre va encabezada por un trabajador de IMATUR", en singular.
     */
    public static function agregarEmpleado(int $id, int $idEmpleado, bool $esEncargado, $user_id = null): bool {
        if (!self::find($id))        throw new Exception('La salida no existe.');
        if ($idEmpleado <= 0)        throw new Exception('Seleccione un empleado.');

        $db = new Database();
        $db->query("SELECT id FROM ruta_ejecucion_empleados
                     WHERE id_ejecucion = :e AND id_empleado = :emp AND is_active = TRUE");
        $db->bind(':e', $id); $db->bind(':emp', $idEmpleado);
        if ($db->single()) throw new Exception('Ese empleado ya está asignado a esta salida.');

        $db->beginTransaction();
        try {
            if ($esEncargado) {
                $db->query("UPDATE ruta_ejecucion_empleados SET es_encargado = FALSE
                             WHERE id_ejecucion = :e AND is_active = TRUE");
                $db->bind(':e', $id);
                $db->execute();
            }
            $db->query("INSERT INTO ruta_ejecucion_empleados (id_ejecucion, id_empleado, es_encargado, created_by)
                        VALUES (:e, :emp, :enc, :uid)");
            $db->bind(':e', $id); $db->bind(':emp', $idEmpleado);
            $db->bind(':enc', $esEncargado); $db->bind(':uid', $user_id);
            $db->execute();
            $db->endTransaction();
        } catch (Exception $ex) {
            $db->cancelTransaction();
            throw $ex;
        }
        return true;
    }

    public static function quitarEmpleado(int $idFila, $user_id = null): bool {
        $db = new Database();
        $db->query("UPDATE ruta_ejecucion_empleados SET is_active = FALSE WHERE id = :id");
        $db->bind(':id', $idFila);
        return $db->execute();
    }

    // =========================================================================
    //  Participantes — vinieron de `Ruta` en la mig. 078
    //
    //  Conviven DOS formas de registrar gente, y los formatos del cliente lo
    //  confirman: la lista nominal con nombre, cédula y firma es para adultos;
    //  los escolares van por conteo demográfico (R-23/R-24), porque no tienen
    //  cédula. Por eso siguen existiendo los participantes «libres».
    // =========================================================================

    public static function participantes(int $id) {
        $db = new Database();
        $db->query("SELECT prt.*, p.cedula, p.nombre, p.apellido, p.telefono
                      FROM participantes_ruta prt
                      LEFT JOIN personas p ON prt.id_persona = p.id
                     WHERE prt.id_ejecucion = :id AND prt.is_active = TRUE
                     ORDER BY COALESCE(p.apellido, prt.apellido_libre) ASC");
        $db->bind(':id', $id);
        return $db->resultSet();
    }

    public static function countParticipantes(int $id): int {
        $db = new Database();
        $db->query("SELECT COUNT(*) AS total FROM participantes_ruta
                     WHERE id_ejecucion = :id AND is_active = TRUE");
        $db->bind(':id', $id);
        return (int)($db->single()->total ?? 0);
    }

    /** Corta la inscripción si la salida ya está cerrada. */
    private static function exigirProgramada(int $id): object {
        $e = self::find($id);
        if (!$e) throw new Exception('La salida no existe.');
        if (in_array($e->estado, self::ESTADOS_TERMINALES, true)) {
            throw new Exception('Esta salida ya está «' . $e->estado . '»: no admite más inscripciones.');
        }
        return $e;
    }

    public static function inscribir(int $id, int $idPersona, int $userId, ?string $observaciones = null) {
        self::exigirProgramada($id);
        $db = new Database();
        $db->query("SELECT id FROM participantes_ruta
                     WHERE id_ejecucion = :e AND id_persona = :p AND is_active = TRUE LIMIT 1");
        $db->bind(':e', $id);
        $db->bind(':p', $idPersona);
        if ($db->single()) throw new Exception('Esta persona ya está inscrita en esta salida.');

        $db->query("INSERT INTO participantes_ruta (id_ejecucion, id_persona, observaciones, created_by)
                    VALUES (:e, :p, :obs, :u)");
        $db->bind(':e', $id);
        $db->bind(':p', $idPersona);
        $db->bind(':obs', $observaciones);
        $db->bind(':u', $userId);
        $ok = $db->execute();
        self::auditStatic('participantes_ruta', 'INSERT', null, null,
            ['id_ejecucion' => $id, 'id_persona' => $idPersona], $userId);
        return $ok;
    }

    /**
     * ¿Ya hay un participante SIN cédula equivalente en esta salida?
     * Mismo nombre + apellido + fecha de nacimiento, o misma cédula libre.
     */
    public static function estaInscritoLibre(int $id, string $nombre, ?string $apellido,
                                             ?string $fnac, ?string $cedulaLibre = null): bool {
        $db = new Database();
        $tieneCed = $cedulaLibre !== null && trim($cedulaLibre) !== '';
        $sql = "SELECT 1 FROM participantes_ruta
                 WHERE id_ejecucion = :e AND is_active = TRUE AND id_persona IS NULL
                   AND ( ( lower(trim(nombre_libre)) = lower(trim(:nom))
                           AND lower(trim(COALESCE(apellido_libre,''))) = lower(trim(:ape))
                           AND COALESCE(fecha_nac_libre::text,'') = COALESCE(:fnac,'') )";
        if ($tieneCed) $sql .= " OR ( cedula_libre IS NOT NULL AND lower(trim(cedula_libre)) = lower(trim(:ced)) )";
        $sql .= " ) LIMIT 1";
        $db->query($sql);
        $db->bind(':e', $id);
        $db->bind(':nom', $nombre);
        $db->bind(':ape', $apellido ?? '');
        $db->bind(':fnac', $fnac ?: null);
        if ($tieneCed) $db->bind(':ced', $cedulaLibre);
        return (bool)$db->single();
    }

    public static function inscribirLibre(int $id, array $d, int $userId) {
        self::exigirProgramada($id);
        $db = new Database();
        $db->query("INSERT INTO participantes_ruta
                        (id_ejecucion, nombre_libre, apellido_libre, cedula_libre,
                         genero_libre, fecha_nac_libre, observaciones,
                         nombre_representante, cedula_representante, created_by)
                    VALUES (:e, :nom, :ape, :ced, :gen, :fnac, :obs, :nrep, :crep, :u)");
        $db->bind(':e',    $id);
        $db->bind(':nom',  $d['nombre_libre']);
        $db->bind(':ape',  $d['apellido_libre']  ?? null);
        $db->bind(':ced',  $d['cedula_libre']    ?? null);
        $db->bind(':gen',  $d['genero_libre']    ?? null);
        $db->bind(':fnac', $d['fecha_nac_libre'] ?? null);
        $db->bind(':obs',  $d['observaciones']   ?? null);
        $db->bind(':nrep', $d['nombre_representante'] ?? null);
        $db->bind(':crep', $d['cedula_representante'] ?? null);
        $db->bind(':u',    $userId);
        $ok = $db->execute();
        self::auditStatic('participantes_ruta', 'INSERT', null, null,
            ['id_ejecucion' => $id, 'nombre_libre' => $d['nombre_libre']], $userId);
        return $ok;
    }

    public static function desinscribir(int $idParticipante, int $userId) {
        $db = new Database();
        $db->query("UPDATE participantes_ruta
                       SET is_active = FALSE, deleted_at = CURRENT_TIMESTAMP, deleted_by = :u
                     WHERE id = :id");
        $db->bind(':id', $idParticipante);
        $db->bind(':u',  $userId);
        $ok = $db->execute();
        self::auditStatic('participantes_ruta', 'DELETE', $idParticipante, null, null, $userId);
        return $ok;
    }

    // ── Asistencia ───────────────────────────────────────────────────────────

    public static function marcarAsistencia(int $idParticipante, bool $asistio, $userId): void {
        $db = new Database();
        $db->query("UPDATE participantes_ruta SET asistio = :a, updated_at = NOW(), updated_by = :u
                     WHERE id = :id AND is_active = TRUE");
        $db->bind(':a', $asistio);
        $db->bind(':u', $userId);
        $db->bind(':id', $idParticipante);
        $db->execute();
    }

    public static function marcarAsistenciaMasiva(int $id, $userId): void {
        $db = new Database();
        $db->query("UPDATE participantes_ruta SET asistio = TRUE, updated_at = NOW(), updated_by = :u
                     WHERE id_ejecucion = :e AND is_active = TRUE AND asistio = FALSE");
        $db->bind(':u', $userId);
        $db->bind(':e', $id);
        $db->execute();
    }

    // ── Oficio emitido ───────────────────────────────────────────────────────

    /** Genera el correlativo, guarda el registro y devuelve el número asignado. */
    public static function crearOficioEmitido(int $id, array $d, int $userId): string {
        $ej = self::find($id);
        if (!$ej) throw new Exception('La salida no existe.');

        $numero = ConfigSistema::generarNumeroOficio('ruta');
        $db     = new Database();
        $db->query("INSERT INTO oficios_emitidos
                        (numero, fecha, destinatario_nombre, destinatario_cargo, asunto,
                         id_ruta, id_ejecucion, created_by)
                    VALUES (:num, CURRENT_DATE, :dn, :dc, :asunto, :ruta, :ejec, :uid)");
        $db->bind(':num',    $numero);
        $db->bind(':dn',     $d['destinatario_nombre'] ?? '');
        $db->bind(':dc',     $d['destinatario_cargo']  ?? '');
        $db->bind(':asunto', $d['asunto']              ?? '');
        $db->bind(':ruta',   (int)$ej->id_ruta);   // se conserva por compatibilidad
        $db->bind(':ejec',   $id);
        $db->bind(':uid',    $userId);
        $db->execute();
        return $numero;
    }

    public static function getUltimoOficio(int $id): ?object {
        $db = new Database();
        $db->query("SELECT * FROM oficios_emitidos WHERE id_ejecucion = :id
                     ORDER BY created_at DESC LIMIT 1");
        $db->bind(':id', $id);
        return $db->single() ?: null;
    }

    // ── Informe / conteo demográfico ─────────────────────────────────────────
    // Es la «Ficha Institucional» del cliente (R-43 = R-47 = R-48): la planilla
    // del día y el informe de cierre resultaron ser el MISMO documento.
    // La fase T-G la completa con docentes, representantes e instituciones de
    // apoyo; de momento conserva el desglose que ya existía.

    public static function getInforme(int $id): ?object {
        $db = new Database();
        $db->query("SELECT * FROM ruta_informes WHERE id_ejecucion = :id");
        $db->bind(':id', $id);
        return $db->single() ?: null;
    }

    public static function saveInforme(array $d): bool {
        $db     = new Database();
        $userId = $_SESSION['user_id'] ?? null;
        $id     = (int)$d['id_ejecucion'];
        $inf    = self::getInforme($id);
        $total  = (int)$d['mujeres'] + (int)$d['hombres'] + (int)$d['ninas'] + (int)$d['ninos'];

        if ($inf) {
            $db->query("UPDATE ruta_informes
                           SET lugar_exacto=:lugar, mujeres=:m, hombres=:h, ninas=:ni, ninos=:no,
                               total_atendidos=:tot, observaciones=:obs, resumen_visita=:res,
                               updated_at=CURRENT_TIMESTAMP
                         WHERE id_ejecucion=:id");
        } else {
            $db->query("INSERT INTO ruta_informes
                            (id_ejecucion, lugar_exacto, mujeres, hombres, ninas, ninos,
                             total_atendidos, observaciones, resumen_visita, created_by)
                        VALUES (:id, :lugar, :m, :h, :ni, :no, :tot, :obs, :res, :uid)");
            $db->bind(':uid', $userId);
        }
        $db->bind(':id',    $id);
        $db->bind(':lugar', $d['lugar_exacto']  ?? '');
        $db->bind(':m',     (int)$d['mujeres']);
        $db->bind(':h',     (int)$d['hombres']);
        $db->bind(':ni',    (int)$d['ninas']);
        $db->bind(':no',    (int)$d['ninos']);
        $db->bind(':tot',   $total);
        $db->bind(':obs',   $d['observaciones']  ?? null);
        $db->bind(':res',   $d['resumen_visita'] ?? '');
        return $db->execute();
    }
}
