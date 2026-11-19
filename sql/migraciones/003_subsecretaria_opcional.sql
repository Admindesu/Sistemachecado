-- La subsecretaría pasa a ser opcional: hay centros de trabajo que no tienen.
-- XAMPP: ejecutar una vez en phpMyAdmin sobre la base sis-asistencia.
-- Docker: se ejecuta solo al crear la base de datos por primera vez.
-- (Numerada 003 porque la 002 la usa la rama feature/dashboard.)

ALTER TABLE `empleado`
  MODIFY `subsecretaria` int(11) DEFAULT NULL;
