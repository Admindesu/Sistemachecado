# Instalación y mantenimiento

Hay dos formas de instalar el sistema: **Docker** (recomendada para servidores nuevos) y
**XAMPP en Windows** (la que se usa hoy en el servidor del IQTP).

- [Requisitos](#requisitos)
- [Docker](#docker)
- [XAMPP en Windows](#xampp-en-windows)
- [Variables de configuración](#variables-de-configuración)
- [Migraciones de base de datos](#migraciones-de-base-de-datos)
- [Respaldos](#respaldos)
- [Problemas frecuentes](#problemas-frecuentes)

## Requisitos

| Componente | Versión |
|---|---|
| PHP | 8.0 o superior, con `mysqli` y `curl` (curl solo para el terminal Hikvision) |
| Base de datos | MariaDB 10.2+ (las migraciones usan `ADD COLUMN IF NOT EXISTS`) |
| Servidor web | Apache 2.4 |
| Red | El servidor y el terminal Hikvision en la misma red local |

No se necesita Composer: las librerías (FPDF/FPDI para PDF) están en `libs/` y el Excel se genera
con `modelo/xlsx.php`, que no depende de ninguna extensión adicional.

## Docker

```bash
cp .env.example .env      # cambiar contraseñas, token y datos del terminal
docker compose up -d --build
```

| Servicio | Qué es | Dirección |
|---|---|---|
| `app` | PHP 8.2 + Apache con la aplicación | http://localhost:8080 (`APP_PORT`) |
| `db` | MariaDB 10.11 | interno |
| `worker` | Sincroniza el terminal cada `HIK_INTERVALO_SEG` segundos | — |
| `phpmyadmin` | Opcional: `docker compose --profile tools up -d` | http://localhost:8081 (`PMA_PORT`) |

- La primera vez se crea la base `sis-asistencia` con `sql/sis-asistencia.sql` y las tres
  migraciones de `sql/migraciones/`. Esto solo ocurre con el volumen vacío; para empezar de cero:
  `docker compose down -v` (**borra todos los datos**).
- `docker-compose.yml` monta el código como volumen para desarrollo. En producción quita la línea
  `- ./:/var/www/html` del servicio `app` para usar el código copiado en la imagen.
- Con Docker Desktop en Windows, la IP de origen del terminal se ve como la del gateway: deja
  `HIK_IPS_PERMITIDAS` vacío y confía en el token.

## XAMPP en Windows

### 1. Código

Clona el repositorio en cualquier carpeta (no hace falta que esté en `htdocs`), por ejemplo
`C:\Users\<usuario>\Desktop\asistencia\Sistemachecado`.

### 2. Configuración de Apache

Crea `C:\xampp\apache\conf\extra\httpd-checador.conf` (fuera del repositorio, porque lleva
contraseñas):

```apache
# php_curl necesita estas DLL; sin ellas aparece "Call to undefined function curl_init()"
LoadFile "C:/xampp/php/brotlicommon.dll"
LoadFile "C:/xampp/php/brotlidec.dll"

Alias /checador "C:/ruta/al/Sistemachecado"
<Directory "C:/ruta/al/Sistemachecado">
    Options -Indexes
    AllowOverride All
    Require all granted
</Directory>

# No exponer archivos internos
<DirectoryMatch "C:/ruta/al/Sistemachecado/(\.git|sql|cron|docker)">
    Require all denied
</DirectoryMatch>
<FilesMatch "^\.env">
    Require all denied
</FilesMatch>

SetEnv APP_TZ America/Cancun
SetEnv APP_SUBSECRETARIAS 0
SetEnv HIK_HOST 192.168.0.209
SetEnv HIK_USUARIO admin
SetEnv HIK_PASSWORD "contraseña-del-terminal"
SetEnv HIK_PUSH_TOKEN token-largo-y-aleatorio
SetEnv HIK_MINORS_VALIDOS 1,38,75,153
SetEnv HIK_DIAS_INICIALES 7
```

Agrega al final de `C:\xampp\apache\conf\httpd.conf`:

```apache
Include "conf/extra/httpd-checador.conf"
```

Activa `extension=curl` en `C:\xampp\php\php.ini` (viene activa en XAMPP reciente) y reinicia
Apache desde el panel de XAMPP (**Stop** y luego **Start**).

Para generar el token: `php -r "echo bin2hex(random_bytes(32));"`.

### 3. Base de datos

En phpMyAdmin (http://localhost/phpmyadmin):

1. Crea la base `sis-asistencia` (cotejamiento `utf8mb4_general_ci`).
2. Importa `sql/sis-asistencia.sql`.
3. Ejecuta en orden las [migraciones](#migraciones-de-base-de-datos) (001 a 004).

La conexión usa por defecto `localhost`, usuario `root` sin contraseña. Para otros datos define
`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` y `DB_PASSWORD` con `SetEnv`.

Entra a http://localhost/checador/vista/login/login.php con `juanmanuel` / `123` y cambia la
contraseña (menú del usuario → *Cambiar contraseña*).

### 4. Sincronización periódica con el terminal

El terminal envía cada marcaje en tiempo real, pero si el servidor estuvo apagado esos marcajes
se pierden. `cron/sincronizar_hikvision.php` los recupera consultando al terminal. En XAMPP se
programa con el **Programador de tareas** de Windows.

El script corre con PHP de línea de comandos, que **no** lee los `SetEnv` de Apache. Crea un
archivo `.bat` fuera del repositorio, por ejemplo `C:\xampp\checador-sincronizar.bat`:

```bat
@echo off
set APP_TZ=America/Cancun
set HIK_HOST=192.168.0.209
set HIK_USUARIO=admin
set HIK_PASSWORD=contraseña-del-terminal
set HIK_MINORS_VALIDOS=1,38,75,153
set HIK_DIAS_INICIALES=7
rem Copiar también HIK_AJUSTE_HASTA, HIK_AJUSTE_HORAS e HIK_IGNORAR_ANTES si se usan
"C:\xampp\php\php.exe" "C:\ruta\al\Sistemachecado\cron\sincronizar_hikvision.php" >> "C:\xampp\checador-sincronizar.log" 2>&1
```

En el Programador de tareas: *Crear tarea* → desencadenador diario, repetir cada 5 minutos
indefinidamente → acción: el `.bat` → marcar *Ejecutar tanto si el usuario inició sesión como si no*.

Para recuperar un periodo concreto: `php cron\sincronizar_hikvision.php --desde=2026-11-01`.

### 5. Red

- Reserva en el router una **IP fija** para el servidor; la URL de HTTP Listening del terminal
  apunta a esa IP.
- Abre el puerto 80 (o el de Apache) en el Firewall de Windows para la red local.

## Variables de configuración

| Variable | Por defecto | Descripción |
|---|---|---|
| `APP_TZ` | `America/Cancun` | Zona horaria de PHP; debe coincidir con la del terminal. |
| `APP_SUBSECRETARIAS` | `0` | `1` muestra las subsecretarías en formularios, listas y organigrama. |
| `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` | `localhost`, `3306`, `sis-asistencia`, `root`, vacío | Conexión a la base. |
| `HIK_HOST` | — | IP del terminal. |
| `HIK_USUARIO`, `HIK_PASSWORD` | `admin`, — | Credenciales del terminal (autenticación digest). |
| `HIK_PUSH_TOKEN` | — | Token secreto de la URL de HTTP Listening. Sin token se rechazan todos los envíos. |
| `HIK_IPS_PERMITIDAS` | vacío | IPs autorizadas a enviar eventos, separadas por coma (vacío = cualquiera con token). |
| `HIK_MINORS_VALIDOS` | `1,38,75,153` | Subtipos de evento que cuentan como marcaje (ver [HIKVISION.md](HIKVISION.md)). |
| `HIK_DIAS_INICIALES` | `7` | Días hacia atrás que descarga la primera sincronización. |
| `HIK_INTERVALO_SEG` | `300` | Solo Docker: cada cuánto sincroniza el `worker`. |
| `HIK_AJUSTE_HASTA`, `HIK_AJUSTE_HORAS` | vacío, `0` | Corrige eventos registrados con el reloj del terminal mal puesto (ver [HIKVISION.md](HIKVISION.md#reloj-desfasado)). |
| `HIK_IGNORAR_ANTES` | vacío | Descarta eventos anteriores a esta fecha (p. ej. reloj reiniciado a 2014). |

En Docker van en `.env`; en XAMPP, como `SetEnv` en `httpd-checador.conf` (y en el `.bat` de la
sincronización).

## Migraciones de base de datos

Se aplican **una vez, en orden**, sobre una base creada con `sql/sis-asistencia.sql`. Se pueden
volver a ejecutar sin daño.

| Archivo | Qué hace |
|---|---|
| `001_hikvision.sql` | Tabla `marcaje_dispositivo` y columnas `origen` e `id_marcaje` en `asistencia`. |
| `002_dias_inhabiles_justificaciones.sql` | Tablas `dia_inhabil` y `justificacion`. |
| `003_subsecretaria_opcional.sql` | Permite empleados sin subsecretaría. |
| `004_baja_empleado.sql` | Columnas `activo`, `fecha_baja` y `motivo_baja` en `empleado` (bajas de personal). **Obligatoria**: el login y el checado la necesitan. |

Docker las aplica solas al crear la base.

## Respaldos

Respaldo completo desde la línea de comandos de XAMPP:

```bat
C:\xampp\mysql\bin\mysqldump -uroot sis-asistencia > respaldo_AAAAMMDD.sql
```

Restaurar: `C:\xampp\mysql\bin\mysql -uroot sis-asistencia < respaldo_AAAAMMDD.sql`.

Haz un respaldo antes de actualizar el sistema, aplicar migraciones o hacer cambios masivos.
Las tablas con datos que no se pueden reconstruir son `empleado`, `asistencia`, `justificacion`,
`dia_inhabil` y los catálogos. `marcaje_dispositivo` se puede volver a descargar del terminal
(guarda unos cuantos meses de eventos).

## Problemas frecuentes

| Síntoma | Causa y solución |
|---|---|
| `Call to undefined function curl_init()` | Falta `extension=curl` o las líneas `LoadFile` de las DLL brotli en la configuración de Apache. |
| Las variables `SetEnv` no se aplican | Apache no se reinició, o se arrancó fuera del panel de XAMPP con otra configuración. Detenerlo y arrancarlo desde el panel. |
| "Token inválido" en el log de Apache | La URL de HTTP Listening del terminal no coincide con `HIK_PUSH_TOKEN`. |
| Los marcajes llegan con la hora mal | Zona horaria o NTP del terminal (ver [HIKVISION.md](HIKVISION.md)). |
| Imágenes que no cargan en Linux/Docker | Linux distingue mayúsculas: `logo.PNG` ≠ `logo.png`. |
