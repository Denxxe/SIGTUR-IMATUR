<?php
/**
 * PasswordReset: tokens de un solo uso para recuperación de contraseña por
 * correo. Solo se persiste el HASH (sha256) del token — el token real solo
 * viaja por el enlace del correo, nunca queda en claro en la BD.
 */
class PasswordReset extends Model {
    const TTL_MINUTOS       = 30;
    const COOLDOWN_SEGUNDOS = 60;

    /**
     * Genera y guarda un token nuevo para el usuario. Devuelve el token en
     * claro (para armar el enlace del correo) o null si está en cooldown
     * (evita reenviar/spamear la bandeja si el usuario reintenta el formulario).
     */
    public static function generar(int $idUsuario, ?string $ip = null): ?string {
        $db = new Database();
        $db->query("SELECT created_at FROM password_resets WHERE id_usuario = :id ORDER BY created_at DESC LIMIT 1");
        $db->bind(':id', $idUsuario);
        $ultimo = $db->single();
        if ($ultimo && (time() - strtotime($ultimo->created_at)) < self::COOLDOWN_SEGUNDOS) {
            return null;
        }

        $token = bin2hex(random_bytes(32));
        $db->query("INSERT INTO password_resets (id_usuario, token_hash, expires_at, requested_ip)
                    VALUES (:id, :hash, NOW() + (:ttl || ' minutes')::INTERVAL, :ip)");
        $db->bind(':id', $idUsuario);
        $db->bind(':hash', hash('sha256', $token));
        $db->bind(':ttl', self::TTL_MINUTOS);
        $db->bind(':ip', $ip);
        $db->execute();
        return $token;
    }

    /** Busca el token vigente (no usado, no expirado). Devuelve la fila o null. */
    public static function validar(string $token) {
        $db = new Database();
        $db->query("SELECT * FROM password_resets
                    WHERE token_hash = :hash AND used_at IS NULL AND expires_at > NOW()");
        $db->bind(':hash', hash('sha256', $token));
        return $db->single();
    }

    /** Marca como usado este token y cualquier otro pendiente del mismo usuario. */
    public static function marcarUsado(int $idUsuario): void {
        $db = new Database();
        $db->query("UPDATE password_resets SET used_at = NOW() WHERE id_usuario = :id AND used_at IS NULL");
        $db->bind(':id', $idUsuario);
        $db->execute();
    }
}
