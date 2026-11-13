# Integración con el terminal Hikvision

Probado con el **DS-K1T344MBFWX-E1** (rostro + huella + tarjeta). Debería funcionar con otros
terminales de control de acceso Hikvision que soporten ISAPI (`/ISAPI/AccessControl/...`).

- [Cómo funciona](#cómo-funciona)
- [Configurar el terminal](#configurar-el-terminal)
- [Dar de alta al personal](#dar-de-alta-al-personal)
- [Cómo se convierten los marcajes en asistencias](#cómo-se-convierten-los-marcajes-en-asistencias)
- [Reloj desfasado](#reloj-desfasado)
- [Solución de problemas](#solución-de-problemas)

## Cómo funciona

El terminal solo informa **quién pasó y a qué hora**. El sistema decide qué es entrada y qué es
salida y calcula retardos y faltas con el horario del empleado.

```
Terminal ──(1) HTTP Listening, en tiempo real──▶ api/hikvision/evento.php ─┐
    ▲                                                                        ├─▶ marcaje_dispositivo ─▶ asistencia
    └──(2) consulta ISAPI cada 5 min──── cron/sincronizar_hikvision.php ───┘     (crudo, sin           (entrada/salida
                                                                                  duplicados)            con estado)
```

1. **HTTP Listening** (vía principal): el terminal envía cada evento al servidor al instante.
2. **Sincronización** (respaldo): `cron/sincronizar_hikvision.php` consulta los eventos del
   terminal (`/ISAPI/AccessControl/AcsEvent`) y recupera los que no llegaron, por ejemplo si el
   servidor estuvo apagado. En Docker lo corre el servicio `worker`; en XAMPP, el Programador de
   tareas (ver [INSTALACION.md](INSTALACION.md#4-sincronización-periódica-con-el-terminal)).

Los dos caminos guardan en `marcaje_dispositivo`, que no admite duplicados (mismo terminal,
empleado y hora), así que un evento que llega por ambos se cuenta una vez.

## Configurar el terminal

Desde la interfaz web del terminal (http://IP-del-terminal, usuario `admin`):

### Hora

**Configuración → Sistema → Hora**: activar **NTP** (servidor `pool.ntp.org` o el NTP de la red,
puerto 123) y elegir la zona horaria que corresponda a `APP_TZ`. Para Quintana Roo
(`America/Cancun`, UTC−5 sin horario de verano) se usa **GMT−05:00**.

> Si la hora del terminal está mal, todos los retardos y faltas se calculan mal. Si ya se
> registraron marcajes con la hora equivocada, ver [Reloj desfasado](#reloj-desfasado).

### HTTP Listening

**Configuración → Red → Servicio avanzado → HTTP Listening** (el nombre varía por firmware):

| Campo | Valor |
|---|---|
| Dirección IP de destino | IP fija del servidor, p. ej. `192.168.0.207` |
| Puerto | `80` con XAMPP, `8080` con Docker (`APP_PORT`) |
| Protocolo | HTTP |
| URL | XAMPP: `/checador/api/hikvision/evento.php?token=<HIK_PUSH_TOKEN>`<br>Docker: `/api/hikvision/evento.php?token=<HIK_PUSH_TOKEN>` |

- El endpoint responde siempre `200 OK` para que el terminal no reintente sin fin; los rechazos
  (token o IP inválidos) quedan en el log de errores de Apache/PHP.
- El terminal manda un *heartbeat* cada 30 s; se ignora.
- Si el terminal adjunta la foto y reintenta sin parar, desactivar el envío de fotos.

### Usuario de consulta

La sincronización entra con `HIK_USUARIO` / `HIK_PASSWORD` (autenticación digest). Puede ser
`admin` o un usuario de operador con permiso de consulta de eventos.

## Dar de alta al personal

La persona se registra **primero en el terminal** (nombre, número de empleado, rostro, huella).
El **Employee No.** del terminal es la llave: tiene que ser igual al **NoEmpleado (DNI)** del
sistema.

Para no capturar dos veces, usar **Empleados → Importar desde Hikvision**:

1. El sistema lee los usuarios del terminal y muestra los que aún no existen.
2. Se eligen los que se van a importar y los datos comunes (cargo, dirección, horario). Después
   se pueden corregir con la **edición masiva**.
3. Se crean con usuario = número de empleado y una **contraseña temporal** que se muestra una
   sola vez en pantalla; anótala o cámbiala después.
4. Los marcajes que esas personas ya tenían en el terminal se procesan en ese momento.

Los marcajes de números de empleado que no existen en el sistema se guardan en
`marcaje_dispositivo` pero no generan asistencia; al importar a la persona desde el terminal se
procesan. (Si se da de alta a mano, sus marcajes anteriores no se procesan hasta que marque de
nuevo ese día.)

## Cómo se convierten los marcajes en asistencias

Por cada empleado y día (`recalcular_dia()` en `modelo/hikvision.php`):

- El **primer** marcaje del día es la **entrada** y el **último** es la **salida**.
- Lecturas repetidas a menos de **5 minutos** de la entrada se ignoran (doble lectura).
- Si el empleado ya checó entrada o salida **en la web**, esa se respeta y el terminal completa
  la otra.
- El estado se calcula con las mismas reglas que el checado web
  (ver [ARQUITECTURA.md](ARQUITECTURA.md#reglas-de-asistencia)), con dos diferencias: la entrada
  anticipada cuenta como **A tiempo** (la web la rechaza) y la salida antes de hora queda
  registrada como **Salida temprana** (la web la rechaza).
- Solo se recalcula el día de cada marcaje **nuevo**. El historial no cambia al modificar
  horarios: un cambio de horario aplica desde ese momento.

### Subtipos de evento

`HIK_MINORS_VALIDOS` define qué eventos cuentan como marcaje (todos con *major* 5, control de
acceso). Los de "no reconocido" se ignoran.

| Minor | Método | ¿Cuenta? |
|---|---|---|
| 1 | Tarjeta válida | Sí |
| 38 | Huella reconocida | Sí |
| 75 | Rostro reconocido | Sí |
| 153 | Rostro + huella | Sí |
| 39, 76, 154 | Huella / rostro / rostro + huella **no** reconocidos | No |
| 21, 22 | Puerta abierta / cerrada | No |

Si un método de autenticación nuevo no genera asistencia, su *minor* no está en la lista. Los
eventos ignorados no se guardan, así que hay que verlo en el terminal (registro de eventos, o
consultando `/ISAPI/AccessControl/AcsEvent?format=json`) y agregarlo a `HIK_MINORS_VALIDOS`.

## Reloj desfasado

Si el terminal tuvo la hora mal un tiempo (antes de activar NTP), sus eventos quedan con esa
hora en la memoria del terminal. Para corregirlos al recibirlos:

| Variable | Ejemplo | Efecto |
|---|---|---|
| `HIK_AJUSTE_HASTA` | `2026-11-11 00:00:00` | Eventos con hora del terminal **anterior** a esta fecha… |
| `HIK_AJUSTE_HORAS` | `13` | …se recorren estas horas. |
| `HIK_IGNORAR_ANTES` | `2026-01-01` | Eventos anteriores a esta fecha se descartan (p. ej. el reloj arrancó en 2014). |

La corrección se aplica tanto en HTTP Listening como en la sincronización. Para rehacer
asistencias ya registradas con la hora mala: respaldar, borrar las filas `origen = 'hikvision'`
de `asistencia` y los `marcaje_dispositivo` del periodo, y volver a sincronizar con
`php cron/sincronizar_hikvision.php --desde=AAAA-MM-DD`.

## Solución de problemas

| Síntoma | Qué revisar |
|---|---|
| No llega nada en tiempo real | IP, puerto y URL de HTTP Listening; firewall de Windows; que la URL lleve el `token` correcto (mensaje "token inválido" en el log de Apache). |
| Llega el evento pero no aparece la asistencia | El *Employee No.* del terminal no coincide con el NoEmpleado del sistema, o el *minor* no está en `HIK_MINORS_VALIDOS`. |
| Asistencias con la hora corrida | Zona horaria/NTP del terminal y `APP_TZ`; ver [Reloj desfasado](#reloj-desfasado). |
| La sincronización falla con `curl_init()` | Extensión curl y DLL brotli (ver [INSTALACION.md](INSTALACION.md#problemas-frecuentes)). |
| La sincronización responde 401 | Usuario o contraseña del terminal (`HIK_USUARIO`, `HIK_PASSWORD`). |
| Casi todos tienen falta | Horario asignado incorrecto o tolerancias muy cortas; revisar **Horarios** y el horario de cada empleado. |
