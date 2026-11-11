# Notas para continuar (rama feature/hikvision)

Última actualización: 2026-11-11. Resumen de lo hecho y lo pendiente para retomar el trabajo
en otro equipo. Al iniciar Claude Code en el otro equipo basta con pedirle: *"lee docs/CONTINUAR.md
y sigamos"*.

## Estado de la rama

`feature/hikvision` (subida a GitHub, **sin fusionar en `main`**, sin pull request) contiene:

- Integración con el terminal Hikvision DS-K1T344MBFWX-E1 por **HTTP Listening**
  (`api/hikvision/evento.php?token=...`) y sincronización de respaldo por ISAPI
  (`cron/sincronizar_hikvision.php`). Migración `sql/migraciones/001_hikvision.sql`.
- Importación de usuarios del terminal (`vista/importar_hikvision.php`).
- Marcajes válidos: subtipos 1 (tarjeta), 38 (huella), 75 (rostro), **153 (rostro + huella)**.
- Corrección del reloj desfasado del terminal: eventos con hora del dispositivo anterior a
  `HIK_AJUSTE_HASTA` se recorren `HIK_AJUSTE_HORAS` (+13 h); los anteriores a `HIK_IGNORAR_ANTES`
  (fechas de 2014) se descartan. El terminal ya tiene NTP y hora correcta.
- Subsecretaría opcional (migración `003_subsecretaria_opcional.sql`) y oculta salvo
  `APP_SUBSECRETARIAS=1` (este centro de trabajo no tiene subsecretarías).
- Horarios con tolerancia/retardo sin tope de 60 min (validados con `validar_tolerancias()`),
  capturados en minutos con resumen de franjas en el formulario.
- Fusionado `feature/dashboard`: dashboard, días inhábiles y justificaciones
  (migración `002_dias_inhabiles_justificaciones.sql`).

## Instalación en el servidor (equipo con XAMPP)

- Alias Apache `/checador` en `C:\xampp\apache\conf\extra\httpd-checador.conf` (incluido desde
  `httpd.conf`). Ahí están las variables `SetEnv` (APP_TZ, HIK_HOST, HIK_USUARIO, HIK_PASSWORD,
  HIK_PUSH_TOKEN, HIK_MINORS_VALIDOS, HIK_AJUSTE_*, HIK_IGNORAR_ANTES). **La contraseña y el token
  no están en el repositorio**; ver ese archivo en el servidor o `.env.example` para los nombres.
- Ese archivo empieza con `LoadFile` de `brotlicommon.dll` y `brotlidec.dll`: sin ellos Apache no
  carga `php_curl` en Windows (`curl_init()` indefinida).
- Migraciones 001, 002 y 003 ya aplicadas en la base local `sis-asistencia`.
- Terminal: 192.168.0.209. Servidor: 192.168.0.207 (Wi-Fi, DHCP).
  HTTP Listening apunta a `http://192.168.0.207/checador/api/hikvision/evento.php?token=...`.

## Datos

- 37 empleados importados del terminal (usuario = número de empleado) + `juanmanuel` (admin de prueba).
  Se borraron los 23 usuarios de prueba; respaldo en
  `Desktop\asistencia\respaldo_antes_borrar_pruebas_20261111.sql` (equipo servidor).
- Cargo y dirección de los importados aún son provisionales.
- Horario Default: 09:00–17:00, tolerancia 10, retardo 20 (falta después de 9:20).
- Horario Matutino (limpieza): 07:40–17:00, tolerancia 90 (hasta 9:10), retardo 100 (hasta 9:20).
  Asignado a Manuel Jesús Cárdenas Domínguez (40719) y Alfredo Koyoc Canul (40728).
- Los marcajes de solo tarde del 20 y 30 de octubre se dejan así (instalación del terminal).

## Pendientes

1. Confirmar las reglas del Horario Default (personal entra 8:30–9:30, sale 17:00): ¿límite de
   retardo 30 para que hasta 9:30 sea retardo? Tras cambiar un horario hay que recalcular las
   asistencias del terminal (`recalcular_empleado()` en `modelo/hikvision.php`, por cada dni).
2. Decidir si un único marcaje por la tarde cuenta como "salida sin entrada" en vez de
   "entrada con falta".
3. Excluir del dashboard a `juanmanuel` si no checa (genera faltas).
4. Servidor: reservar la IP 192.168.0.207 en el router; programar
   `cron/sincronizar_hikvision.php` en el Programador de tareas (el CLI necesita las mismas
   variables HIK_* que Apache); reiniciar Apache desde el panel de XAMPP.
5. Pull request de `feature/hikvision` hacia `main` cuando se apruebe.
