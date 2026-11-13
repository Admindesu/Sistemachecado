# Sistema de Control de Asistencias

Sistema web para registrar y supervisar la asistencia del personal. Recibe los marcajes de un
terminal biométrico **Hikvision** (rostro, huella, tarjeta) y también permite checar desde el
navegador; calcula retardos y faltas según el horario de cada empleado y genera reportes en
PDF y Excel.

Desarrollado en PHP 8 + MariaDB, sin frameworks ni Composer. Funciona con **XAMPP** en Windows
o con **Docker**.

## Funciones principales

| Módulo | Qué hace |
|---|---|
| **Dashboard** | Puntualidad, retardos, faltas, pendientes y salidas sin checar por día, semana, mes o rango. |
| **Asistencias** | Tabla de todos los registros (lo más reciente primero), reporte **PDF** y exportación a **Excel (.xlsx)**. |
| **Empleados** | Alta, edición, baja, importación por CSV, **importación desde el terminal** y **edición masiva** de cargo, dirección y horario. |
| **Organigrama** | Catálogos de cargos y direcciones (y subsecretarías, si se activan). |
| **Horarios** | Hora de entrada y salida, minutos de tolerancia y límite de retardo. |
| **Justificaciones** | Permisos que justifican una entrada, una salida o el día completo. |
| **Días inhábiles** | Festivos y asuetos (pueden repetirse cada año); no generan faltas. |
| **Checado web** | Cada empleado marca entrada y salida desde `index.php` y descarga su propio reporte. |
| **Terminal Hikvision** | Recepción en tiempo real (HTTP Listening) y sincronización periódica de respaldo. |

## Inicio rápido

### Docker

```bash
cp .env.example .env          # cambiar contraseñas y datos del terminal
docker compose up -d --build
```

Aplicación en http://localhost:8080. La base de datos y las migraciones se crean solas.

### XAMPP (Windows)

Ver la guía completa en [docs/INSTALACION.md](docs/INSTALACION.md): alias de Apache, variables
de entorno, base de datos, migraciones y tarea programada.

**Usuario inicial** (base nueva): `juanmanuel` / `123`. Cámbialo al entrar.

## Documentación

| Documento | Para quién | Contenido |
|---|---|---|
| [docs/MANUAL_USUARIO.md](docs/MANUAL_USUARIO.md) | Administradores y empleados | Cómo usar cada módulo. |
| [docs/INSTALACION.md](docs/INSTALACION.md) | Quien instala o mantiene el servidor | Docker, XAMPP, variables, migraciones, respaldos. |
| [docs/HIKVISION.md](docs/HIKVISION.md) | Quien configura el terminal | Configuración del equipo, cómo se procesan los marcajes, solución de problemas. |
| [docs/ARQUITECTURA.md](docs/ARQUITECTURA.md) | Desarrolladores | Estructura del código, base de datos, reglas de asistencia, seguridad y convenciones. |
| [docs/CONTINUAR.md](docs/CONTINUAR.md) | Desarrolladores | Estado actual del trabajo y pendientes. |

## Reglas de asistencia (resumen)

Con un horario de entrada 09:00, tolerancia 10 y límite de retardo 20:

| Hora de entrada | Estado |
|---|---|
| hasta 09:10 | **A tiempo** |
| 09:11 – 09:20 | **Retardo** |
| después de 09:20 | **Falta** |

- La salida antes de la hora (menos 15 min) es **salida temprana**; más de 2 h después, **falta**.
- Un cambio de horario aplica **desde ese momento**; las asistencias ya registradas no se
  recalculan (salvo que el horario se haya asignado por error).
- Detalles en [docs/ARQUITECTURA.md](docs/ARQUITECTURA.md#reglas-de-asistencia).

## Créditos

Desarrollado por Ing. Francisco Daniel Paredes Moreno e Ing. en Sistemas Fermín Pérez Soza.
Basado originalmente en un proyecto de
[InformaticaStudios](https://www.youtube.com/watch?v=w5wrSsW9s_M).
