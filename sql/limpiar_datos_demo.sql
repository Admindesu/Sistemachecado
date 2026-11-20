-- Quita los datos de ejemplo que traía sql/sis-asistencia.sql en instalaciones hechas antes del
-- 2026-11-20: deja solo al administrador juanmanuel y una dirección, "Administracion".
--
-- Solo borra a los usuarios de ejemplo (coinciden usuario Y número de empleado), así que no toca
-- al personal real importado del terminal o por CSV. Se puede repetir sin problema.
-- Haz un respaldo antes de ejecutarlo.
--
-- XAMPP: ejecutar en phpMyAdmin sobre la base sis-asistencia.
-- Docker (desde cmd, en la carpeta del repositorio):
--   docker compose exec -T db mariadb -uroot -p<DB_ROOT_PASSWORD> sis-asistencia < sql\limpiar_datos_demo.sql

START TRANSACTION;

-- 1. Empleados de ejemplo. Sus asistencias y justificaciones se borran en cascada.
DELETE FROM empleado
WHERE (usuario, dni) IN (
    ('josepvega', '77441122'), ('mariamolina', '00225566'), ('ismaelsandoval', '74433542'),
    ('prueba', '00225588'), ('asmin', '7894561212'), ('adsasddsa', '123123'),
    ('carlos.gomez', '2395'), ('maria.perez', '2396'), ('juan.lopez', '2397'),
    ('ana.martinez', '2398'), ('luis.garcia', '2399'), ('sofia.sanchez', '2400'),
    ('pedro.ramirez', '2401'), ('lucia.torres', '2402'), ('miguel.diaz', '2403'),
    ('laura.fernandez', '2404'), ('jorge.ruiz', '2405'), ('elena.morales', '2406'),
    ('diego.castro', '2407'), ('paula.vargas', '2408'), ('raul.mendoza', '2409'),
    ('jperez', '12345678'), ('mgonzalez', '87654321')
);

-- 2. Checados de prueba de juanmanuel que venían en el ejemplo (2022 y agosto de 2025).
DELETE FROM asistencia
WHERE dni = '78945612' AND origen = 'web' AND id_asistencia BETWEEN 13 AND 41;

-- 3. Una sola dirección: "Administracion" (id 2), asignada a juanmanuel.
UPDATE direccion SET nombre = 'Administracion' WHERE id_direccion = 2;
UPDATE empleado SET direccion = 2 WHERE usuario = 'juanmanuel';
-- Las demás direcciones de ejemplo se borran si ya nadie las usa.
DELETE FROM direccion
WHERE id_direccion BETWEEN 1 AND 12 AND id_direccion <> 2
  AND id_direccion NOT IN (SELECT direccion FROM empleado);

COMMIT;

-- Comprobación: empleados y direcciones que quedaron.
SELECT id_empleado, usuario, nombre, apellido, direccion FROM empleado;
SELECT id_direccion, nombre FROM direccion;
