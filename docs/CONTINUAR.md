# Notas para continuar (rama feature/hikvision)

Última actualización: 2026-11-17. Resumen de lo hecho y lo pendiente para retomar el trabajo
en otro equipo. Al iniciar Claude Code en el otro equipo basta con pedirle: *"lee docs/CONTINUAR.md
y sigamos"*.

## Estado de la rama

`feature/hikvision` se fusionó en `main` el 2026-11-19 (merge `0b6a77f`, subido a GitHub) y
contiene:

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
  cambiar cargo, dirección u horario a varios a la vez. (Desde el 2026-11-17 ya no recalcula;
  ver la regla de negocio.)

### Regla de negocio: cambios de horario

Un cambio de horario (de un empleado o del horario mismo) aplica **desde ese momento**: lo
respalda un documento y las asistencias ya registradas se conservan con el horario que tenían.
Solo se recalcula el historial cuando el horario se asignó por **error del administrador**
(solo con el botón **Recalcular** por empleado: rango de fechas, motivo obligatorio y bitácora
`recalculo_asistencia`; la edición masiva ya no recalcula).
El terminal solo recalcula el día de cada marcaje nuevo; si llega tarde un marcaje de un día
anterior al cambio, ese día se calcula con el horario vigente.

### Agregado el 2026-11-13

- **Bajas de personal (soft delete)**: migración `004_baja_empleado.sql` (`activo`, `fecha_baja`,
  `motivo_baja`). "Dar de baja" en Empleados conserva todo su historial; el dado de baja no puede
  iniciar sesión ni checar, el terminal no le genera asistencias después de la fecha de baja y el
  dashboard lo cuenta solo hasta esa fecha. "Ver bajas" permite reactivar o eliminar
  definitivamente (solo a quien ya está de baja). Nadie puede darse de baja a sí mismo (se
  compara por id y por número de empleado). Respaldo previo:
  `Desktop\asistencia\respaldo_antes_baja_empleado_20261113.sql`.
- Documentación completa en `README.md` y `docs/` (manual, instalación, Hikvision, arquitectura).
- Correcciones de interfaz: en Horarios los campos de tolerancia/retardo quedaban tapados (ahora
  usan su propia cuadrícula) y la etiqueta de *Origen* en Asistencias era invisible (ahora tiene
  color propio).
- **Documentos compartibles en claude.ai** (fuera del repositorio, privados hasta que se compartan):
  - Manual de operación con capturas: https://claude.ai/artifact/DUfo7yjcCWYRUrTDpKWviF
  - Documentación del sistema (Resumen, Manual de usuario, Instalación, Terminal Hikvision,
    Arquitectura): https://claude.ai/artifact/ACP4pbBCn2BuAYLA8XHMXR

  Las capturas usan **datos ficticios** porque el repositorio es público. Se tomaron con una
  copia anonimizada de la base (`sis-asistencia-demo`, ya borrada) servida con `php -S` y Edge
  headless. Si cambia la interfaz, hay que actualizar estos documentos a mano además de `docs/`.

### Agregado el 2026-11-17

- **Dar de baja no hacía nada**: la ventana se abría invisible (ver la nota técnica sobre
  `.modal('show')`). Ahora abre con `data-toggle="modal"`. Hasta hoy nadie se había dado de baja
  (38 de 38 activos). Falta probarlo con un empleado real: debe salir "Baja registrada" y pasar a
  "Ver bajas".
- **Asistencias** (`vista/inicio.php`): etiqueta "Baja dd/mm/aaaa" junto al nombre de quien está
  dado de baja.
- **Botón Recalcular** por empleado (`controlador/controlador_recalcular_asistencias.php`,
  `recalcular_rango()` y `recalcular_web_dia()` en `modelo/hikvision.php`): rango de fechas (máx.
  un año, sin futuras), motivo y confirmación obligatorios; recalcula con el horario actual los
  registros del terminal y los web, sin tocar lo posterior a la baja. Cada uso queda en la tabla
  `recalculo_asistencia` (migración `005_bitacora_recalculo.sql`, ya aplicada aquí). Es la **única**
  vía de recálculo: se quitó la casilla de la edición masiva. Motivo (del usuario): por auditoría,
  el cambio de horario lo respalda un documento firmado y aplica desde la firma o notificación.
  Probado sobre una copia de la base: con los horarios actuales los registros del terminal salen
  idénticos y solo cambiarían 8 registros web de agosto de 2025 (su horario cambió después).
- `graphify-out/` en `.gitignore`: grafo de conocimiento local del código (graphify instalado con
  `uv tool install graphifyy`; `/graphify .` en Claude Code lo reconstruye, excluyendo `public/` y
  `libs/`).
- Manual de operación (claude.ai) actualizado: apartado *Recalcular asistencias* con captura
  (Figura 11, nombre ficticio Juan Pérez), edición masiva sin recálculo y etiqueta de baja. El PDF
  exportado reemplazó a `Desktop\asistencia\Manual de operación — Sistema de Control de
  Asistencias.pdf` (18 páginas). El doc "Documentación" (ACP4pbBCn2BuAYLA8XHMXR) se
  actualizó el 2026-11-19 (Resumen, Manual, Instalación y Arquitectura).

### Notas técnicas

- Nunca abrir un modal con `$('#x').modal('show')` en las vistas del panel: `footer.php` también
  carga Bootstrap 5, que reemplaza `$.fn.modal` y pone la clase `show`, pero el CSS que manda es el
  de Bootstrap 4 (espera `in`) y la ventana queda abierta con opacity 0, tapando la página. Usar
  `data-toggle="modal" data-target="#x"` y llenar los campos en el `click`.
- En la consola de `empleado.php` aparece `Uncaught (in promise) Error: Container is not defined`
  desde antes de estos cambios; no está investigado.

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
- Migraciones 001 a 005 ya aplicadas en la base local `sis-asistencia`.
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
5. ~~Fusionar en `main`~~ (hecho el 2026-11-19). En otras instalaciones hay que aplicar las
   migraciones 001 a 005.
6. Poner cargo y dirección reales a los importados (edición masiva).
7. Revisar si conviene cambiar el `ON DELETE CASCADE` de la llave de cargo en `empleado`
   (borrar un cargo podría arrastrar empleados si se salta la validación de la vista).
8. Probar "Dar de baja" y "Recalcular" con datos reales (ver Agregado el 2026-11-17).
9. Opcional: vista para consultar la bitácora `recalculo_asistencia` desde el sistema (hoy solo
   en phpMyAdmin).
10. El dashboard repite reglas de `asistencia_reglas.php` (`minutos_hora_dashboard()`, límite de
    retardo 20 por defecto, `MINUTOS_LIMITE_SALIDA = 120`): unificarlas en un solo lugar.
11. ~~Actualizar el doc "Documentación" en claude.ai~~ (hecho el 2026-11-19).
