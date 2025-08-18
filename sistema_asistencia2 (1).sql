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
INSERT INTO `cargo` VALUES ('1', 'cirujano');
INSERT INTO `cargo` VALUES ('2', 'odontologo');
INSERT INTO `cargo` VALUES ('3', 'farmacia');
INSERT INTO `cargo` VALUES ('4', 'limpieza');
INSERT INTO `cargo` VALUES ('5', 'enfermera');

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
INSERT INTO `empleado` VALUES ('1', 'juan manuel', 'quispe chocce', '78945612', '1', 'juanmanuel', '202cb962ac59075b964b07152d234b70', '1');
INSERT INTO `empleado` VALUES ('2', 'josep', 'vega chavez', '77441122', '2', 'josepvega', '202cb962ac59075b964b07152d234b70', '0');
INSERT INTO `empleado` VALUES ('3', 'erick', 'muleta paredes', '77885522', '3', 'erickmuleta', '202cb962ac59075b964b07152d234b70', '0');
INSERT INTO `empleado` VALUES ('4', 'maria', 'molina gutierrez', '00225566', '5', 'mariamolina', '202cb962ac59075b964b07152d234b70', '0');
INSERT INTO `empleado` VALUES ('6', 'ismael', 'sandoval', '74433542', '4', 'ismaelsandoval', '202cb962ac59075b964b07152d234b70', '0');
INSERT INTO `empleado` VALUES ('11', 'prueba', 'prueba', '00225588', '1', 'prueba', '202cb962ac59075b964b07152d234b70', '0');

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
