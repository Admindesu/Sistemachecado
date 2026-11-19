-- Bitácora de recálculos de asistencia (botón "Recalcular" de vista/empleado.php).
-- Un cambio de horario aplica desde la firma del documento y no toca lo ya registrado; solo en
-- casos extraordinarios (p. ej. un horario asignado por error) el administrador recalcula un
-- rango de fechas. Cada recálculo queda registrado aquí para auditoría.
-- XAMPP: ejecutar una vez en phpMyAdmin sobre la base sis-asistencia (se puede repetir sin problema).
-- Docker: se ejecuta solo al crear la base de datos por primera vez.

CREATE TABLE IF NOT EXISTS `recalculo_asistencia` (
  `id_recalculo` int(11) NOT NULL AUTO_INCREMENT,
  `dni` varchar(255) NOT NULL COMMENT 'Empleado recalculado',
  `desde` date NOT NULL,
  `hasta` date NOT NULL,
  `motivo` varchar(255) NOT NULL,
  `id_horario` int(11) DEFAULT NULL COMMENT 'Horario con el que se recalculó',
  `dias` int(11) NOT NULL DEFAULT 0 COMMENT 'Días con registros que se recalcularon',
  `registros` int(11) NOT NULL DEFAULT 0 COMMENT 'Registros web cuyo estado cambió',
  `realizado_por` varchar(255) DEFAULT NULL COMMENT 'Número de empleado del administrador',
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_recalculo`),
  KEY `idx_recalculo_dni` (`dni`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
