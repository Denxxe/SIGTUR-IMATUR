<?php
/**
 * Codificación propia de bienes (R-12 · C-1…C-4, mig. 087).
 *
 * Procedimiento notificado el 2026-09-02: IMATUR asigna el código de sus
 * bienes, continuando la secuencia desde la última revisión de la Alcaldía.
 * Ver docs/PLAN_MODULO_BIENES.md §2-ter y §2-sexies.
 *
 * El motor está construido pero arranca APAGADO. Mientras lo esté, codificar
 * es transcribir el BM-1 (comportamiento de siempre). Para encenderlo hacen
 * falta dos datos del cliente, y por eso la activación se bloquea sin ellos:
 *   · B-73 — punto de partida (`bienes_nro_orden_inicio`);
 *   · B-75 — al menos una clasificación vigente en `inventario_clasificaciones`.
 * B-74 (una secuencia o una por clasificación; qué pasa al pasar de 999) es un
 * parámetro: `bienes_secuencia_por_clasificacion` y `bienes_nro_orden_digitos`.
 *
 * Los códigos NUNCA se reciclan (B-76): el siguiente número se calcula sobre
 * todos los bienes registrados, incluidos los desincorporados y la papelera.
 */
class CodificacionBienes extends Model
{
    const QUIEN_ALCALDIA = 'Alcaldía';
    const QUIEN_IMATUR   = 'IMATUR';

    const CLAVE_ACTIVA    = 'bienes_codificacion_propia';
    const CLAVE_INICIO    = 'bienes_nro_orden_inicio';
    const CLAVE_DIGITOS   = 'bienes_nro_orden_digitos';
    const CLAVE_POR_CLASE = 'bienes_secuencia_por_clasificacion';

    const DIGITOS_DEFAULT = 3;

    // =====================================================================
    // Parámetros
    // =====================================================================

    public static function params(): array
    {
        $inicio = trim((string)ConfigSistema::get(self::CLAVE_INICIO));
        $dig    = (int)ConfigSistema::get(self::CLAVE_DIGITOS);
        return [
            'activa'            => ConfigSistema::get(self::CLAVE_ACTIVA) === '1',
            'inicio'            => ctype_digit($inicio) ? (int)$inicio : null,
            'digitos'           => $dig > 0 ? $dig : self::DIGITOS_DEFAULT,
            'por_clasificacion' => ConfigSistema::get(self::CLAVE_POR_CLASE) === '1',
        ];
    }

    public static function activa(): bool
    {
        return self::params()['activa'];
    }

    /** Lo que falta para poder encenderla (vacío = se puede). */
    public static function faltantes(): array
    {
        $p = self::params();
        $f = [];
        if ($p['inicio'] === null) {
            $f[] = 'el punto de partida de la secuencia (B-73): el último N° de orden que deje la Alcaldía en su última revisión';
        }
        if (!self::clasificaciones(true)) {
            $f[] = 'al menos una clasificación (grupo-subgrupo-sección) vigente en el catálogo (B-75)';
        }
        return $f;
    }

    /**
     * Guarda los parámetros. Encender exige que no falte nada: sin punto de
     * partida el sistema propondría el 001 y chocaría con la numeración que la
     * Alcaldía ya asignó.
     */
    public static function guardarParams(array $d, ?int $userId = null): void
    {
        $inicio = trim((string)($d['inicio'] ?? ''));
        if ($inicio !== '' && !ctype_digit($inicio)) {
            throw new Exception('El punto de partida debe ser un número entero (el último N° de orden asignado, ej. 171).');
        }
        $dig = (int)($d['digitos'] ?? self::DIGITOS_DEFAULT);
        if ($dig < 1 || $dig > 8) throw new Exception('Los dígitos del N° de orden deben estar entre 1 y 8.');

        ConfigSistema::set(self::CLAVE_INICIO, $inicio === '' ? '' : (string)(int)$inicio, $userId);
        ConfigSistema::set(self::CLAVE_DIGITOS, (string)$dig, $userId);
        ConfigSistema::set(self::CLAVE_POR_CLASE, !empty($d['por_clasificacion']) ? '1' : '0', $userId);

        $activar = !empty($d['activa']);
        if ($activar && ($falta = self::faltantes())) {
            ConfigSistema::set(self::CLAVE_ACTIVA, '0', $userId);
            throw new Exception('Los parámetros se guardaron, pero la codificación propia no se puede encender todavía. Falta: '
                . implode('; ', $falta) . '.');
        }
        ConfigSistema::set(self::CLAVE_ACTIVA, $activar ? '1' : '0', $userId);
    }

