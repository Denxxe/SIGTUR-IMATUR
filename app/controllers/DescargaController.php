<?php
/**
 * DescargaController — entrega controlada de documentos privados.
 *
 * Los archivos viven FUERA del web root (storage/uploads/...) y NO son
 * accesibles por URL directa. Se sirven solo a través de estos métodos, que
 * validan el ROL del usuario y resuelven el archivo por el ID del registro
 * (evita path traversal: siempre se usa basename del valor guardado).
 *
 * Accesible para cualquier usuario autenticado (incluido en $accesoSiempre del
 * Router); cada método aplica su propia restricción de rol por recurso.
 */
class DescargaController extends Controller {

    private const MIME = [
        'pdf'  => 'application/pdf',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
    ];

    private function rol(): int { return (int)($_SESSION['user_rol'] ?? 0); }

    private function abort(int $code, string $msg): void {
        http_response_code($code);
        exit($msg);
    }

    /** Recaudo del expediente de un empleado — RRHH / Admin. */
    public function expediente($idDoc = 0) {
        if (!in_array($this->rol(), [1, 2], true)) $this->abort(403, 'Acceso denegado.');
        $db = new Database();
        $db->query("SELECT archivo_url, nombre_original FROM expediente_documentos WHERE id = :id AND is_active = TRUE");
        $db->bind(':id', (int)$idDoc);
        $row = $db->single();
        if (!$row) $this->abort(404, 'Documento no encontrado.');
        $this->stream('expedientes', $row->archivo_url, $row->nombre_original ?? null);
    }

    /** Foto de una persona (carnetización) — RRHH / Turismo / Admin. */
    public function foto($idPersona = 0) {
        if (!in_array($this->rol(), [1, 2, 3], true)) $this->abort(403, 'Acceso denegado.');
        $db = new Database();
        $db->query("SELECT foto_url FROM personas WHERE id = :id AND is_active = TRUE");
        $db->bind(':id', (int)$idPersona);
        $row = $db->single();
        if (!$row || empty($row->foto_url)) $this->abort(404, 'Foto no disponible.');
        $this->stream('fotos', $row->foto_url, null);
    }

    /** Documento de un pasante — Turismo / Admin. */
    public function pasante($idDoc = 0) {
        if (!in_array($this->rol(), [1, 3], true)) $this->abort(403, 'Acceso denegado.');
        $db = new Database();
        $db->query("SELECT archivo_url FROM pasante_documentos WHERE id = :id");
        $db->bind(':id', (int)$idDoc);
        $row = $db->single();
        if (!$row || empty($row->archivo_url)) $this->abort(404, 'Documento no encontrado.');
        $this->stream('pasantes', $row->archivo_url, null);
    }

    /** Documento de respaldo de un bien (factura, actas, oficios) — Inventario / Admin. */
    public function bien($idDoc = 0) {
        if (!in_array($this->rol(), [1, 4], true)) $this->abort(403, 'Acceso denegado.');
        $db = new Database();
        $db->query("SELECT archivo_url, nombre_original FROM inventario_documentos
                     WHERE id = :id AND is_active = TRUE");
        $db->bind(':id', (int)$idDoc);
        $row = $db->single();
        if (!$row) $this->abort(404, 'Documento no encontrado.');
        $this->stream('bienes', $row->archivo_url, $row->nombre_original ?? null);
    }

    /** Formulario BM-1 recibido de la Alcaldía — Inventario / Admin. */
    public function bm1($idConsolidado = 0) {
        if (!in_array($this->rol(), [1, 4], true)) $this->abort(403, 'Acceso denegado.');
        $db = new Database();
        $db->query("SELECT archivo_url, nombre_original FROM inventario_consolidados_bm1
                     WHERE id = :id AND is_active = TRUE");
        $db->bind(':id', (int)$idConsolidado);
        $row = $db->single();
        if (!$row || empty($row->archivo_url)) $this->abort(404, 'Documento no disponible.');
        $this->stream('bienes', $row->archivo_url, $row->nombre_original ?? null);
    }

    /** Foto de un bien — Inventario / Admin. */
    public function fotoBien($idBien = 0) {
        if (!in_array($this->rol(), [1, 4], true)) $this->abort(403, 'Acceso denegado.');
        $db = new Database();
        $db->query("SELECT foto_url FROM inventario WHERE id = :id AND is_active = TRUE");
        $db->bind(':id', (int)$idBien);
        $row = $db->single();
        if (!$row || empty($row->foto_url)) $this->abort(404, 'Foto no disponible.');
        $this->stream('bienes', $row->foto_url, null);
    }

    /**
     * Envía el archivo desde storage/uploads/<sub>/. Resuelve por basename del
     * valor guardado (robusto ante rutas antiguas tipo "/uploads/<sub>/x.pdf").
     */
    private function stream(string $sub, string $archivoUrl, ?string $nombre): void {
        $file = basename((string)$archivoUrl);
        if ($file === '') $this->abort(404, 'Archivo no disponible.');
        $path = dirname(dirname(__DIR__)) . '/storage/uploads/' . $sub . '/' . $file;
        if (!is_file($path)) $this->abort(404, 'Archivo no disponible.');

        $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = self::MIME[$ext] ?? 'application/octet-stream';
        $descarga = str_replace('"', '', $nombre ?: $file);

        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . $descarga . '"');
        header('Content-Length: ' . filesize($path));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
        readfile($path);
        exit;
    }
}
