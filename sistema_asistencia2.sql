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
-- Table structure for asistencia
-- ----------------------------


-- ----------------------------
-- Table structure for cargo
-- ----------------------------
DROP TABLE IF EXISTS `cargo`;
CREATE TABLE `cargo` (
  `id_cargo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_cargo`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

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
  PRIMARY KEY (`id_empleado`),
  KEY `fk1` (`cargo`),
  CONSTRAINT `fk1` FOREIGN KEY (`cargo`) REFERENCES `cargo` (`id_cargo`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of empleado
-- ----------------------------
INSERT INTO `empleado` VALUES ('1', 'juan manuel', 'quispe chocce', '78945612', '1');
INSERT INTO `empleado` VALUES ('2', 'josep', 'vega chavez', '77441122', '2');
INSERT INTO `empleado` VALUES ('3', 'erick', 'muleta paredes', '77885522', '3');
INSERT INTO `empleado` VALUES ('4', 'maria', 'molina gutierrez', '00225566', '5');
INSERT INTO `empleado` VALUES ('6', 'ismael', 'sandoval', '74433542', '4');
INSERT INTO `empleado` VALUES ('11', 'prueba', 'prueba', '00225588', '1');

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
-- Table structure for usuario
-- ----------------------------
DROP TABLE IF EXISTS `usuario`;
CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `usuario` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of usuario
-- ----------------------------
INSERT INTO `usuario` VALUES ('1', 'ismaelito', 'sandoval', 'isai', '202cb962ac59075b964b07152d234b70');
INSERT INTO `usuario` VALUES ('2', 'juan', 'mamani', 'juan', '202cb962ac59075b964b07152d234b70');


DROP TABLE IF EXISTS `asistencia`;
CREATE TABLE `asistencia` (
  `id_asistencia` int(11) NOT NULL AUTO_INCREMENT,
  `id_empleado` int(11) NOT NULL,
  `entrada` datetime DEFAULT NULL,
  `salida` datetime DEFAULT NULL,
  PRIMARY KEY (`id_asistencia`),
  KEY `fk2` (`id_empleado`),
  CONSTRAINT `fk2` FOREIGN KEY (`id_empleado`) REFERENCES `empleado` (`id_empleado`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of asistencia
-- ----------------------------
INSERT INTO `asistencia` VALUES ('13', '1', '2022-03-31 00:17:34', '2022-03-31 00:17:41');
INSERT INTO `asistencia` VALUES ('14', '6', '2022-03-31 00:22:53', '2022-03-31 00:23:04');
INSERT INTO `asistencia` VALUES ('21', '11', '2022-03-31 10:36:58', '2022-03-31 10:37:37');
INSERT INTO `asistencia` VALUES ('22', '6', '2022-08-06 20:59:07', null);
