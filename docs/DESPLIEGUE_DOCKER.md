# Montar SIGTUR-IMATUR con Docker (Windows)

Guía para levantar el sistema en una PC con Windows y entrar desde **otras computadoras y
teléfonos de la misma red**, y opcionalmente **desde internet**.

> ⚠️ Los archivos de Docker (`docker-compose.yml`, `docker/`, `.env.example`) se escribieron el
> 2026-10-03 y **todavía no se han levantado en una máquina con Docker**. La configuración PHP y la
> selección de URL sí se probaron; la construcción de la imagen no. Si algo falla en el paso 4,
> el error que muestre la consola dice exactamente qué.

**Qué se levanta:** tres contenedores.

| Servicio | Qué hace |
|---|---|
| `db` | PostgreSQL 17. La primera vez **crea la base sola** desde `database/schema_consolidado.sql`. No queda expuesta fuera de Docker |
| `app` | PHP 8.1 + Apache con el sistema, en el puerto **8080** |
| `tareas` | Lo que en Laragon hace el Programador de tareas: estados de talleres cada 10 min y respaldo diario |

---

## 1. Instalar Docker Desktop (una sola vez)

1. Abrir **PowerShell como administrador** y ejecutar `wsl --install`. Reiniciar.
2. Descargar **Docker Desktop** de <https://www.docker.com/products/docker-desktop/> e instalarlo
   (dejar marcada la opción *Use WSL 2*). Reiniciar.
3. Abrir Docker Desktop y esperar a que diga *Engine running*.
4. En *Settings → General*, marcar **Start Docker Desktop when you sign in**, para que el sistema
   vuelva solo después de reiniciar la PC.

Comprobar en una consola: `docker --version` y `docker compose version`.

> Laragon puede seguir instalado: Docker usa el puerto **8080** y su propio PostgreSQL interno, así
> que no choca con el Apache (80) ni con el PostgreSQL (5432) de Laragon.

## 2. Tener el código

Si ya está en `C:\laragon\www\SIGTUR-IMATUR`, se usa ese. En otra PC:

```powershell
git clone https://github.com/Denxxe/SIGTUR-IMATUR.git
cd SIGTUR-IMATUR
```

## 3. Crear el archivo `.env`

```powershell
copy .env.example .env
notepad .env
```

Cambiar como mínimo:

- **`DB_PASS`** — una contraseña larga. Es la de la base de datos.
- **`APP_URLS`** — las direcciones desde las que se va a entrar, separadas por coma. Para saber la IP
  de esta PC: `ipconfig` → *Dirección IPv4* (algo como `192.168.1.10`). Ejemplo:
  `APP_URLS=http://localhost:8080,http://192.168.1.10:8080`
- **`SMTP_*`** — opcional, para que funcione «¿Olvidaste tu contraseña?».

`.env` no se sube a git (tiene contraseñas).

## 4. Levantar el sistema

```powershell
docker compose up -d --build
```

La primera vez tarda varios minutos (descarga PostgreSQL y PHP y crea la base). Ver que los tres
estén arriba:

```powershell
docker compose ps
```

Abrir **<http://localhost:8080>** y entrar con `admin` / `Sigtur2026`. **Cambiar esa contraseña en el
primer ingreso** (Perfil → Cambiar contraseña): está publicada en el repositorio.

## 5. Entrar desde el teléfono u otra computadora (misma red)

1. **Abrir el puerto en el firewall de Windows** (PowerShell como administrador):

   ```powershell
   New-NetFirewallRule -DisplayName "SIGTUR 8080" -Direction Inbound -Protocol TCP -LocalPort 8080 -Action Allow -Profile Private
   ```

2. Confirmar que la red Wi-Fi de esta PC está como **Privada** (Configuración → Red e Internet →
   la red → *Tipo de perfil de red: Privada*). En una red *Pública* Windows bloquea la conexión.
3. Desde el teléfono, **conectado al mismo Wi-Fi**, abrir `http://192.168.1.10:8080` (la IP del paso 3).

