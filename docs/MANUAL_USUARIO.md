# Manual de usuario

- [Entrar al sistema](#entrar-al-sistema)
- [Empleados: checado web y reporte propio](#empleados-checado-web-y-reporte-propio)
- [Administradores](#administradores)
  - [Dashboard](#dashboard)
  - [Asistencias](#asistencias)
  - [Empleados](#empleados)
  - [Organigrama](#organigrama)
  - [Horarios](#horarios)
  - [Justificaciones](#justificaciones)
  - [Días inhábiles](#días-inhábiles)
- [Estados de asistencia](#estados-de-asistencia)
- [Preguntas frecuentes](#preguntas-frecuentes)

## Entrar al sistema

Abre la dirección del sistema (por ejemplo `http://192.168.0.207/checador/`) y escribe tu
**usuario** y **contraseña**. Los empleados importados desde el terminal tienen como usuario su
**número de empleado** y una contraseña temporal que les da el administrador.

Para cambiar tu contraseña: menú de tu nombre (arriba a la derecha) → **Cambiar contraseña**.

## Empleados: checado web y reporte propio

Al entrar verás un reloj y dos botones:

- **Entrada** y **Salida** registran tu asistencia con la hora del servidor. Normalmente se
  checa en el terminal (rostro o huella); el checado web es la alternativa.
- No se puede checar entrada más de 15 minutos antes de tu hora, ni salida antes de tiempo,
  ni registrar dos entradas o dos salidas el mismo día. El sistema te dice el motivo.
- También puedes descargar el **reporte PDF de tus asistencias** de un periodo.

Los administradores ven además el botón **Ingresar al sistema**, que lleva al panel.

## Administradores

El menú lateral tiene, en orden: Dashboard, Asistencias, Empleados, Organigrama, Horarios,
Justificaciones, Días inhábiles y Acerca de.

### Dashboard

Resumen de asistencia de un periodo: **Ayer** (último día hábil; es lo que se ve al abrir),
**Hoy**, **Esta semana**, **Este mes** o un **rango** de fechas.

- **Tarjetas**: puntuales, retardos, faltas y porcentaje de puntualidad; además *pendientes*
  (hoy, aún dentro de su horario de entrada) y *sin salida* (checaron entrada pero no salida).
- **Un solo día**: lista de personas agrupada por estado, con enlace **Justificar** en retardos,
  faltas y salidas sin checar.
- **Varios días**: gráfica por día y tabla por empleado, primero los que más faltan.
- Fines de semana y **días inhábiles** no generan faltas. Las **justificaciones** convierten el
  retardo o la falta en *Justificado*.

### Asistencias

Todos los registros de entrada y salida, **lo más reciente primero**. La columna **Origen**
indica si vino del terminal (*Hikvision*) o de la web. Se puede buscar y ordenar por cualquier
columna.

- **Generar Reporte PDF**: elige fechas y, opcionalmente, un empleado. Sale en hoja horizontal
  con el encabezado institucional.
- **Exportar a Excel**: mismos filtros; descarga un `.xlsx` con fechas y horas reales de Excel,
  el estado coloreado, filtros y encabezado fijo.
- Sin fechas, ambos reportes toman el año en curso.

### Empleados

- **Agregar Empleado**: alta manual.
- **Importar desde Hikvision**: trae a las personas registradas en el terminal (ver
  [HIKVISION.md](HIKVISION.md#dar-de-alta-al-personal)). Anota las contraseñas temporales que
  aparecen al terminar; no se vuelven a mostrar.
- **Importar CSV**: alta en lote desde un archivo; hay una plantilla para descargar.
- **Editar** / **Eliminar**: por empleado. Eliminar borra también sus asistencias.

**Edición masiva** — para cambiar cargo, dirección u horario a muchos a la vez:

1. Marca las casillas de la primera columna. Para seleccionar rápido, escribe en el buscador
   (por ejemplo el cargo provisional) y pulsa **Seleccionar todos los de la búsqueda**. La
   casilla del encabezado marca los de la página actual.
2. La selección se conserva al cambiar de página o de búsqueda. **Quitar selección** la limpia.
3. Pulsa **Editar seleccionados (N)**, elige solo lo que quieras cambiar (lo demás queda en
   *No cambiar*) y **Aplicar cambios**.
4. Al cambiar el horario aparece la opción **Corregir también sus asistencias anteriores**.
   Déjala desmarcada salvo que el horario anterior se haya asignado **por error**: un cambio de
   horario normal aplica desde ese momento.

### Organigrama

Catálogos de **cargos** y **direcciones** (y **subsecretarías**, solo si el sistema está
configurado para usarlas). El sistema no deja eliminar un cargo, una dirección o un horario que
tenga empleados asignados: primero hay que reasignarlos (la edición masiva sirve para eso).

### Horarios

Cada horario tiene hora de entrada y de salida, **tolerancia** y **límite de retardo**, ambos en
minutos contados desde la hora de entrada. Debajo del formulario aparece un resumen de las
franjas, por ejemplo con entrada 07:40, tolerancia 90 y límite 100:

> **A tiempo:** hasta las 9:10 · **Retardo:** de 9:11 a 9:20 · **Falta:** después de las 9:20

No hay tope de minutos; solo se exige que el retardo termine después de la tolerancia y antes
de la hora de salida.

Modificar un horario **no** cambia las asistencias ya registradas: aplica desde ese momento.

### Justificaciones

Registran permisos con documento: **empleado**, **qué se justifica** (entrada, salida o día
completo), **motivo** (permiso personal, incapacidad médica, comisión oficial, vacaciones u
otro), **rango de fechas** y observaciones.

Se reflejan en el **Dashboard** como *Justificado*. Los reportes PDF/Excel muestran el registro
tal como se checó.

### Días inhábiles

Festivos, asuetos o cierres. Marca **Se repite cada año** para fechas fijas (1 de enero,
25 de diciembre…). En esos días el dashboard no cuenta faltas.

## Estados de asistencia

| Estado | Entrada | Salida |
|---|---|---|
| **A tiempo** | Hasta la hora de entrada + tolerancia | — |
| **Retardo** | Después de la tolerancia, hasta el límite de retardo | — |
| **Falta** | Después del límite de retardo | Más de 2 h después de la hora de salida (también marca la entrada como falta) |
| **Normal** | — | Desde 15 min antes de la hora de salida hasta 2 h después |
| **Salida temprana** | — | Más de 15 min antes de la hora de salida (solo desde el terminal) |

## Preguntas frecuentes

**Un empleado marcó en el terminal y no aparece.** Revisa que su *número de empleado* en el
terminal sea igual a su *NoEmpleado* en el sistema y que tenga horario asignado.

**Solo hay un marcaje en la tarde y aparece como entrada con falta.** El primer marcaje del día
se toma como entrada. Si olvidó checar en la mañana, registra una justificación de entrada.

**Cambié el horario de alguien y sus faltas anteriores siguen igual.** Es lo esperado: el cambio
aplica desde ese momento. Si el horario anterior fue un error, reasígnalo con la edición masiva
marcando *Corregir también sus asistencias anteriores*.
