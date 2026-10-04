# Montar SIGTUR-IMATUR con Docker (Windows)

Guía para levantar el sistema en una PC con Windows y entrar desde **otras computadoras y
teléfonos de la misma red**, y opcionalmente **desde internet**.

> ✅ **Probado el 2026-10-03** (Windows 11, Docker 29.8, Compose v5.5): la imagen construye, la base
> se crea sola con las 69 tablas (migraciones 001-083), el login `admin` entra al panel, se accede
> por `localhost` y por la IP de la red local, Apache devuelve 403 a `config.php`, `app/` y
> `database/`, la tarea de estados corre y un respaldo manual genera el `.sql` en
> `docker-data/backups/`.
>
> Si se ejecutan comandos `docker compose exec … /var/www/…` desde **Git Bash**, anteponer
> `MSYS_NO_PATHCONV=1`: Git Bash reescribe las rutas que empiezan por `/`. En PowerShell o CMD no hace falta.

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

**Después de reiniciar la PC** no hay que hacer nada si Docker Desktop tiene marcado *Start Docker
Desktop when you sign in* (paso 1.4): al iniciar sesión en Windows arranca Docker, y Docker levanta
solos los tres contenedores (`restart: unless-stopped`). Tarda 1-2 minutos. Sin esa opción, abrir
Docker Desktop a mano y esperar a *Engine running*; no hace falta ningún comando. Si se detuvo con
`docker compose stop`, en cambio, **no** vuelve solo: hay que ejecutar `docker compose start` desde
la carpeta del proyecto.

> Docker arranca al **iniciar sesión**, no al encender: la PC tiene que quedar con la sesión de
> Windows abierta (puede estar bloqueada con Win+L).

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

## 9. Llevar el sistema a otra computadora

**Instalación nueva (base vacía):** repetir los pasos 1 a 5 en la otra PC. Cambian dos cosas: su IP
(`ipconfig`), que va en `APP_URLS`, y la regla del firewall, que se crea de nuevo en esa PC.

**Mudar el sistema con sus datos** (usuarios, empleados, documentos). Probado el 2026-10-03:

1. En la PC **vieja**, sacar un respaldo al momento:
   `docker compose exec tareas php cron/respaldo_bd.php`
2. Copiar a un pendrive **el último `.sql` de `docker-data\backups\`** y la carpeta completa
   **`docker-data\uploads\`** (fotos y documentos subidos: no van dentro del `.sql`).
3. En la PC **nueva**, pasos 1 a 4. Se crea una base vacía con el `admin` de arranque.
4. Pegar la carpeta `uploads` dentro de `docker-data\` de la PC nueva (reemplazando la vacía).
5. Restaurar el respaldo con los tres comandos de **Restaurar un respaldo** (sección 7). La base
   vacía se reemplaza por la de la PC vieja, con sus usuarios y contraseñas.
6. Ajustar `APP_URLS` en el `.env` con la IP de la PC nueva y ejecutar `docker compose up -d`.
7. Apagar el sistema en la PC vieja (`docker compose stop`): dos copias vivas se separan en cuanto
   alguien registre algo en una de ellas.

> **Fijar la IP del servidor.** El router puede asignarle otra IP al reiniciar y los teléfonos
> dejarían de encontrarlo. En la página del router (suele ser `http://192.168.1.1` o la *Puerta de
> enlace* que muestra `ipconfig`) buscar *DHCP → Reserva de IP / Static lease* y reservar la IP
> actual para esa PC.

## 10. Entrar con un nombre en vez de IP y puerto

Cada dirección nueva hay que agregarla a `APP_URLS` (exacta: con `http`/`https` y con el puerto si
lo lleva) y ejecutar `docker compose up -d`. Si no está en la lista, el sistema funciona pero los
enlaces vuelven a la primera dirección de la lista.

**a) Quitar el `:8080`.** En el `.env`: `PUERTO=80` y `APP_URLS=http://192.168.100.144` (sin
puerto). Se entra con `http://192.168.100.144`. Choca con el Apache de Laragon, que también usa el
80: sirve en la PC que solo hace de servidor, no en la de desarrollo. La regla del firewall pasa a
`-LocalPort 80`.

**b) Nombre gratis, con HTTPS, desde casa y desde fuera: Tailscale.** Con Tailscale instalado (sección
6, opción A), en la consola de administración de Tailscale activar *DNS → MagicDNS* y *HTTPS
Certificates*. Luego, en la PC servidor:

```powershell
tailscale serve --bg 8080
```

Muestra una dirección como `https://nombre-pc.tailXXXX.ts.net`: agregarla a `APP_URLS`. Solo la
abren los equipos con Tailscale y la misma cuenta.

**c) Dominio propio público (`https://sigtur.midominio.com`).** Comprar un dominio (unos 10 USD al
año), ponerlo en Cloudflare y crear un *tunnel* con nombre que apunte a `http://localhost:8080`.
Cualquiera con el enlace llega al login: antes, contraseñas fuertes para todos (sección 6).

> Un nombre **solo dentro de la red**, sin Tailscale ni dominio, depende de que el router permita
> registrar nombres (*DNS local / Static DNS*). La mayoría de los routers que dan los proveedores
> no lo permiten, y el archivo `hosts` sirve en computadoras pero no en teléfonos.
