<?php
/**
 * DotacionInventario — suficiencia de bienes por departamento (B-63, mig. 067/091).
 *
 * El cliente aclaró que NO es stock de consumibles (no llevan): es saber si
 * **alcanzan** los bienes. Y el criterio es **por departamento**, no por
 * empleado: «cada departamento tendrá una cantidad de bienes» — el aire
 * acondicionado es del espacio, no de la persona (mig. 091; hasta entonces
 * se guardaba `unidades_por_empleado`).
 *
 * `inventario_dotacion` define, por cada par departamento + categoría,
 * cuántas unidades debería haber. El reporte compara eso contra lo que
 * realmente está ubicado en el departamento.
 *
 * Solo se evalúan los pares con dotación definida: un departamento o una
 * categoría sin fila simplemente no aparece en el análisis.
 */
class DotacionInventario extends Model {

    const UNIDADES_MAX = 9999;

    /** Dotaciones definidas, con el nombre de su departamento y su categoría. */
    public static function all() {
        $db = new Database();
        $db->query("SELECT dot.*, c.nombre AS categoria, d.nombre AS departamento
                      FROM inventario_dotacion dot
                      INNER JOIN categorias    c ON c.id = dot.id_categoria
                      INNER JOIN departamentos d ON d.id = dot.id_departamento
                     WHERE dot.is_active = TRUE
                     ORDER BY d.nombre, c.nombre");
        return $db->resultSet();
    }

    /**
     * Crea o actualiza la dotación de un departamento para una categoría (una
     * sola vigente por par: el índice único de la mig. 091 lo garantiza).
     */
    public static function guardar(int $idDepto, int $idCategoria, $unidades, string $obs = '', $user_id = null): bool {
        if ($idDepto <= 0 || $idCategoria <= 0) {
            throw new Exception('Selecciona el departamento y la categoría.');
        }
        $u = filter_var($unidades, FILTER_VALIDATE_INT);
        if ($u === false || $u < 1 || $u > self::UNIDADES_MAX) {
            throw new Exception('Las unidades deben ser un número entero entre 1 y ' . self::UNIDADES_MAX . '.');
        }
        $db = new Database();
        $db->query("SELECT id, unidades, observaciones FROM inventario_dotacion
                     WHERE id_departamento = :d AND id_categoria = :c AND is_active = TRUE LIMIT 1");
        $db->bind(':d', $idDepto);
        $db->bind(':c', $idCategoria);
        $existe = $db->single();

        if ($existe) {
            $db->query("UPDATE inventario_dotacion
                           SET unidades = :u, observaciones = :o,
                               updated_at = CURRENT_TIMESTAMP, updated_by = :uid
                         WHERE id = :id");
            $db->bind(':id', (int)$existe->id);
        } else {
            $db->query("INSERT INTO inventario_dotacion
                            (id_departamento, id_categoria, unidades, observaciones, created_by)
                        VALUES (:d, :c, :u, :o, :uid)");
            $db->bind(':d', $idDepto);
            $db->bind(':c', $idCategoria);
        }
        $db->bind(':u',   $u);
        $db->bind(':o',   trim($obs) ?: null);
        $db->bind(':uid', $user_id);
        $ok = $db->execute();
        self::auditStatic('inventario_dotacion', $existe ? 'UPDATE' : 'INSERT',
            $existe->id ?? null, $existe,
            ['id_departamento' => $idDepto, 'id_categoria' => $idCategoria, 'unidades' => $u], $user_id);
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
     * Análisis de suficiencia: por cada dotación definida, cuántos bienes de
     * esa categoría hay en el departamento contra cuántos debería tener.
     *
     * Los bienes se cuentan por el departamento de su UBICACIÓN (donde
     * están realmente), no por el de su responsable — y se excluye el
     * depósito, porque lo que está ahí no está en uso. El número de
     * empleados se muestra solo como contexto: ya no entra en el cálculo.
     */
    public static function analisis(): array {
        $db = new Database();
        $estBaja = Inventario::sqlEstBaja();   // mig. 076: el texto no se cablea
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
                   AND i.estatus NOT IN ({$estBaja},'Extraviado','Robado')
                   AND u.es_deposito = FALSE
                 GROUP BY u.\"departamento _d\", i.id_categoria
            )
            SELECT d.nombre AS departamento,
                   c.nombre AS categoria,
                   COALESCE(p.empleados, 0) AS empleados,
                   dot.unidades AS deberia_haber,
                   COALESCE(ex.hay, 0) AS hay
              FROM inventario_dotacion dot
              INNER JOIN departamentos d ON d.id = dot.id_departamento
              INNER JOIN categorias    c ON c.id = dot.id_categoria
              LEFT  JOIN personal    p  ON p.id_depto = dot.id_departamento
              LEFT  JOIN existencias ex ON ex.id_depto = dot.id_departamento
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
                'deberia'      => $deberia,
                'hay'          => $hay,
                'deficit'      => max(0, $deberia - $hay),
            ];
        }
        return $filas;
    }
}
