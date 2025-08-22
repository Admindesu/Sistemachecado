/*
Navicat MySQL Data Transfer

Source Server         : conex 3306
Source Server Version : 50505
Source Host           : 127.0.0.1:3306
Source Database       : sistema_asistencia

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2022-08-06 22:31:40
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for cargo
-- ----------------------------
DROP TABLE IF EXISTS `cargo`;
CREATE TABLE `cargo` (
  `id_cargo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_cargo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of cargo
-- ----------------------------
INSERT INTO `cargo` VALUES ('1', 'Analista Profesional');
INSERT INTO `cargo` VALUES ('2', 'Servicios Generales');
INSERT INTO `cargo` VALUES ('3', 'Jefe de Oficina');
INSERT INTO `cargo` VALUES ('4', 'Limpieza');
INSERT INTO `cargo` VALUES ('5', 'Supervisor Especializado');

-- ----------------------------
-- Table structure for direccion
-- ----------------------------
DROP TABLE IF EXISTS `direccion`;
CREATE TABLE `direccion` (
  `id_direccion` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_direccion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of direccion
-- ----------------------------
INSERT INTO `direccion` VALUES ('1', 'Direccion de Planeacion');
INSERT INTO `direccion` VALUES ('2', 'Direccion de Administracion');
INSERT INTO `direccion` VALUES ('3', 'Direccion de Tecnologia e Informacion');
INSERT INTO `direccion` VALUES ('4', 'Direccion de Residuos');
INSERT INTO `direccion` VALUES ('5', 'Direccion de Fomento y Proteccion Forestal');
INSERT INTO `direccion` VALUES ('6', 'Direccion de Cambio Climatico');
INSERT INTO `direccion` VALUES ('7', 'Direccion de Manejo y Productividad Forestal');
INSERT INTO `direccion` VALUES ('8', 'Direccion de Impacto y Riesgo Ambiental');
INSERT INTO `direccion` VALUES ('9', 'Direccion de Educacion Ambiental');
INSERT INTO `direccion` VALUES ('10', 'Direccion de Ordenamiento');
INSERT INTO `direccion` VALUES ('11', 'Direccion de Relaciones Publicas');
INSERT INTO `direccion` VALUES ('12', 'Direccion Juridica y Transparencia');

-- ----------------------------
-- Table structure for subsecretaria
-- ----------------------------
DROP TABLE IF EXISTS `subsecretaria`;
CREATE TABLE `subsecretaria` (
  `id_subsecretaria` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_subsecretaria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of subsecretaria
-- ----------------------------
INSERT INTO `subsecretaria` VALUES ('1', 'Subsecretaria de Cultura Politica Ambiental Planeacion y Mejora Regulatoria de Archivo');
INSERT INTO `subsecretaria` VALUES ('2', 'Subsecretaria de Gestion y Proteccion Ambiental');
INSERT INTO `subsecretaria` VALUES ('3', 'Subsecretaria de Desarrollo Sostenible y Cambio Climatico');



-- ----------------------------
-- Table structure for empleado
-- ----------------------------
DROP TABLE IF EXISTS `empleado`;
CREATE TABLE `empleado` (
  `id_empleado` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  `apellido` varchar(255) DEFAULT NULL,
  `dni` varchar(255) NOT NULL,
  `cargo` int(11) NOT NULL,
  `direccion` int(11) NOT NULL,
  `subsecretaria` int(11) NOT NULL,
  `usuario` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_empleado`),
  UNIQUE KEY `dni_unique` (`dni`),
  KEY `fk1` (`cargo`),
  CONSTRAINT `fk1` FOREIGN KEY (`cargo`) REFERENCES `cargo` (`id_cargo`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of empleado
-- ----------------------------
INSERT INTO `empleado` VALUES ('1', 'juan manuel', 'quispe chocce', '78945612', '1', '3', '1', 'juanmanuel', '202cb962ac59075b964b07152d234b70', '1');
INSERT INTO `empleado` VALUES ('2', 'josep', 'vega chavez', '77441122', '2', '3', '2', 'josepvega', '202cb962ac59075b964b07152d234b70', '0');
INSERT INTO `empleado` VALUES ('3', 'erick', 'muleta paredes', '77885522', '3', '3', '3', 'erickmuleta', '202cb962ac59075b964b07152d234b70', '0');
INSERT INTO `empleado` VALUES ('4', 'maria', 'molina gutierrez', '00225566', '5', '3', '4', 'mariamolina', '202cb962ac59075b964b07152d234b70', '0');
INSERT INTO `empleado` VALUES ('6', 'ismael', 'sandoval', '74433542', '4', '3', '5', 'ismaelsandoval', '202cb962ac59075b964b07152d234b70', '0');
INSERT INTO `empleado` VALUES ('11', 'prueba', 'prueba', '00225588', '1', '3', '6', 'prueba', '202cb962ac59075b964b07152d234b70', '0');

-- ----------------------------
-- Table structure for empresa
-- ----------------------------
DROP TABLE IF EXISTS `empresa`;
CREATE TABLE `empresa` (
  `id_empresa` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `ubicacion` varchar(255) DEFAULT NULL,
  `ruc` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_empresa`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of empresa
-- ----------------------------
INSERT INTO `empresa` VALUES ('1', 'Informatica Studios', '925310896', 'av. los incas', '78945612378');

-- ----------------------------
-- Table structure for asistencia
-- ----------------------------
DROP TABLE IF EXISTS `asistencia`;
CREATE TABLE `asistencia` (
  `id_asistencia` int(11) NOT NULL AUTO_INCREMENT,
  `dni` varchar(255) NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `fecha` datetime DEFAULT NULL,
  PRIMARY KEY (`id_asistencia`),
  KEY `fk2` (`dni`),
  CONSTRAINT `fk2` FOREIGN KEY (`dni`) REFERENCES `empleado` (`dni`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of asistencia
-- ----------------------------
INSERT INTO `asistencia` VALUES ('13', '78945612', 'entrada', '2022-03-31 00:17:34');
INSERT INTO `asistencia` VALUES ('14', '74433542', 'entrada', '2022-03-31 00:22:53');
INSERT INTO `asistencia` VALUES ('21', '00225588', 'entrada', '2022-03-31 10:36:58');
INSERT INTO `asistencia` VALUES ('22', '74433542', 'entrada', '2022-08-06 20:59:07');

-- Crear tabla de horarios
CREATE TABLE IF NOT EXISTS horarios (
    id_horario INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    hora_entrada TIME NOT NULL,
    hora_salida TIME NOT NULL,
    tolerancia_entrada INT NOT NULL DEFAULT 10, -- minutos de tolerancia para entrada
    limite_retardo INT NOT NULL DEFAULT 20, -- minutos máximos para considerar retardo
    descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Agregar horario por defecto (9:00 AM - 5:00 PM)
INSERT INTO horarios (nombre, hora_entrada, hora_salida, tolerancia_entrada, limite_retardo, descripcion) 
VALUES ('Horario Default', '09:00:00', '17:00:00', 10, 20, 'Horario estándar de 9 AM a 5 PM');

-- Agregar horario alternativo (7:00 AM - 2:30 PM)
INSERT INTO horarios (nombre, hora_entrada, hora_salida, tolerancia_entrada, limite_retardo, descripcion)
VALUES ('Horario Matutino', '07:00:00', '14:30:00', 10, 20, 'Horario matutino de 7 AM a 2:30 PM');

-- Agregar columna de horario a la tabla empleado
ALTER TABLE empleado
ADD COLUMN id_horario INT,
ADD FOREIGN KEY (id_horario) REFERENCES horarios(id_horario);

-- Asignar horario por defecto a empleados existentes
UPDATE empleado SET id_horario = 1 WHERE id_horario IS NULL;

ALTER TABLE asistencia ADD COLUMN estado VARCHAR(20) DEFAULT 'A_TIEMPO' 
COMMENT 'Posibles valores: A_TIEMPO, RETARDO, FALTA';

