# Arquitectura y guía para desarrolladores

- [Estructura del proyecto](#estructura-del-proyecto)
- [Flujo de una página](#flujo-de-una-página)
- [Base de datos](#base-de-datos)
- [Reglas de asistencia](#reglas-de-asistencia)
- [Seguridad](#seguridad)
- [Reportes](#reportes)
- [Convenciones y trampas conocidas](#convenciones-y-trampas-conocidas)
- [Cómo agregar…](#cómo-agregar)

## Estructura del proyecto

PHP sin framework, organizado como MVC ligero. Las vistas incluyen a sus controladores.

```
index.php                     Pantalla de checado web (todos los usuarios)
api/hikvision/evento.php      Endpoint de HTTP Listening del terminal
cron/sincronizar_hikvision.php  Sincronización periódica con el terminal (CLI)
controlador/
  auth.php                    Sesión, roles, CSRF, escape HTML (e()), contraseñas
  login.php, controlador_cerrar_sesion.php, controlador_cambiar_password.php
  controlador_registrar_entrada_salida.php   Checado web
  controlador_{registrar,modificar,eliminar}_{empleado,cargo,direccion,horario,subsecretaria}.php
  controlador_editar_masivo.php              Edición masiva de empleados
  importar_csv.php, get_asistencias_*.php    Importación CSV y datos para gráficas
modelo/
  conexion.php                Conexión mysqli (variables DB_*) y zona horaria (APP_TZ)
  db.php                      consulta(): consultas preparadas
  asistencia_reglas.php       Estados de entrada/salida, validación de horarios
  hikvision.php               Cliente ISAPI, normalización de eventos, recalcular_dia()
  dashboard.php, calendario.php   Cálculos del dashboard, días inhábiles, justificaciones
  xlsx.php                    Generador de .xlsx sin dependencias
vista/
  layout/{topbar,sidebar,footer}.php   Plantilla común
  dashboard.php, inicio.php (asistencias), empleado.php, organigrama.php, horarios.php,
  justificaciones.php, dias_inhabiles.php, importar_hikvision.php, registro_*.php, acerca.php
  reportes/{pdf_report,excel_report}.php
  login/                      Pantalla de inicio de sesión (CSS propio)
libs/                         FPDF, FPDI y header_pdf.png (encabezado del PDF)
public/                       CSS/JS de la plantilla, DataTables, íconos
sql/                          Esquema base y migraciones
docker/                       Imagen PHP 8.2 + Apache
```

## Flujo de una página

Una vista típica (`vista/horarios.php`):

1. `require_once '../controlador/auth.php'; requerir_admin();` — exige sesión de administrador
   y, en POST, un token CSRF válido.
2. `require('./layout/topbar.php')` y `sidebar.php`.
3. Incluye `modelo/conexion.php` y, si procesa formularios en la misma página, su controlador.
4. Contenido.
5. `require('./layout/footer.php')` — carga jQuery, Bootstrap, DataTables (inicializa
   `#example`) y SweetAlert.
6. Scripts propios de la vista **después** del footer (ver [trampas](#convenciones-y-trampas-conocidas)).

Los controladores que reciben formularios responden con una **redirección** con un código de
resultado (`?msg=registrado`, `?masivo=ok`) y la vista muestra el mensaje con SweetAlert; así
recargar la página no reenvía el formulario.

## Base de datos

MariaDB, base `sis-asistencia`. Esquema base en `sql/sis-asistencia.sql` más
`sql/migraciones/001-003`.

| Tabla | Contenido |
|---|---|
| `empleado` | Persona y usuario: `dni` (= número de empleado del terminal, único), `usuario`, `password`, `is_admin`, `cargo`, `direccion`, `subsecretaria` (opcional), `id_horario`. |
| `asistencia` | Un registro por entrada o salida: `dni`, `tipo` (`entrada`/`salida`), `fecha`, `estado`, `origen` (`web`/`hikvision`), `id_marcaje`. |
| `marcaje_dispositivo` | Marcajes crudos del terminal; llave única (`dispositivo`, `dni`, `fecha`). Nunca se modifican. |
| `horarios` | `hora_entrada`, `hora_salida`, `tolerancia_entrada`, `limite_retardo` (minutos desde la entrada). |
| `cargo`, `direccion`, `subsecretaria` | Catálogos del organigrama. |
| `justificacion` | Permisos: `dni`, `fecha_inicio`, `fecha_fin`, `tipo` (`entrada`/`salida`/`dia_completo`), `motivo`. |
| `dia_inhabil` | `fecha`, `descripcion`, `anual` (se repite cada año). |

Llaves foráneas a tener en cuenta:

- `asistencia.dni` y `justificacion.dni` → `empleado.dni` con **ON DELETE CASCADE**: borrar un
  empleado borra su historial.
- `empleado.cargo` → `cargo` con **ON DELETE CASCADE**: borrar un cargo directamente en la base
  borraría a sus empleados. La aplicación lo impide (no deja borrar un cargo en uso), pero cuidado
  con borrados manuales en phpMyAdmin.
- `empleado.id_horario` → `horarios` (RESTRICT).

`sql/sis-asistencia.sql` trae datos de ejemplo (empleados y asistencias de prueba, usuario
`juanmanuel` / `123`, contraseñas en md5). En una instalación real conviene borrarlos tras crear
el primer administrador.

## Reglas de asistencia

Todo está en `modelo/asistencia_reglas.php` y lo usan por igual el checado web y el terminal.
Las horas se comparan en minutos desde la medianoche (no hay turnos nocturnos).

**Entrada** — `estado_entrada()`; `E` = hora de entrada:

| Hora de checado | Estado |
|---|---|
| antes de `E − 15 min` | `ANTICIPADA` (la web la rechaza; el terminal la registra como `A_TIEMPO`) |
| hasta `E + tolerancia_entrada` | `A_TIEMPO` |
| hasta `E + limite_retardo` | `RETARDO` |
| después | `FALTA` |

**Salida** — `estado_salida()`; `S` = hora de salida:

| Hora de checado | Estado |
|---|---|
| antes de `S − 15 min` | `TEMPRANA` (la web la rechaza; el terminal la guarda como `SALIDA_TEMPRANA`) |
| hasta `S + 120 min` | `NORMAL` |
| después | `FALTA`, y la entrada del día también pasa a `FALTA` |

Constantes: `MINUTOS_ANTICIPACION_ENTRADA = 15`, `TOLERANCIA_SALIDA_DEFECTO = 15`,
`MINUTOS_FALTA_SALIDA = 120`.

**Horarios**: `validar_tolerancias()` exige `0 ≤ tolerancia < límite de retardo` y que
`entrada + límite` sea anterior a la salida. No hay tope fijo de minutos (hay horarios con 90 min
de tolerancia).

**Terminal**: `recalcular_dia($dni, $dia)` borra y regenera las filas `origen = 'hikvision'` de
ese día a partir de todos sus marcajes (idempotente): primer marcaje = entrada, último = salida,
lecturas a menos de 5 min de la entrada se ignoran, y los checados web del día se respetan. Solo
se llama para los días con marcajes nuevos.

**Cambios de horario**: aplican desde ese momento; el historial no se recalcula salvo que el
administrador lo pida para corregir un error (`recalcular_empleado()` / opción de la edición
masiva).

**Dashboard** (`modelo/dashboard.php`): por empleado y día laborable (lunes a viernes, sin días
inhábiles): `PUNTUAL`, `RETARDO`, `FALTA` (entrada con falta o sin entrada), `PENDIENTE` (hoy,
aún dentro del límite) o `JUSTIFICADO`; además `SIN_SALIDA`. Las justificaciones solo mejoran el
estado y solo se aplican aquí (no modifican la tabla `asistencia`).

## Seguridad

- **Sesión**: cookie `HttpOnly` y `SameSite=Lax`; `session_regenerate_id()` al iniciar sesión.
- **Roles**: `requerir_sesion()` (cualquier usuario) y `requerir_admin()`. Un empleado sin
  permisos que entra a una vista de administración es enviado a `index.php`. Los reportes
  permiten a un empleado ver solo sus propios registros.
- **CSRF**: todo formulario POST lleva `<?= campo_csrf() ?>`; `requerir_sesion()` lo valida.
  Para `fetch`, enviar el token en el campo `csrf` o en la cabecera `X-CSRF-Token`.
- **XSS**: toda salida de datos va por `e()` (`htmlspecialchars`).
- **SQL**: consultas preparadas con `consulta($conexion, $sql, $tipos, ...$params)`.
- **Contraseñas**: `password_hash()`; las antiguas en md5 se aceptan y se migran solas al
  iniciar sesión.
- **Terminal**: el endpoint exige `HIK_PUSH_TOKEN` (comparación con `hash_equals`) y opcionalmente
  una lista de IPs. La contraseña del terminal y el token viven en la configuración del servidor,
  nunca en el repositorio.

## Reportes

- **PDF** (`vista/reportes/pdf_report.php`): FPDF/FPDI, A4 horizontal. La clase `PDF` dibuja el
  encabezado (`libs/header_pdf.png` con su proporción real, sobre una franja del mismo color),
  repite la cabecera de la tabla en cada página y numera las páginas. FPDF usa Latin-1: los textos
  pasan por `utf8_decode()`.
- **Excel** (`vista/reportes/excel_report.php` + `modelo/xlsx.php`): la clase `Xlsx` arma el
  `.xlsx` (Office Open XML) y el ZIP a mano con `gzdeflate`, sin la extensión `zip`. Admite texto,
  números y fechas (`DateTimeInterface` → número de serie de Excel), estilos predefinidos
  (`TITULO`, `ENCABEZADO`, `FECHA`, `HORA`, `VERDE`, `AMARILLO`, `ROJO`…), anchos de columna,
  celdas combinadas, autofiltro y paneles fijos.

## Convenciones y trampas conocidas

- **`footer.php` vuelve a cargar jQuery y Bootstrap.** El código de una vista que use eventos de
  jQuery o Bootstrap (modales, DataTables) debe ir **después** de `require('./layout/footer.php')`;
  si va antes, sus manejadores quedan en la primera copia de jQuery y no reciben los eventos.
- **DataTables** se inicializa en `footer.php` para la tabla `#example`. El orden inicial se
  define en la tabla con `data-order='[[columna, "desc"]]'` y una columna se excluye con
  `data-orderable="false"` en su `<th>`. No llamar `$('#example').DataTable()` antes de que el
  footer la inicialice (se crearía dos veces); usar `$.fn.dataTable.isDataTable('#example')`.
- **Nombres de archivo**: Linux distingue mayúsculas (`logo.png` ≠ `logo.PNG`).
- **Codificación**: archivos UTF-8; la mayoría con fin de línea CRLF.
- **Subsecretarías**: todo lo relacionado va envuelto en `usar_subsecretarias()`.
- **Zona horaria**: siempre `APP_TZ`; la base guarda hora local.

## Cómo agregar…

**Una opción al menú**: agregar una entrada a `$menu` en `vista/layout/sidebar.php`
(`[página, ícono en public/img-inicio/, TEXTO, [subpáginas]]`). El resaltado de la página actual
es automático.

**Una vista de administración**: copiar la estructura de `vista/dias_inhabiles.php` (acciones POST
con redirección y mensaje, CSRF, `e()` en la salida).

**Una tabla nueva**: crear `sql/migraciones/00N_descripcion.sql` con `CREATE TABLE IF NOT EXISTS`
/ `ADD COLUMN IF NOT EXISTS` (para que se pueda repetir), montarla en `docker-compose.yml`
(`docker-entrypoint-initdb.d/0N-...`) y documentarla en [INSTALACION.md](INSTALACION.md#migraciones-de-base-de-datos).

**Un método de marcaje del terminal**: agregar su *minor* a `HIK_MINORS_VALIDOS`
(ver [HIKVISION.md](HIKVISION.md#subtipos-de-evento)).