    // =====================================================================
    // El siguiente N° de orden
    // =====================================================================

    /**
     * FUNCIÓN PURA. Siguiente N° de orden a partir de los ya usados.
     *
     * Toma el mayor entre el punto de partida y el mayor número existente, y
     * le suma uno. Los números se comparan como enteros («084» = 84): la
     * Alcaldía los escribe con ceros a la izquierda y el listado viene
     * ordenado por departamento, no por código (B-72) — por eso se busca el
     * MÁXIMO de todo el registro, nunca el último de una hoja. Lo que no es
     * numérico (p. ej. «S/C») se ignora.
     */
    public static function calcularSiguiente(array $existentes, ?int $inicio, int $digitos): string
    {
        $max = $inicio ?? 0;
        foreach ($existentes as $n) {
            $n = trim((string)$n);
            if ($n !== '' && ctype_digit($n)) $max = max($max, (int)$n);
        }
        return self::formatear($max + 1, $digitos);
    }

    /** Rellena con ceros a la izquierda; si el número supera los dígitos, va completo. */
    public static function formatear(int $numero, int $digitos): string
    {
        return str_pad((string)$numero, max(1, $digitos), '0', STR_PAD_LEFT);
    }

    /**
     * Siguiente N° de orden según la configuración. Con secuencia por
     * clasificación, cuenta solo los bienes de ese grupo-subgrupo-sección.
     */
    public static function siguienteNroOrden(?string $g = null, ?string $sg = null, ?string $sec = null): string
    {
        $p  = self::params();
        $db = new Database();
        if ($p['por_clasificacion'] && $g !== null && $sg !== null && $sec !== null) {
            $db->query("SELECT nro_orden FROM inventario
                         WHERE nro_orden IS NOT NULL
                           AND codigo_grupo = :g AND codigo_subgrupo = :sg AND codigo_seccion = :sec");
            $db->bind(':g', $g); $db->bind(':sg', $sg); $db->bind(':sec', $sec);
        } else {
            // Todos: activos, desincorporados y papelera — un código no se recicla (B-76).
            $db->query("SELECT nro_orden FROM inventario WHERE nro_orden IS NOT NULL");
        }
        $usados = array_map(fn($r) => $r->nro_orden, $db->resultSet());
        return self::calcularSiguiente($usados, $p['inicio'], $p['digitos']);
    }

    /**
     * ¿Ese N° de orden ya está usado? Compara como entero («84» = «084»). Con
     * secuencia por clasificación, el número solo se repite dentro de la misma.
     */
    public static function nroOrdenOcupado(string $orden, ?int $excluirId, ?string $g, ?string $sg, ?string $sec): ?int
    {
        $orden = trim($orden);
        if ($orden === '') return null;
        $porClase = self::params()['por_clasificacion'];
        $db = new Database();
        $sql = "SELECT id FROM inventario
                 WHERE is_active = TRUE AND nro_orden IS NOT NULL
                   AND LTRIM(nro_orden, '0') = LTRIM(:o, '0')";
        if ($porClase) $sql .= " AND codigo_grupo = :g AND codigo_subgrupo = :sg AND codigo_seccion = :sec";
        if ($excluirId) $sql .= " AND id <> :x";
        $db->query($sql . " LIMIT 1");
        $db->bind(':o', $orden);
        if ($porClase) { $db->bind(':g', (string)$g); $db->bind(':sg', (string)$sg); $db->bind(':sec', (string)$sec); }
        if ($excluirId) $db->bind(':x', $excluirId);
        $r = $db->single();
        return $r ? (int)$r->id : null;
    }

    // =====================================================================
    // Catálogo de clasificaciones (C-4 · B-75)
    // =====================================================================

    public static function clasificaciones(bool $soloVigentes = false): array
    {
        $db = new Database();
        $db->query("SELECT c.*, (SELECT COUNT(*) FROM inventario i
                                   WHERE i.codigo_grupo = c.grupo AND i.codigo_subgrupo = c.subgrupo
                                     AND i.codigo_seccion = c.seccion AND i.is_active = TRUE) AS bienes
                      FROM inventario_clasificaciones c"
                   . ($soloVigentes ? " WHERE c.is_active = TRUE" : "")
                   . " ORDER BY c.grupo::int, c.subgrupo::int, c.seccion::int");
        return $db->resultSet();
    }