Si no abre: la IP de la PC cambió (fijarla en el router o en Windows) o falta que esté en `APP_URLS`.
Después de cambiar `.env`: `docker compose up -d`.

## 6. Entrar desde internet (opcional)

El sistema está pensado para la red interna. Antes de exponerlo: contraseñas fuertes para todos los
usuarios y `admin` de arranque desactivado. Dos caminos, de más a menos seguro:

### Opción A — Tailscale (recomendada): solo tus dispositivos

Una red privada entre tus equipos; nadie más ve el sistema y no hay que tocar el router.

1. Instalar **Tailscale** (<https://tailscale.com/download>) en esta PC y en el teléfono, con la
   misma cuenta.
2. En esta PC, ver su IP de Tailscale: `tailscale ip -4` (algo como `100.101.102.103`).
3. Agregarla a `APP_URLS` → `http://100.101.102.103:8080`, y `docker compose up -d`.
4. Con Tailscale activo en el teléfono, abrir esa dirección **desde cualquier lugar**.

### Opción B — Cloudflare Tunnel: una dirección pública con HTTPS

Cualquiera con el enlace llega a la pantalla de login. No hace falta abrir puertos del router.

- **Para una prueba rápida** (la dirección cambia cada vez que se arranca):

  ```powershell
  winget install Cloudflare.cloudflared
  cloudflared tunnel --url http://localhost:8080
  ```

  Muestra una dirección `https://algo-al-azar.trycloudflare.com`. Agregarla a `APP_URLS` y
  `docker compose up -d`.
- **Para algo permanente** hace falta un dominio propio en Cloudflare y un *tunnel* con nombre (ver
  la documentación de Cloudflare Zero Trust).

> **Abrir un puerto en el router** (port forwarding) no se recomienda: expone la PC directamente, y
> con muchos proveedores ni siquiera funciona porque la conexión no tiene IP pública propia (CGNAT).

## 7. Operación diaria

| Para… | Comando |
|---|---|
| Ver si está arriba | `docker compose ps` |
| Ver errores | `docker compose logs app --tail 100` |
| Ver las tareas programadas | `docker compose logs tareas --tail 50` |
| Detener | `docker compose stop` |
| Arrancar | `docker compose start` |

**Dónde quedan los datos:**

- La base de datos: en el volumen de Docker `sigtur_bd`.
- Documentos subidos: `docker-data\uploads\`.
- Respaldos diarios: **`docker-data\backups\`**. Copiarlos con frecuencia a **otro disco o equipo**:
  si se daña esta PC, se pierde todo lo que solo esté aquí.

> 🔴 **`docker compose down -v` BORRA LA BASE DE DATOS** (la `-v` elimina el volumen). Para detener,
> usar `stop` o `down` **sin** `-v`.

**Respaldo manual inmediato:** `docker compose exec tareas php cron/respaldo_bd.php`

**Restaurar un respaldo** (reemplaza los datos actuales):

```powershell
docker compose exec -T db psql -U postgres -d postgres -c "DROP DATABASE \"SIGTUR-IMATUR\" WITH (FORCE);" -c "CREATE DATABASE \"SIGTUR-IMATUR\";"
docker compose cp docker-data\backups\sigtur_AAAA-MM-DD_HHMMSS.sql db:/tmp/restaurar.sql
docker compose exec db psql -U postgres -d SIGTUR-IMATUR -f /tmp/restaurar.sql
```

> Se copia el archivo al contenedor en vez de pasarlo con `Get-Content ... |`: Windows PowerShell
> reenvía el texto en ASCII y estropea las tildes y la ñ.

## 8. Actualizar a una versión nueva

```powershell
git pull
docker compose up -d --build
```

⚠️ La base **no** se recrea al actualizar (solo se crea la primera vez). Si la versión nueva trae
migraciones en `database/migrations/`, aplicar **solo las nuevas**, en orden (son idempotentes):

```powershell
docker compose cp database\migrations\084_ejemplo.sql db:/tmp/m.sql
docker compose exec db psql -U postgres -d SIGTUR-IMATUR -v ON_ERROR_STOP=1 -f /tmp/m.sql
```
