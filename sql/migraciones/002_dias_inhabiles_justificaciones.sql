-- Días inhábiles y justificaciones de asistencia
-- XAMPP: ejecutar una vez en phpMyAdmin sobre la base sis-asistencia (se puede repetir sin problema).
-- Docker: se ejecuta solo al crear la base de datos por primera vez.

-- Días que no cuentan como laborables (festivos, asuetos, cierres).
-- anual = 1: se repite cada año en el mismo día y mes (p. ej. 1 de enero).
CREATE TABLE IF NOT EXISTS `dia_inhabil` (
  `id_dia_inhabil` int NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `descripcion` varchar(150) NOT NULL,
  `anual` tinyint(1) NOT NULL DEFAULT 0,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_dia_inhabil`),
  UNIQUE KEY `uq_dia_inhabil_fecha` (`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Permisos que justifican la entrada, la salida o el día completo de un empleado.
-- Mismo charset que empleado para poder usar la llave foránea sobre dni.
CREATE TABLE IF NOT EXISTS `justificacion` (
  `id_justificacion` int NOT NULL AUTO_INCREMENT,
  `dni` varchar(255) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `tipo` varchar(20) NOT NULL COMMENT 'entrada | salida | dia_completo',
  `motivo` varchar(40) NOT NULL COMMENT 'Permiso personal, Incapacidad médica, Comisión oficial, Vacaciones, Otro',
  `observaciones` varchar(500) DEFAULT NULL,
  `registrado_por` int DEFAULT NULL COMMENT 'id_empleado del administrador',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_justificacion`),
  KEY `idx_justificacion_dni_fechas` (`dni`, `fecha_inicio`, `fecha_fin`),
  CONSTRAINT `fk_justificacion_empleado` FOREIGN KEY (`dni`) REFERENCES `empleado` (`dni`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