    public static function existeClasificacion(string $g, string $sg, string $sec): bool
    {
        $db = new Database();
        $db->query("SELECT 1 FROM inventario_clasificaciones
                     WHERE grupo = :g AND subgrupo = :sg AND seccion = :sec AND is_active = TRUE");
        $db->bind(':g', $g); $db->bind(':sg', $sg); $db->bind(':sec', $sec);
        return (bool)$db->single();
    }

    public static function guardarClasificacion(array $d, ?int $userId = null): void
    {
        $id  = (int)($d['id'] ?? 0);
        $g   = trim((string)($d['grupo'] ?? ''));
        $sg  = trim((string)($d['subgrupo'] ?? ''));
        $sec = trim((string)($d['seccion'] ?? ''));
        $desc = trim((string)($d['descripcion'] ?? ''));
        if (!preg_match('/^\d{1,4}$/', $g) || !preg_match('/^\d{1,4}$/', $sg) || !preg_match('/^\d{1,6}$/', $sec)) {
            throw new Exception('Grupo, sub-grupo y sección son números (ej. 2 · 01 · 108), como en el BM-1.');
        }
        $db = new Database();
        $db->query("SELECT id FROM inventario_clasificaciones WHERE grupo = :g AND subgrupo = :sg AND seccion = :sec"
                   . ($id ? " AND id <> :id" : ""));
        $db->bind(':g', $g); $db->bind(':sg', $sg); $db->bind(':sec', $sec);
        if ($id) $db->bind(':id', $id);
        if ($db->single()) throw new Exception("La clasificación $g-$sg-$sec ya existe.");

        if ($id) {
            // El código no cambia si ya hay bienes con él: renombrarlo los dejaría fuera del catálogo.
            $db->query("SELECT c.*, (SELECT COUNT(*) FROM inventario i WHERE i.codigo_grupo = c.grupo
                               AND i.codigo_subgrupo = c.subgrupo AND i.codigo_seccion = c.seccion) AS bienes
                          FROM inventario_clasificaciones c WHERE c.id = :id");
            $db->bind(':id', $id);
            $prev = $db->single();
            if (!$prev) throw new Exception('La clasificación no existe.');
            if ((int)$prev->bienes > 0 && "$g-$sg-$sec" !== "{$prev->grupo}-{$prev->subgrupo}-{$prev->seccion}") {
                throw new Exception('Hay bienes codificados con ' . "{$prev->grupo}-{$prev->subgrupo}-{$prev->seccion}" . ': su código no se puede cambiar, solo la descripción.');
            }
            $db->query("UPDATE inventario_clasificaciones SET grupo = :g, subgrupo = :sg, seccion = :sec,
                               descripcion = :d, updated_at = CURRENT_TIMESTAMP, updated_by = :u WHERE id = :id");
            $db->bind(':id', $id);
        } else {
            $db->query("INSERT INTO inventario_clasificaciones (grupo, subgrupo, seccion, descripcion, created_by)
                        VALUES (:g, :sg, :sec, :d, :u)");
        }
        $db->bind(':g', $g); $db->bind(':sg', $sg); $db->bind(':sec', $sec);
        $db->bind(':d', $desc !== '' ? $desc : null);
        $db->bind(':u', $userId);
        $db->execute();
        self::auditStatic('inventario_clasificaciones', $id ? 'UPDATE' : 'INSERT', $id,
            null, ['grupo' => $g, 'subgrupo' => $sg, 'seccion' => $sec, 'descripcion' => $desc], $userId);
    }

    /** Dar de baja o reactivar (no se borra: puede haber bienes codificados con ella). */
    public static function cambiarVigencia(int $id, bool $vigente, ?int $userId = null): void
    {
        $db = new Database();
        $db->query("UPDATE inventario_clasificaciones SET is_active = :a,
                           updated_at = CURRENT_TIMESTAMP, updated_by = :u WHERE id = :id");
        $db->bind(':a', $vigente, PDO::PARAM_BOOL);
        $db->bind(':u', $userId);
        $db->bind(':id', $id);
        $db->execute();
        if ($db->rowCount() < 1) throw new Exception('La clasificación no existe.');
        if (!$vigente && self::activa() && !self::clasificaciones(true)) {
            // Sin catálogo no se puede codificar: se apaga para no dejar el módulo trabado.
            ConfigSistema::set(self::CLAVE_ACTIVA, '0', $userId);
        }
        self::auditStatic('inventario_clasificaciones', 'UPDATE', $id, null, ['is_active' => $vigente], $userId);
    }
}
