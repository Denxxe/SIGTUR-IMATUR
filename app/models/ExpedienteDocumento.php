<?php
/**
 * Documentos (recaudos) del expediente de un empleado (R-5).
 * Modelo híbrido: archivo subido + checklist con detección de faltantes (D-RH22).
 */
class ExpedienteDocumento extends Model
{
    /**
     * Catálogo de recaudos del expediente.
     * clave => [etiqueta, obligatorio]. La clave se usa en el nombre del archivo.
     */
    const RECAUDOS = [
        'CV'              => ['Currículum (CV)', true],
        'Cedula'          => ['Copia de cédula ampliada y centrada', true],
        'Partida'         => ['Copia de la partida de nacimiento', true],
        'Titulo'          => ['Copia del título (Bachiller/Profesional)', true],
        'FondoNegro'      => ['Fondo negro del título', true],
        'RIF'             => ['RIF', true],
        'RefBancaria'     => ['Referencia bancaria', true],
        'CargaFamiliar'   => ['Recaudos de carga familiar', false],
        'FichaTecnica'    => ['Ficha Técnica del Trabajador', false],
        'Discapacidad'    => ['Documentación estudiante / discapacidad', false],
        'Otro'            => ['Otro documento', false],
    ];

    public static function porEmpleado($idEmpleado) {
        $db = new Database();
        $db->query("SELECT * FROM expediente_documentos WHERE id_empleado = :id AND is_active = TRUE ORDER BY created_at DESC");
        $db->bind(':id', $idEmpleado);
        return $db->resultSet();
    }

    public static function find($id) {
        $db = new Database();
        $db->query("SELECT * FROM expediente_documentos WHERE id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    /**
     * Estado del checklist: por cada recaudo del catálogo, indica si está entregado
     * (y con qué documento). Devuelve además el conteo de faltantes obligatorios.
     */
    public static function recaudosEstado($idEmpleado): array {
        $docs = self::porEmpleado($idEmpleado);
        $porTipo = [];
        foreach ($docs as $d) {
            $porTipo[$d->tipo_documento][] = $d;
        }
        $items = [];
        $faltanObligatorios = 0;
        foreach (self::RECAUDOS as $clave => [$label, $obligatorio]) {
            $entregado = !empty($porTipo[$clave]);
            if ($obligatorio && !$entregado) $faltanObligatorios++;
            $items[] = [
                'clave'       => $clave,
                'label'       => $label,
                'obligatorio' => $obligatorio,
                'entregado'   => $entregado,
                'documentos'  => $porTipo[$clave] ?? [],
            ];
        }
        return ['items' => $items, 'faltan_obligatorios' => $faltanObligatorios];
    }

    /** Claves de recaudos marcados como obligatorios en el catálogo. */
    public static function clavesObligatorias(): array {
        $obl = [];
        foreach (self::RECAUDOS as $clave => [$label, $obligatorio]) {
            if ($obligatorio) $obl[] = $clave;
        }
        return $obl;
    }

    /**
     * Faltantes de recaudos OBLIGATORIOS por empleado, en UNA sola consulta
     * (evita el N+1 de llamar recaudosEstado() por cada empleado).
     * Devuelve [id_empleado => nº de obligatorios faltantes] para el personal
     * activo no egresado (mismo universo que Empleado::all()).
     */
    public static function faltantesObligatorios(): array {
        $obl   = self::clavesObligatorias();
        $total = count($obl);
        $db = new Database();
        if ($total === 0) {
            $db->query("SELECT e.id FROM empleados e INNER JOIN personas p ON e.id_persona = p.id
                        WHERE e.is_active = TRUE AND p.is_active = TRUE AND e.fecha_egreso IS NULL");
            $out = [];
            foreach ($db->resultSet() as $r) $out[(int)$r->id] = 0;
            return $out;
        }
        $ph = [];
        foreach ($obl as $i => $c) $ph[":o$i"] = $c;
        $inList = implode(',', array_keys($ph));
        $db->query("SELECT e.id AS id_empleado,
                           COUNT(DISTINCT ed.tipo_documento) AS entregados
                    FROM empleados e
                    INNER JOIN personas p ON e.id_persona = p.id
                    LEFT JOIN expediente_documentos ed
                           ON ed.id_empleado = e.id AND ed.is_active = TRUE
                          AND ed.tipo_documento IN ($inList)
                    WHERE e.is_active = TRUE AND p.is_active = TRUE AND e.fecha_egreso IS NULL
                    GROUP BY e.id");
        foreach ($ph as $k => $v) $db->bind($k, $v);
        $out = [];
        foreach ($db->resultSet() as $r) {
            $out[(int)$r->id_empleado] = max(0, $total - (int)$r->entregados);
        }
        return $out;
    }

    /**
     * Recaudos entregados por empleado: [id_empleado => [tipo_documento => true]],
     * en UNA sola consulta. Útil para construir la lista de faltantes sin N+1.
     */
    public static function entregadosPorEmpleado(): array {
        $db = new Database();
        $db->query("SELECT id_empleado, tipo_documento FROM expediente_documentos WHERE is_active = TRUE");
        $out = [];
        foreach ($db->resultSet() as $r) {
            $out[(int)$r->id_empleado][$r->tipo_documento] = true;
        }
        return $out;
    }

    public static function save(array $data, $user_id = null) {
        $db = new Database();
        $db->query("INSERT INTO expediente_documentos (id_empleado, tipo_documento, archivo_url, nombre_original, observaciones, created_by)
                    VALUES (:emp, :tipo, :url, :nombre, :obs, :uid) RETURNING id");
        $db->bind(':emp', (int)$data['id_empleado']);
        $db->bind(':tipo', $data['tipo_documento']);
        $db->bind(':url', $data['archivo_url']);
        $db->bind(':nombre', $data['nombre_original'] ?? null);
        $db->bind(':obs', !empty($data['observaciones']) ? trim($data['observaciones']) : null);
        $db->bind(':uid', $user_id);
        $res = $db->single();
        $newId = $res->id ?? null;
        self::auditStatic('expediente_documentos', 'INSERT', $newId, null, $data, $user_id);
        return (bool)$res;
    }

    public static function delete($id, $user_id = null) {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE expediente_documentos SET is_active=FALSE, deleted_at=CURRENT_TIMESTAMP, deleted_by=:uid WHERE id=:id");
        $db->bind(':id', $id);
        $db->bind(':uid', $user_id);
        $ok = $db->execute();
        self::auditStatic('expediente_documentos', 'DELETE', $id, $previos, null, $user_id);
        return $ok;
    }
}
