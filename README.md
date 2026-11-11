Sistema de Control de Asistencias
Este es un sistema moderno y fácil de usar para la gestión de asistencia de personal, desarrollado con PHP para el backend y JavaScript para una interfaz de usuario dinámica y responsiva. Es ideal para llevar un registro preciso de la puntualidad y la presencia de los colaboradores de la Secretaria.

Características Principales
Registro de Asistencia: Permite a los usuarios marcar su entrada y salida de forma sencilla.

Gestión de Usuarios: Creación, edición y eliminación de usuarios con diferentes roles (por ejemplo, empleado, administrador).

Visualización de Reportes: Generación de reportes detallados de asistencia por día, semana o mes, con filtros personalizables.

Interfaz Intuitiva: Diseño amigable y fácil de navegar gracias a JavaScript.

Base de Datos Relacional: Almacenamiento seguro y organizado de datos de asistencia y usuarios.

Configuración Flexible: Permite ajustar parámetros como horarios de trabajo, días festivos, etc.

Tecnologías Utilizadas
Backend: PHP

Frontend: HTML5, CSS3, JavaScript

Base de Datos: MySQL (XAMPP) 

Servidor Web: Apache / Nginx (XAMMP)

Requisitos del Sistema
Para ejecutar este sistema, necesitas tener instalado lo siguiente:

Servidor Web: Apache, Nginx o similar, recomendamos usar Xampp para mayor facilidad de instalacion y manejo.

PHP: Versión 7.4 o superior.

Extensiones PHP recomendadas: mysqli, pdo, json.

Base de Datos: MySQL 5.7+ o MariaDB 10.2+.

## Instalación con Docker (recomendado)

Requisitos: Docker Desktop (incluye Docker Compose).

```bash
cp .env.example .env      # y cambia las contraseñas
docker compose up -d --build
```

- Aplicación: http://localhost:8080
- phpMyAdmin (opcional): `docker compose --profile tools up -d` → http://localhost:8081

La primera vez se crea la base de datos `sis-asistencia` con `sql/sis-asistencia.sql`.
Ese script solo corre cuando el volumen está vacío; para reiniciar la BD desde cero:
`docker compose down -v` (borra todos los datos).

La zona horaria se configura con `APP_TZ` en `.env` (por defecto `America/Cancun`).

## Integración con terminal Hikvision (DS-K1T344MBFWX-E1)

El terminal solo registra **quién pasó y a qué hora**. Este sistema decide qué es entrada
y qué es salida y calcula el estado con las mismas reglas del checado web:

- El primer marcaje del día es la **entrada** y el último la **salida**. Las lecturas repetidas
  a menos de 5 minutos de la entrada se ignoran.
- Una entrada anticipada cuenta como `A_TIEMPO`; una salida antes de hora queda como `SALIDA_TEMPRANA`.
- Si el empleado ya checó entrada o salida en la web, esa se respeta y el terminal completa la otra.
- El checado web (`index.php`) sigue funcionando igual.

Los marcajes llegan por dos vías y se guardan sin duplicados en `marcaje_dispositivo`:

1. **Envío en tiempo real**: el terminal envía cada evento a `api/hikvision/evento.php`.
2. **Consulta periódica**: el servicio `worker` consulta el terminal cada `HIK_INTERVALO_SEG`
   segundos y recupera lo que no haya llegado (servidor apagado, red caída).

### 1. Base de datos
- Docker: la migración se aplica sola al crear la base.
- XAMPP o base existente: ejecutar `sql/migraciones/001_hikvision.sql` una vez en phpMyAdmin.

### 2. Variables en `.env`
`HIK_HOST` (IP del terminal), `HIK_USUARIO`, `HIK_PASSWORD` y un `HIK_PUSH_TOKEN` largo y aleatorio.
Ver `.env.example`.

### 3. En el terminal (interfaz web del dispositivo)
- **Hora**: activar NTP y poner la misma zona horaria que `APP_TZ`. Si la hora del terminal está mal,
  los retardos se calculan mal.
- **Usuarios**: el *Employee No.* de cada persona debe ser igual a su **NoEmpleado (DNI)** en este sistema.
  Los marcajes de números que no existen aquí se guardan, pero no generan asistencia.
- **HTTP Listening** (Configuración → Red → Servicio avanzado):
  IP de este servidor, puerto `APP_PORT` (8080), protocolo HTTP y URL
  `/api/hikvision/evento.php?token=<HIK_PUSH_TOKEN>`.
- Desactivar *"Guardar foto al autenticar"* en el envío si el terminal reintenta sin fin.

### 4. Probar la consulta manualmente
```bash
docker compose exec worker php cron/sincronizar_hikvision.php --desde=2026-11-01
```
Con XAMPP: `php cron/sincronizar_hikvision.php`, programado con el Programador de tareas de Windows.

> Sin probar aún contra el equipo real. Si el terminal responde `badParameters` a la consulta
> o los marcajes no aparecen, revisar el firmware y `HIK_MINORS_VALIDOS`
> (subtipos que cuentan como marcaje: 1 tarjeta, 38 huella, 75 rostro).

## Instalación con XAMPP

Sigue estos pasos para poner en marcha el sistema en tu entorno local o servidor:

Clona el Repositorio:

Configura el Servidor Web:
Asegúrate de que tu servidor web (Apache/Nginx) apunte al directorio raíz del proyecto (nombre-del-repositorio).

Configura la Base de Datos:

Crea una nueva base de datos en tu servidor MySQL sis-asistencia.

Importa el archivo sistema_asistencia2.sql  a tu nueva base de datos. Este archivo contiene la estructura de las tablas y algunos datos iniciales.
Abre tu navegador web y navega a la URL donde hayas desplegado el sistema (ej. http://localhost/sis-asistencia/vista/login/login.php o http://tu-dominio.com/).


Inicia Sesion
Usuario: isai

Contraseña: 123 (¡Por favor, cambia esta contraseña inmediatamente después de iniciar sesión!)
Esto es un Fork de InformaticaStudios: https://www.youtube.com/watch?v=w5wrSsW9s_M&list=LL&index=4&ab_channel=InformaticaStudios
