# Notas para continuar (rama feature/hikvision)

Última actualización: 2026-11-13. Resumen de lo hecho y lo pendiente para retomar el trabajo
en otro equipo. Al iniciar Claude Code en el otro equipo basta con pedirle: *"lee docs/CONTINUAR.md
y sigamos"*.

## Estado de la rama

`feature/hikvision` (subida a GitHub, **sin fusionar en `main`**; el pull request aún no está
creado, ver Pendientes) contiene:

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

### Agregado el 2026-11-12

- **Login**: el fondo se arma con CSS (`vista/login/img/Qlogo.svg` + barra oscura con
  `logo_blanco.png`) en lugar de la imagen `grecas.png`, que se borró. Se escala con la altura
  de la pantalla y, en pantallas más angostas que 16:9, con el ancho (para no tapar el formulario).
- **Reporte PDF** (`vista/reportes/pdf_report.php`): A4 horizontal, encabezado `libs/header_pdf.png`
  en todas las páginas con su proporción real (si la imagen cambia no hay que tocar código),
  cabecera de tabla repetida y número de página.
- **Sidebar** (`vista/layout/sidebar.php`): se genera desde la lista `$menu` (Dashboard primero,
  Acerca de al final) y resalta solo la página actual; las subpáginas cuentan como su sección.
  Para reordenar o agregar opciones solo se edita esa lista (ya no hay reglas `nth-child` en
  las vistas).
- **Asistencias** (`vista/inicio.php`): ordenadas por fecha, la más reciente primero, y botón
  **Exportar a Excel** (`vista/reportes/excel_report.php`) que descarga un `.xlsx` real con los
  mismos filtros y permisos que el PDF. El generador es `modelo/xlsx.php` (sin dependencias; no
  requiere la extensión zip de PHP, que XAMPP trae desactivada).
- **Edición masiva de empleados** (`vista/empleado.php` + `controlador/controlador_editar_masivo.php`):
  casillas en la tabla, "Seleccionar todos los de la búsqueda" y "Editar seleccionados" para
  cambiar cargo, dirección u horario a varios a la vez. Al cambiar el horario hay una casilla
  (desmarcada) para corregir también las asistencias anteriores, solo si fue un error.

### Regla de negocio: cambios de horario

Un cambio de horario (de un empleado o del horario mismo) aplica **desde ese momento**: lo
respalda un documento y las asistencias ya registradas se conservan con el horario que tenían.
Solo se recalcula el historial cuando el horario se asignó por **error del administrador**
(casilla de la edición masiva, o `recalcular_empleado()` en `modelo/hikvision.php`).
El terminal solo recalcula el día de cada marcaje nuevo; si llega tarde un marcaje de un día
anterior al cambio, ese día se calcula con el horario vigente.

### Agregado el 2026-11-13

- **Bajas de personal (soft delete)**: migración `004_baja_empleado.sql` (`activo`, `fecha_baja`,
  `motivo_baja`). "Dar de baja" en Empleados conserva todo su historial; el dado de baja no puede
  iniciar sesión ni checar, el terminal no le genera asistencias después de la fecha de baja y el
  dashboard lo cuenta solo hasta esa fecha. "Ver bajas" permite reactivar o eliminar
  definitivamente (solo a quien ya está de baja). Respaldo previo:
  `Desktopsistenciaespaldo_antes_baja_empleado_20261113.sql`.
- Documentación completa en `README.md` y `docs/` (manual, instalación, Hikvision, arquitectura).

### Notas técnicas

- `vista/layout/footer.php` vuelve a cargar jQuery y Bootstrap: el JavaScript de una vista que
  use eventos de jQuery/Bootstrap (modales, DataTables) debe ir **después** de
  `require('./layout/footer.php')`, como en `inicio.php` y `empleado.php`.
- Para revisar páginas sin navegador visible: Edge headless
  (`msedge --headless=new --screenshot=... --window-size=...`). Para ver un PDF, renderizarlo con
  pdf.js en una página HTML local (Edge headless no dibuja PDFs).

## Instalación en el servidor (equipo con XAMPP)

- Alias Apache `/checador` en `C:\xampp\apache\conf\extra\httpd-checador.conf` (incluido desde
  `httpd.conf`). Ahí están las variables `SetEnv` (APP_TZ, HIK_HOST, HIK_USUARIO, HIK_PASSWORD,
  HIK_PUSH_TOKEN, HIK_MINORS_VALIDOS, HIK_AJUSTE_*, HIK_IGNORAR_ANTES). **La contraseña y el token
  no están en el repositorio**; ver ese archivo en el servidor o `.env.example` para los nombres.
- Ese archivo empieza con `LoadFile` de `brotlicommon.dll` y `brotlidec.dll`: sin ellos Apache no
  carga `php_curl` en Windows (`curl_init()` indefinida).
- Migraciones 001 a 004 ya aplicadas en la base local `sis-asistencia`.
- Terminal: 192.168.0.209. Servidor: 192.168.0.207 (Wi-Fi, DHCP).
  HTTP Listening apunta a `http://192.168.0.207/checador/api/hikvision/evento.php?token=...`.

## Datos

- 37 empleados importados del terminal (usuario = número de empleado) + `juanmanuel` (admin de prueba).
  Se borraron los 23 usuarios de prueba; respaldo en
  `Desktop\asistencia\respaldo_antes_borrar_pruebas_20261111.sql` (equipo servidor).
- Cargo y dirección: 28 importados siguen con los provisionales (Jefe de Departamento /
  Dirección Administrativa y Archivo); 7 ya tienen Dirección de Recursos de Revisión. Se pueden
  corregir con la edición masiva.
- Horario Default: 09:00–17:00, tolerancia 10, retardo 20 (falta después de 9:20).
- Horario Matutino (limpieza): 07:40–17:00, tolerancia 90 (hasta 9:10), retardo 100 (hasta 9:20).
  Asignado a Manuel Jesús Cárdenas Domínguez (40719) y Alfredo Koyoc Canul (40728).
- Los marcajes de solo tarde del 20 y 30 de octubre se dejan así (instalación del terminal).
- Respaldo antes de probar la edición masiva:
  `Desktop\asistencia\respaldo_antes_prueba_masivo_20261112.sql` (los datos de prueba se
  restauraron).

## Pendientes

1. Confirmar las reglas del Horario Default (personal entra 8:30–9:30, sale 17:00): ¿límite de
   retardo 30 para que hasta 9:30 sea retardo? (Aplica desde el cambio; ver la regla de
   negocio de arriba.)
2. Decidir si un único marcaje por la tarde cuenta como "salida sin entrada" en vez de
   "entrada con falta".
3. Excluir del dashboard a `juanmanuel` si no checa (genera faltas).
4. Servidor: reservar la IP 192.168.0.207 en el router; programar
   `cron/sincronizar_hikvision.php` en el Programador de tareas (el CLI necesita las mismas
   variables HIK_* que Apache); reiniciar Apache desde el panel de XAMPP.
5. Crear el pull request de `feature/hikvision` hacia `main` (en este equipo no está `gh`;
   se crea desde https://github.com/Admindesu/Sistemachecado/compare/main...feature/hikvision).
   En otras instalaciones hay que aplicar las migraciones 001 a 004.
6. Poner cargo y dirección reales a los importados (edición masiva).
