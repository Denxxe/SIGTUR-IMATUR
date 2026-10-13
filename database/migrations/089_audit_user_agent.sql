-- ─────────────────────────────────────────────────────────────────────────────
-- Migración 089 — Dispositivo en la bitácora (audit_logs.user_agent)
--
-- Reportado 2026-10-13: en Docker Desktop (Windows) TODOS los accesos salían con
-- la IP 172.18.0.1. No es un fallo del sistema: Docker reenvía cada conexión
-- del puerto publicado desde su propia puerta de enlace y la IP del cliente se
-- pierde antes de llegar a Apache. Con eso, el reporte de Accesos no permitía
-- distinguir un teléfono de la PC.
--
-- Se guarda el User-Agent del navegador con cada evento; el reporte lo traduce
-- a «Teléfono · Android · Chrome» con Util::dispositivo(). No reemplaza a la IP
-- (en Laragon sigue siendo la real): la complementa. Los registros anteriores
-- quedan con NULL y el reporte muestra «—».
--
-- Idempotente.
-- ─────────────────────────────────────────────────────────────────────────────

ALTER TABLE audit_logs ADD COLUMN IF NOT EXISTS user_agent VARCHAR(255);
COMMENT ON COLUMN audit_logs.user_agent IS 'User-Agent del navegador que originó el evento (mig. 089). Se muestra traducido con Util::dispositivo().';
