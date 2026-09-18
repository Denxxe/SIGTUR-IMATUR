<?php
/**
 * Cobro de una salida de ruta — fase T-C (mig. 083).
 *
 * R-40 preguntaba si el sistema debe **llevar la contabilidad** de los cobros o
 * solo dejar constancia de que la ruta tenía tarifa. La respuesta fue *«Sí debe
 * llevar el cobro… y lo cancelado»*, así que esto es un registro de pagos: hay
 * abonos, varias transferencias del mismo grupo y pagos de representantes
 * distintos.
 *
 * DOS COSAS QUE NO SON OBVIAS:
 *
 * 1. **La tarifa se pacta en USD y se cobra en Bs a la tasa del día** (R-36),
 *    así que cada salida **congela** su tarifa y su tasa. Nada de releerlas del
 *    catálogo al calcular: si mañana sube el dólar, lo cobrado la semana pasada
 *    no se mueve. Es el mismo criterio de la mig. 074 con la nómina.
 *
 * 2. **La gratuidad no es automática por ser institución** (R-03/R-42): depende
 *    de la RUTA. Las instituciones públicas no pagan Cumaná Histórica, pero sí
 *    Playa Colorada. Por eso `rutas.exonera_instituciones` es por ruta y no una
 *    regla global.
 */
class PagoRuta extends Model {

    const FORMA_TRANSFERENCIA = 'Transferencia';
    const FORMA_EFECTIVO      = 'Efectivo';
    const FORMA_PUNTO         = 'Punto de venta';

    const FORMAS = [self::FORMA_TRANSFERENCIA, self::FORMA_EFECTIVO, self::FORMA_PUNTO];

    // Modos de tarifa del catálogo (R-41)
    const TARIFA_GRATUITA   = 'Gratuita';
    const TARIFA_FIJA       = 'Fija';
    const TARIFA_A_CONVENIR = 'A convenir';

    const TARIFA_MODOS = [self::TARIFA_GRATUITA, self::TARIFA_FIJA, self::TARIFA_A_CONVENIR];

    // ── Lectura ──────────────────────────────────────────────────────────────

    public static function find(int $id): ?object {
        $db = new Database();
        $db->query("SELECT * FROM ruta_pagos WHERE id = :id AND is_active = TRUE");
        $db->bind(':id', $id);
        return $db->single() ?: null;
    }

