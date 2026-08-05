<?php
/**
 * DotacionInventario — suficiencia de bienes por departamento (B-63, mig. 067).
 *
 * El cliente aclaró que NO es stock de consumibles (no llevan): es saber si
 * **alcanzan** los bienes, medido **por el número de empleados** de cada
 * departamento.
 *
 * `inventario_dotacion` define cuántas unidades de cada categoría
 * corresponden por empleado. El reporte compara, departamento por
 * departamento, lo que hay contra lo que debería haber.
 *
 * Solo se evalúan las categorías con dotación definida: las que no se
 * reparten por persona (herramientas, material turístico, bienes
 * culturales) simplemente no tienen fila y no aparecen en el análisis.
 */
class DotacionInventario extends Model {

    /** Dotaciones definidas, con el nombre de su categoría. */
    public static function all() {
        $db = new Database();
        $db->query("SELECT d.*, c.nombre AS categoria
                      FROM inventario_dotacion d
                      INNER JOIN categorias c ON c.id = d.id_categoria
                     WHERE d.is_active = TRUE
                     ORDER BY c.nombre");
        return $db->resultSet();
    }

    /** Categorías que aún no tienen dotación definida (para el selector). */
    public static function categoriasSinDotacion() {
        $db = new Database();
        $db->query("SELECT c.id, c.nombre
                      FROM categorias c
                     WHERE c.is_active = TRUE
                       AND NOT EXISTS (SELECT 1 FROM inventario_dotacion d
                                        WHERE d.id_categoria = c.id AND d.is_active = TRUE)
                     ORDER BY c.nombre");
        return $db->resultSet();
    }

    public static function guardar(int $idCategoria, $unidades, string $obs = '', $user_id = null): bool {
        $u = (float)$unidades;
        if ($u <= 0 || $u > 99) {
            throw new Exception('Las unidades por empleado deben estar entre 0,01 y 99.');
        }
        $db = new Database();
        $db->query("SELECT id FROM inventario_dotacion
                     WHERE id_categoria = :c AND is_active = TRUE LIMIT 1");
        $db->bind(':c', $idCategoria);
        $existe = $db->single();

        if ($existe) {
            $db->query("UPDATE inventario_dotacion
                           SET unidades_por_empleado = :u, observaciones = :o,
                               updated_at = CURRENT_TIMESTAMP, updated_by = :uid
                         WHERE id = :id");
            $db->bind(':id', (int)$existe->id);
        } else {
            $db->query("INSERT INTO inventario_dotacion
                            (id_categoria, unidades_por_empleado, observaciones, created_by)
                        VALUES (:c, :u, :o, :uid)");
            $db->bind(':c', $idCategoria);
        }
        $db->bind(':u',   $u);
        $db->bind(':o',   trim($obs) ?: null);
        $db->bind(':uid', $user_id);
        $ok = $db->execute();
        self::auditStatic('inventario_dotacion', $existe ? 'UPDATE' : 'INSERT',
            $existe->id ?? null, $existe, ['id_categoria' => $idCategoria, 'unidades' => $u], $user_id);
        return $ok;
    }

    public static function eliminar(int $id, $user_id = null): bool {
        $db = new Database();
        $db->query("UPDATE inventario_dotacion SET is_active = FALSE,
                        updated_at = CURRENT_TIMESTAMP, updated_by = :u WHERE id = :id");
        $db->bind(':id', $id);
        $db->bind(':u',  $user_id);
        $ok = $db->execute();
        self::auditStatic('inventario_dotacion', 'DELETE', $id, null, null, $user_id);
        return $ok;
    }

    /**
     * Análisis de suficiencia: por cada departamento con personal y cada
     * categoría con dotación, cuántos bienes hay, cuántos deberían haber
     * según su número de empleados, y el déficit.
     *
     * Los bienes se cuentan por el departamento de su UBICACIÓN (donde
     * están realmente), no por el de su responsable — y se excluye el
     * depósito, porque lo que está ahí no está en uso.
     */
    public static function analisis(): array {
        $db = new Database();
        $db->query("
            WITH personal AS (
                SELECT e.id_departamento AS id_depto, COUNT(*) AS empleados
                  FROM empleados e
                 WHERE e.is_active = TRUE AND e.fecha_egreso IS NULL
                   AND e.id_departamento IS NOT NULL
                 GROUP BY e.id_departamento
            ),
            existencias AS (
                SELECT u.\"departamento _d\" AS id_depto, i.id_categoria, COUNT(*) AS hay
                  FROM inventario i
                  INNER JOIN ubicaciones u ON u.id = i.id_ubicacion
                 WHERE i.is_active = TRUE
                   AND i.estatus NOT IN ('Dado de baja','Extraviado','Robado')
                   AND u.es_deposito = FALSE
                 GROUP BY u.\"departamento _d\", i.id_categoria
            )
            SELECT d.nombre AS departamento,
                   c.nombre AS categoria,
                   p.empleados,
                   dot.unidades_por_empleado,
                   CEIL(p.empleados * dot.unidades_por_empleado) AS deberia_haber,
                   COALESCE(ex.hay, 0) AS hay
              FROM personal p
              INNER JOIN departamentos d ON d.id = p.id_depto
              CROSS JOIN inventario_dotacion dot
              INNER JOIN categorias c ON c.id = dot.id_categoria
              LEFT  JOIN existencias ex ON ex.id_depto = p.id_depto
                                       AND ex.id_categoria = dot.id_categoria
             WHERE dot.is_active = TRUE AND d.is_active = TRUE
             ORDER BY d.nombre, c.nombre");
        $filas = [];
        foreach ($db->resultSet() as $r) {
            $deberia = (int)$r->deberia_haber;
            $hay     = (int)$r->hay;
            $filas[] = [
                'departamento' => $r->departamento,
                'categoria'    => $r->categoria,
                'empleados'    => (int)$r->empleados,
                'ratio'        => (float)$r->unidades_por_empleado,
                'deberia'      => $deberia,
                'hay'          => $hay,
                'deficit'      => max(0, $deberia - $hay),
            ];
        }
        return $filas;
    }
}
