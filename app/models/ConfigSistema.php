<?php
class ConfigSistema extends Model {

    public static function getAll(): array {
        $db = new Database();
        $db->query("SELECT clave, valor, descripcion FROM configuracion_sistema ORDER BY id ASC");
        $rows = $db->resultSet();
        $result = [];
        foreach ($rows as $row) {
            $result[$row->clave] = ['valor' => $row->valor ?? '', 'descripcion' => $row->descripcion ?? ''];
        }
        return $result;
    }

    public static function get(string $clave): string {
        $db = new Database();
        $db->query("SELECT valor FROM configuracion_sistema WHERE clave = :clave");
        $db->bind(':clave', $clave);
        $row = $db->single();
        return $row ? ($row->valor ?? '') : '';
    }

    public static function set(string $clave, string $valor, int $userId): bool {
        $db = new Database();
        $db->query("UPDATE configuracion_sistema
                    SET valor = :valor, updated_at = NOW(), updated_by = :uid
                    WHERE clave = :clave");
        $db->bind(':clave', $clave);
        $db->bind(':valor', $valor);
        $db->bind(':uid', $userId);
        return $db->execute();
    }

    /**
     * Genera el siguiente número de oficio (ej: "007/2026").
     * Acepta un parámetro $modulo para usar correlativas independientes por módulo.
     * Reinicia automáticamente el correlativo si cambia el año.
     */
    public static function generarNumeroOficio(string $modulo = 'ruta'): string {
        $claveCorr = 'correlativo_oficio_' . $modulo;
        $claveAnio = 'ano_correlativo_'    . $modulo;
        $anioActual = (int)date('Y');

        $db = new Database();
        $db->beginTransaction();
        try {
            // Leer año guardado con bloqueo para evitar race condition
            $db->query("SELECT valor FROM configuracion_sistema WHERE clave = :clave FOR UPDATE");
            $db->bind(':clave', $claveAnio);
            $rowAnio      = $db->single();
            $anioGuardado = (int)(($rowAnio->valor ?? '') ?: $anioActual);

            if ($anioActual !== $anioGuardado) {
                // Año nuevo: reiniciar correlativo a 0 y actualizar año
                $db->query("UPDATE configuracion_sistema SET valor = '0' WHERE clave = :clave");
                $db->bind(':clave', $claveCorr);
                $db->execute();

                $db->query("UPDATE configuracion_sistema SET valor = :ano WHERE clave = :clave");
                $db->bind(':ano', (string)$anioActual);
                $db->bind(':clave', $claveAnio);
                $db->execute();
            }

            // Incremento atómico: UPDATE + RETURNING en una sola operación
            $db->query("UPDATE configuracion_sistema
                        SET valor = (CAST(valor AS INTEGER) + 1)::TEXT
                        WHERE clave = :clave
                        RETURNING CAST(valor AS INTEGER) AS nuevo");
            $db->bind(':clave', $claveCorr);
            $row = $db->single();
            $correlativo = (int)($row->nuevo ?? 1);

            $db->endTransaction();
        } catch (\Exception $e) {
            $db->cancelTransaction();
            throw $e;
        }

        return str_pad($correlativo, 3, '0', STR_PAD_LEFT) . '/' . $anioActual;
    }
}