    /** Los pagos de una salida, el más reciente primero. Los anulados se ven. */
    public static function porEjecucion(int $idEjecucion): array {
        $db = new Database();
        $db->query("SELECT * FROM ruta_pagos
                     WHERE id_ejecucion = :e AND is_active = TRUE
                     ORDER BY fecha DESC, id DESC");
        $db->bind(':e', $idEjecucion);
        return $db->resultSet();
    }

    /**
     * El estado de cuenta de una salida. Lo usa la pantalla y el reporte, para
     * que los dos digan lo mismo.
     *
     * `esperado_bs` sale de la tarifa y la tasa **congeladas** en la salida, por
     * los participantes registrados. Con tarifa «a convenir» o exonerada no hay
     * esperado que calcular: lo que manda es lo cobrado.
     */
    public static function estadoDeCuenta(int $idEjecucion): array {
        $ej = RutaEjecucion::find($idEjecucion);
        if (!$ej) throw new Exception('La salida no existe.');

        $db = new Database();
        $db->query("SELECT COALESCE(SUM(monto_bs), 0)  AS bs,
                           COALESCE(SUM(monto_usd), 0) AS usd,
                           COUNT(*)                    AS n
                      FROM ruta_pagos
                     WHERE id_ejecucion = :e AND is_active = TRUE AND anulado = FALSE");
        $db->bind(':e', $idEjecucion);
        $pag = $db->single();

        $tarifa   = $ej->tarifa_usd !== null ? (float)$ej->tarifa_usd : null;
        $tasa     = $ej->tasa_cambio !== null ? (float)$ej->tasa_cambio : null;
        $personas = (int)($ej->total_participantes ?? 0);
        $exonerada = !empty($ej->es_exonerada);

        $esperadoUsd = null; $esperadoBs = null;
        if (!$exonerada && $tarifa !== null && $tarifa > 0) {
            $esperadoUsd = round($tarifa * $personas, 2);
            if ($tasa !== null && $tasa > 0) $esperadoBs = round($esperadoUsd * $tasa, 2);
        }

        $cobradoBs = (float)$pag->bs;
        return [
            'exonerada'    => $exonerada,
            'tarifa_usd'   => $tarifa,
            'tasa'         => $tasa,
            'personas'     => $personas,
            'esperado_usd' => $esperadoUsd,
            'esperado_bs'  => $esperadoBs,
            'cobrado_bs'   => $cobradoBs,
            'cobrado_usd'  => (float)$pag->usd,
            'pagos'        => (int)$pag->n,
            // NULL cuando no hay nada que comparar: sin esperado no hay saldo.
            'saldo_bs'     => $esperadoBs !== null ? round($esperadoBs - $cobradoBs, 2) : null,
            'solvente'     => $exonerada || ($esperadoBs !== null && $cobradoBs + 0.01 >= $esperadoBs),
            'vencido'      => self::topeVencido($ej, $cobradoBs, $esperadoBs, $exonerada),
        ];
    }

    /**
     * R-38: el pago es anticipado y con fecha tope, «para poder planificar la
     * salida». Vencido = pasó el tope, no está exonerada y falta por cobrar.
     */
    private static function topeVencido(object $ej, float $cobrado, ?float $esperado, bool $exonerada): bool {
        if ($exonerada || empty($ej->fecha_tope_pago)) return false;
        if ($ej->fecha_tope_pago >= date('Y-m-d'))     return false;
        if ($esperado === null)                        return $cobrado <= 0;
        return $cobrado + 0.01 < $esperado;
    }

    // ── Tarifa y tasa de la salida ───────────────────────────────────────────

    /**
     * Congela en la salida lo que se le va a cobrar. Se llama al programarla y
     * se puede corregir mientras esté abierta.
     *
     * La tarifa se propone desde el catálogo, pero se guarda **en la salida**:
     * con «a convenir» (R-41, Altos de Cumaná) el monto se pacta cada vez.
     */
    public static function fijarCondiciones(int $id, array $d, $user_id = null): bool {
        $ej = RutaEjecucion::find($id);
        if (!$ej) throw new Exception('La salida no existe.');
        if (in_array($ej->estado, RutaEjecucion::ESTADOS_TERMINALES, true)) {
            throw new Exception('Esta salida ya está «' . $ej->estado . '»: sus condiciones de cobro no se cambian.');
        }

        $tarifa = ($d['tarifa_usd'] ?? '') === '' ? null : round((float)$d['tarifa_usd'], 2);
        $tasa   = ($d['tasa_cambio'] ?? '') === '' ? null : round((float)$d['tasa_cambio'], 4);
        $tope   = trim((string)($d['fecha_tope_pago'] ?? '')) ?: null;

        if ($tarifa !== null && $tarifa < 0) throw new Exception('La tarifa no puede ser negativa.');
        if ($tasa   !== null && $tasa  <= 0) throw new Exception('La tasa de cambio debe ser mayor que cero.');
        if ($tarifa !== null && $tarifa > 0 && $tasa === null) {
            throw new Exception('Si la salida se cobra, indique la tasa de cambio con la que se calcula en bolívares.');
        }
        if ($tope !== null && $tope > $ej->fecha) {
            throw new Exception('La fecha tope de pago no puede ser posterior a la salida: el pago es anticipado (R-38).');
        }

        $db = new Database();
        $db->query("UPDATE ruta_ejecuciones
                       SET tarifa_usd = :t, tasa_cambio = :tc, tasa_fecha = :tf,
                           fecha_tope_pago = :tope,
                           updated_at = CURRENT_TIMESTAMP, updated_by = :u
                     WHERE id = :id");
        $db->bind(':t',    $tarifa);
        $db->bind(':tc',   $tasa);
        $db->bind(':tf',   $tasa !== null ? (trim((string)($d['tasa_fecha'] ?? '')) ?: date('Y-m-d')) : null);
        $db->bind(':tope', $tope);
        $db->bind(':u',    $user_id);
        $db->bind(':id',   $id);
        $ok = $db->execute();
        self::auditStatic('ruta_ejecuciones', 'UPDATE', $id, $ej,
            ['tarifa_usd' => $tarifa, 'tasa_cambio' => $tasa, 'fecha_tope_pago' => $tope], $user_id);
        return $ok;
    }

    /**
     * Lo que el catálogo sugiere cobrarle a esta salida, antes de congelarlo.
     * Aplica las exoneraciones que el cliente dejó fijas (R-03/R-42) — que son
     * **por ruta**, no generales.
     */
    public static function sugerenciaTarifa(object $ejec, object $ruta): array {
        $modo = $ruta->tarifa_modo ?? self::TARIFA_GRATUITA;

        if ($modo === self::TARIFA_GRATUITA) {
            return ['tarifa' => 0, 'motivo' => 'El recorrido es gratuito.'];
        }
        if (!empty($ruta->exonera_instituciones) && ($ejec->origen ?? '') === 'Institucional') {
            return ['tarifa' => 0,
                    'motivo' => 'Institución pública en un recorrido exonerado (R-03). Recuerde que igual debe traer el oficio.'];
        }
        if ($modo === self::TARIFA_A_CONVENIR) {
            return ['tarifa' => null,
                    'motivo' => 'La tarifa de este recorrido se pacta en cada salida (R-41).'];
        }
        return ['tarifa' => (float)$ruta->tarifa_monto, 'motivo' => null];
    }

    // ── Exoneración (R-42) ───────────────────────────────────────────────────

    /**
     * R-42: quien autoriza es la Presidenta. El sistema no decide nada: registra
     * el motivo, quién lo asentó y cuándo. Por eso el motivo es obligatorio —
     * un cobro perdonado sin explicación no es un registro, es un agujero.
     */
    public static function exonerar(int $id, string $motivo, $user_id = null): bool {
        $ej = RutaEjecucion::find($id);
        if (!$ej) throw new Exception('La salida no existe.');
        if (!empty($ej->es_exonerada)) throw new Exception('Esta salida ya está exonerada.');
        if (trim($motivo) === '') throw new Exception('Indique el motivo de la exoneración y quién la autorizó.');

        $db = new Database();
        $db->query("UPDATE ruta_ejecuciones
                       SET es_exonerada = TRUE, motivo_exoneracion = :m,
                           exonerada_por = :u, fecha_exoneracion = CURRENT_DATE,
                           updated_at = CURRENT_TIMESTAMP, updated_by = :u
                     WHERE id = :id");
        $db->bind(':m',  trim($motivo));
        $db->bind(':u',  $user_id);
        $db->bind(':id', $id);
        $ok = $db->execute();
        self::auditStatic('ruta_ejecuciones', 'UPDATE', $id, $ej,
            ['es_exonerada' => true, 'motivo' => $motivo], $user_id);
        return $ok;
    }

    public static function quitarExoneracion(int $id, $user_id = null): bool {
        $ej = RutaEjecucion::find($id);
        if (!$ej) throw new Exception('La salida no existe.');
        if (empty($ej->es_exonerada)) throw new Exception('Esta salida no está exonerada.');

        $db = new Database();
        $db->query("UPDATE ruta_ejecuciones
                       SET es_exonerada = FALSE, motivo_exoneracion = NULL,
                           exonerada_por = NULL, fecha_exoneracion = NULL,
                           updated_at = CURRENT_TIMESTAMP, updated_by = :u
                     WHERE id = :id");
        $db->bind(':u',  $user_id);
        $db->bind(':id', $id);
        $ok = $db->execute();
        self::auditStatic('ruta_ejecuciones', 'UPDATE', $id, $ej, ['es_exonerada' => false], $user_id);
        return $ok;
    }

    // ── Pagos ────────────────────────────────────────────────────────────────

    /**
     * Registra un pago. Devuelve `['id' => n, 'acta' => 'ACTP-001/2026'|null]`.
     *
     * El acta solo se numera para **efectivo** (R-39): la transferencia ya trae
     * su propio respaldo —la captura o el voucher—, y numerar un acta que nadie
     * va a levantar solo gastaría correlativos.
     */
    public static function registrar(int $idEjecucion, array $d, $user_id = null): array {
        $ej = RutaEjecucion::find($idEjecucion);
        if (!$ej) throw new Exception('La salida no existe.');
        if (!empty($ej->es_exonerada)) {
            throw new Exception('Esta salida está exonerada: no hay pago que registrar. Quite la exoneración primero.');
        }

        $forma = $d['forma'] ?? '';
        if (!in_array($forma, self::FORMAS, true)) throw new Exception('Indique la forma de pago.');

        $montoBs = round((float)($d['monto_bs'] ?? 0), 2);
        if ($montoBs <= 0) throw new Exception('El monto en bolívares debe ser mayor que cero.');

        $fecha = trim((string)($d['fecha'] ?? '')) ?: date('Y-m-d');
        if ($fecha > date('Y-m-d')) throw new Exception('No se puede registrar un pago con fecha futura.');

        // La tasa del pago: la que se le pasa, o la congelada de la salida.
        $tasa = ($d['tasa_aplicada'] ?? '') === '' ? null : round((float)$d['tasa_aplicada'], 4);
        if ($tasa === null && $ej->tasa_cambio !== null) $tasa = (float)$ej->tasa_cambio;
        if ($tasa !== null && $tasa <= 0) throw new Exception('La tasa aplicada debe ser mayor que cero.');
        $montoUsd = $tasa !== null && $tasa > 0 ? round($montoBs / $tasa, 2) : null;

        if ($forma === self::FORMA_TRANSFERENCIA && trim((string)($d['referencia'] ?? '')) === '') {
            throw new Exception('Indique el número de referencia de la transferencia.');
        }

        $acta = null;
        $db   = new Database();
        $db->beginTransaction();
        try {
            // El correlativo se pide DENTRO de la transacción y después de
            // validar: un intento fallido no puede quemar un número.
            if ($forma === self::FORMA_EFECTIVO) {
                $acta = 'ACTP-' . ConfigSistema::generarNumeroOficio('actapago');
            }

            $db->query("INSERT INTO ruta_pagos
                            (id_ejecucion, fecha, forma, monto_bs, monto_usd, tasa_aplicada,
                             personas, pagador_nombre, pagador_cedula, referencia,
                             acta_numero, observaciones, created_by)
                        VALUES (:e, :f, :fo, :bs, :usd, :tasa, :per, :pn, :pc, :ref, :acta, :obs, :u)
                        RETURNING id");
            $db->bind(':e',    $idEjecucion);
            $db->bind(':f',    $fecha);
            $db->bind(':fo',   $forma);
            $db->bind(':bs',   $montoBs);
            $db->bind(':usd',  $montoUsd);
            $db->bind(':tasa', $tasa);
            $db->bind(':per',  ($d['personas'] ?? '') === '' ? null : (int)$d['personas']);
            $db->bind(':pn',   trim((string)($d['pagador_nombre'] ?? '')) ?: null);
            $db->bind(':pc',   trim((string)($d['pagador_cedula'] ?? '')) ?: null);
            $db->bind(':ref',  trim((string)($d['referencia'] ?? '')) ?: null);
            $db->bind(':acta', $acta);
            $db->bind(':obs',  trim((string)($d['observaciones'] ?? '')) ?: null);
            $db->bind(':u',    $user_id);
            $idPago = (int)$db->single()->id;

            $db->endTransaction();
        } catch (Exception $e) {
            $db->cancelTransaction();
            throw $e;
        }

        self::auditStatic('ruta_pagos', 'INSERT', $idPago, null,
            ['id_ejecucion' => $idEjecucion, 'monto_bs' => $montoBs, 'forma' => $forma], $user_id);
        return ['id' => $idPago, 'acta' => $acta];
    }

    /** La captura o el voucher de una transferencia (R-39). */
    public static function guardarComprobante(int $id, string $archivo, ?string $original, $user_id = null): bool {
        if (!self::find($id)) throw new Exception('El pago no existe.');
        $db = new Database();
        $db->query("UPDATE ruta_pagos
                       SET comprobante_archivo = :a, comprobante_original = :o,
                           updated_at = CURRENT_TIMESTAMP, updated_by = :u
                     WHERE id = :id");
        $db->bind(':a', $archivo);
        $db->bind(':o', $original);
        $db->bind(':u', $user_id);
        $db->bind(':id', $id);
        $ok = $db->execute();
        self::auditStatic('ruta_pagos', 'UPDATE', $id, null, ['comprobante_original' => $original], $user_id);
        return $ok;
    }

    /**
     * Anular ≠ borrar: es dinero. La fila queda con su motivo y deja de sumar.
     * El número de acta tampoco se recicla — mismo criterio que en Bienes.
     */
    public static function anular(int $id, string $motivo, $user_id = null): bool {
        $p = self::find($id);
        if (!$p) throw new Exception('El pago no existe.');
        if (!empty($p->anulado)) throw new Exception('Este pago ya está anulado.');
        if (trim($motivo) === '') throw new Exception('Indique por qué se anula el pago.');

        $db = new Database();
        $db->query("UPDATE ruta_pagos
                       SET anulado = TRUE, motivo_anulacion = :m,
                           updated_at = CURRENT_TIMESTAMP, updated_by = :u
                     WHERE id = :id");
        $db->bind(':m',  trim($motivo));
        $db->bind(':u',  $user_id);
        $db->bind(':id', $id);
        $ok = $db->execute();
        self::auditStatic('ruta_pagos', 'UPDATE', $id, $p, ['anulado' => true, 'motivo' => $motivo], $user_id);
        return $ok;
    }
}
