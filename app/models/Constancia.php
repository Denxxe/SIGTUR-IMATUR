<?php
/**
 * Constancia de trabajo generada por RRHH (R-10).
 * Correlativo CONST-NNN/AAAA vía ConfigSistema; se conserva el historial por empleado.
 */
class Constancia extends Model
{
    // Catálogo de tipos de constancia (clave => etiqueta). La clave se guarda en `tipo`.
    const TIPOS = [
        'trabajo'    => 'Constancia de Trabajo',
        'bancaria'   => 'Constancia de Trabajo (Trámite Bancario)',
        'horario'    => 'Constancia de Horario',
        'funciones'  => 'Constancia de Funciones',
        'antiguedad' => 'Constancia de Antigüedad',
        'egreso'     => 'Constancia de Egreso / Cesación de Funciones',
    ];

    /** Etiqueta legible de un tipo (soporta filas antiguas con texto libre). */
    public static function labelTipo($clave) {
        return self::TIPOS[$clave] ?? 'Constancia de Trabajo';
    }

    public static function porEmpleado($idEmpleado) {
        $db = new Database();
        $db->query("SELECT * FROM constancias WHERE id_empleado = :id AND is_active = TRUE ORDER BY fecha_emision DESC");
        $db->bind(':id', $idEmpleado);
        return $db->resultSet();
    }

    public static function find($id) {
        $db = new Database();
        $db->query("SELECT * FROM constancias WHERE id = :id AND is_active = TRUE");
        $db->bind(':id', $id);
        return $db->single();
    }

    /** Crea una constancia con correlativo y devuelve su id (o false). $tipo = clave del catálogo. */
    public static function crear($idEmpleado, $tipo = 'trabajo', $user_id = null) {
        if (empty($idEmpleado)) throw new Exception("Empleado obligatorio.");
        if (!array_key_exists($tipo, self::TIPOS)) $tipo = 'trabajo';
        $numero = 'CONST-' . ConfigSistema::generarNumeroOficio('constancia');

        $db = new Database();
        $db->query("INSERT INTO constancias (id_empleado, numero, tipo, created_by)
                    VALUES (:emp, :num, :tipo, :uid) RETURNING id");
        $db->bind(':emp', (int)$idEmpleado);
        $db->bind(':num', $numero);
        $db->bind(':tipo', $tipo);
        $db->bind(':uid', $user_id);
        $res = $db->single();
        $newId = $res->id ?? null;
        self::auditStatic('constancias', 'INSERT', $newId, null, ['numero' => $numero, 'tipo' => $tipo], $user_id);
        return $newId;
    }

    public static function delete($id, $user_id = null) {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE constancias SET is_active=FALSE, deleted_at=CURRENT_TIMESTAMP, deleted_by=:uid WHERE id=:id");
        $db->bind(':id', $id);
        $db->bind(':uid', $user_id);
        $ok = $db->execute();
        self::auditStatic('constancias', 'DELETE', $id, $previos, null, $user_id);
        return $ok;
    }
}
