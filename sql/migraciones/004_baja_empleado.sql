-- Baja de empleados (soft delete): el colaborador que deja de laborar se marca como inactivo
-- y sus asistencias, marcajes y justificaciones se conservan.
-- XAMPP: ejecutar una vez en phpMyAdmin sobre la base sis-asistencia (se puede repetir sin problema).
-- Docker: se ejecuta solo al crear la base de datos por primera vez.

ALTER TABLE `empleado`
  ADD COLUMN IF NOT EXISTS `activo` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0 = dado de baja',
  ADD COLUMN IF NOT EXISTS `fecha_baja` date DEFAULT NULL COMMENT 'Último día laborado',
  ADD COLUMN IF NOT EXISTS `motivo_baja` varchar(150) DEFAULT NULL,
  ADD INDEX IF NOT EXISTS `idx_empleado_activo` (`activo`);
