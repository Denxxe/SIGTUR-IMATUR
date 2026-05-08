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
     * Reinicia automáticamente el correlativo si cambia el año.
     */
    public static function generarNumeroOficio(): string {
        $db = new Database();

        $db->query("SELECT valor FROM configuracion_sistema WHERE clave = 'correlativo_oficio'");
        $corrActual = (int)($db->single()->valor ?? 0);

        $db->query("SELECT valor FROM configuracion_sistema WHERE clave = 'ano_correlativo'");
        $anoActual  = (int)($db->single()->valor ?? date('Y'));
        $anoReal    = (int)date('Y');

        if ($anoActual !== $anoReal) {
            $corrActual = 0;
            $db->query("UPDATE configuracion_sistema SET valor = :ano WHERE clave = 'ano_correlativo'");
            $db->bind(':ano', (string)$anoReal);
            $db->execute();
        }

        $corrNuevo = $corrActual + 1;
        $db->query("UPDATE configuracion_sistema SET valor = :val WHERE clave = 'correlativo_oficio'");
        $db->bind(':val', (string)$corrNuevo);
        $db->execute();

        return str_pad((string)$corrNuevo, 3, '0', STR_PAD_LEFT) . '/' . $anoReal;
    }
}
