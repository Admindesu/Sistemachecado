-- Integración con el terminal Hikvision (requiere MariaDB 10.2+)
-- XAMPP: ejecutar una vez en phpMyAdmin sobre la base sis-asistencia.
-- Docker: se ejecuta solo al crear la base de datos por primera vez.

-- Marcajes crudos tal como los entrega el dispositivo (nunca se modifican).
CREATE TABLE IF NOT EXISTS `marcaje_dispositivo` (
  `id_marcaje` int NOT NULL AUTO_INCREMENT,
  `dispositivo` varchar(64) NOT NULL COMMENT 'IP o identificador del terminal',
  `dni` varchar(64) NOT NULL COMMENT 'employeeNo registrado en el dispositivo',
  `fecha` datetime NOT NULL COMMENT 'Hora local (APP_TZ)',
  `serial_no` int DEFAULT NULL,
  `minor` int DEFAULT NULL COMMENT 'Subtipo de evento Hikvision (75 = rostro, 1 = tarjeta, 38 = huella)',
  `metodo` varchar(32) DEFAULT NULL,
  `datos` text DEFAULT NULL COMMENT 'Evento original en JSON',
  `recibido_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_marcaje`),
  UNIQUE KEY `uq_marcaje` (`dispositivo`, `dni`, `fecha`),
  KEY `idx_marcaje_dni_fecha` (`dni`, `fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- De dónde viene cada registro de asistencia: 'web' (index.php) o 'hikvision'.
ALTER TABLE `asistencia`
  ADD COLUMN IF NOT EXISTS `origen` varchar(10) NOT NULL DEFAULT 'web',
  ADD COLUMN IF NOT EXISTS `id_marcaje` int DEFAULT NULL,
  ADD INDEX IF NOT EXISTS `idx_asistencia_dni_fecha` (`dni`, `fecha`);
