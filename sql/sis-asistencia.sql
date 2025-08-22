-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-08-2025 a las 21:45:39
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sis-asistencia`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia`
--

CREATE TABLE `asistencia` (
  `id_asistencia` int(11) NOT NULL,
  `dni` varchar(255) NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `fecha` datetime DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'A_TIEMPO' COMMENT 'Posibles valores: A_TIEMPO, RETARDO, FALTA'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `asistencia`
--

INSERT INTO `asistencia` (`id_asistencia`, `dni`, `tipo`, `fecha`, `estado`) VALUES
(13, '78945612', 'entrada', '2022-03-31 00:17:34', 'A_TIEMPO'),
(14, '74433542', 'entrada', '2022-03-31 00:22:53', 'A_TIEMPO'),
(21, '00225588', 'entrada', '2022-03-31 10:36:58', 'A_TIEMPO'),
(22, '74433542', 'entrada', '2022-08-06 20:59:07', 'A_TIEMPO'),
(23, '78945612', 'entrada', '2025-08-19 14:04:28', 'A_TIEMPO'),
(24, '78945612', 'entrada', '2025-08-19 14:04:29', 'A_TIEMPO'),
(25, '78945612', 'entrada', '2025-08-19 14:04:29', 'A_TIEMPO'),
(26, '78945612', 'entrada', '2025-08-19 14:04:29', 'A_TIEMPO'),
(27, '7894561212', 'entrada', '2025-08-19 14:22:55', 'A_TIEMPO'),
(28, '78945612', 'entrada', '2025-08-20 12:20:57', 'A_TIEMPO'),
(29, '78945612', 'salida', '2025-08-20 12:22:22', 'A_TIEMPO'),
(30, '78945612', 'entrada', '2025-08-22 13:33:56', 'FALTA'),
(31, '78945612', 'entrada', '2025-08-22 13:51:14', 'FALTA'),
(32, '78945612', 'entrada', '2025-08-22 13:51:15', 'FALTA'),
(33, '78945612', 'entrada', '2025-08-22 09:02:48', 'A_TIEMPO'),
(34, '78945612', 'entrada', '2025-08-22 09:03:01', 'A_TIEMPO'),
(35, '78945612', 'entrada', '2025-08-22 13:04:37', 'FALTA'),
(36, '78945612', 'entrada', '2025-08-22 10:05:26', 'FALTA'),
(37, '78945612', 'entrada', '2025-08-22 10:05:28', 'FALTA'),
(38, '78945612', 'entrada', '2025-08-22 10:08:52', 'FALTA'),
(39, '78945612', 'salida', '2025-08-22 17:12:54', 'A_TIEMPO'),
(40, '78945612', 'salida', '2025-08-22 17:38:40', 'A_TIEMPO'),
(41, '78945612', 'entrada', '2025-08-22 17:39:22', 'FALTA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargo`
--

CREATE TABLE `cargo` (
  `id_cargo` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `cargo`
--

INSERT INTO `cargo` (`id_cargo`, `nombre`) VALUES
(1, 'Analista Profesional'),
(2, 'Servicios Generales'),
(3, 'Jefe de Oficina'),
(4, 'Limpieza'),
(5, 'Supervisor Especializado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direccion`
--

CREATE TABLE `direccion` (
  `id_direccion` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `direccion`
--

INSERT INTO `direccion` (`id_direccion`, `nombre`) VALUES
(1, 'Direccion de Planeación y Archivo'),
(2, 'Direccion de Administracion'),
(3, 'Direccion de Tecnologia e Informacion'),
(4, 'Direccion de Residuos'),
(5, 'Direccion de Fomento y Proteccion Forestal'),
(6, 'Direccion de Cambio Climatico'),
(7, 'Direccion de Manejo y Productividad Forestal'),
(8, 'Direccion de Impacto y Riesgo Ambiental'),
(9, 'Direccion de Educacion Ambiental'),
(10, 'Direccion de Ordenamiento'),
(11, 'Direccion de Relaciones Publicas'),
(12, 'Direccion Juridica y Transparencia');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleado`
--

CREATE TABLE `empleado` (
  `id_empleado` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `apellido` varchar(255) DEFAULT NULL,
  `dni` varchar(255) NOT NULL,
  `cargo` int(11) NOT NULL,
  `direccion` int(11) NOT NULL,
  `subsecretaria` int(11) NOT NULL,
  `usuario` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `id_horario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `empleado`
--

INSERT INTO `empleado` (`id_empleado`, `nombre`, `apellido`, `dni`, `cargo`, `direccion`, `subsecretaria`, `usuario`, `password`, `is_admin`, `id_horario`) VALUES
(1, 'juan manuel', 'quispe chocce', '78945612', 1, 2, 1, 'juanmanuel', '202cb962ac59075b964b07152d234b70', 1, 2),
(2, 'josep', 'vega chavez', '77441122', 2, 3, 3, 'josepvega', '202cb962ac59075b964b07152d234b70', 0, 1),
(4, 'maria', 'molina gutierrez', '00225566', 5, 3, 4, 'mariamolina', '202cb962ac59075b964b07152d234b70', 0, 1),
(6, 'ismael', 'sandoval', '74433542', 4, 3, 5, 'ismaelsandoval', '202cb962ac59075b964b07152d234b70', 0, 1),
(11, 'prueba', 'prueba', '00225588', 1, 3, 6, 'prueba', '202cb962ac59075b964b07152d234b70', 0, 1),
(14, 'admin', 'sandoval', '7894561212', 2, 2, 3, 'asmin', '202cb962ac59075b964b07152d234b70', 0, 1),
(16, 'ismaelito', 'quispe chocce', '123123', 2, 1, 1, 'adsasddsa', '202cb962ac59075b964b07152d234b70', 0, 1),
(17, 'Carlos', 'Gomez', '2395', 1, 1, 1, 'carlos.gomez', '202cb962ac59075b964b07152d234b70', 1, 1),
(18, 'Maria', 'Perez', '2396', 1, 2, 2, 'maria.perez', '202cb962ac59075b964b07152d234b70', 0, 1),
(19, 'Juan', 'Lopez', '2397', 1, 5, 1, 'juan.lopez', '202cb962ac59075b964b07152d234b70', 0, 1),
(20, 'Ana', 'Martinez', '2398', 1, 1, 2, 'ana.martinez', '202cb962ac59075b964b07152d234b70', 0, 1),
(21, 'Luis', 'Garcia', '2399', 2, 2, 1, 'luis.garcia', '202cb962ac59075b964b07152d234b70', 0, 1),
(22, 'Sofia', 'Sanchez', '2400', 1, 5, 3, 'sofia.sanchez', '202cb962ac59075b964b07152d234b70', 0, 2),
(23, 'Pedro', 'Ramirez', '2401', 2, 4, 3, 'pedro.ramirez', '202cb962ac59075b964b07152d234b70', 1, 1),
(24, 'Lucia', 'Torres', '2402', 3, 3, 3, 'lucia.torres', '202cb962ac59075b964b07152d234b70', 1, 1),
(25, 'Miguel', 'Diaz', '2403', 3, 4, 1, 'miguel.diaz', '202cb962ac59075b964b07152d234b70', 1, 1),
(26, 'Laura', 'Fernandez', '2404', 1, 4, 2, 'laura.fernandez', '202cb962ac59075b964b07152d234b70', 1, 1),
(27, 'Jorge', 'Ruiz', '2405', 2, 2, 3, 'jorge.ruiz', '202cb962ac59075b964b07152d234b70', 1, 1),
(28, 'Elena', 'Morales', '2406', 1, 3, 1, 'elena.morales', '202cb962ac59075b964b07152d234b70', 0, 1),
(29, 'Diego', 'Castro', '2407', 2, 2, 2, 'diego.castro', '202cb962ac59075b964b07152d234b70', 0, 1),
(30, 'Paula', 'Vargas', '2408', 3, 1, 3, 'paula.vargas', '202cb962ac59075b964b07152d234b70', 0, 1),
(31, 'Raul', 'Mendoza', '2409', 4, 1, 1, 'raul.mendoza', '202cb962ac59075b964b07152d234b70', 0, 1),
(35, 'Juan', 'Pérez', '12345678', 1, 1, 1, 'jperez', '482c811da5d5b4bc6d497ffa98491e38', 0, 1),
(36, 'María', 'González', '87654321', 2, 2, 2, 'mgonzalez', '96b33694c4bb7dbd07391e0be54745fb', 0, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa`
--

CREATE TABLE `empresa` (
  `id_empresa` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `ubicacion` varchar(255) DEFAULT NULL,
  `ruc` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `empresa`
--

INSERT INTO `empresa` (`id_empresa`, `nombre`, `telefono`, `ubicacion`, `ruc`) VALUES
(1, 'Informatica Studios', '925310896', 'av. los incas', '78945612378');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios`
--

CREATE TABLE `horarios` (
  `id_horario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `hora_entrada` time NOT NULL,
  `hora_salida` time NOT NULL,
  `tolerancia_entrada` int(11) NOT NULL DEFAULT 10,
  `limite_retardo` int(11) NOT NULL DEFAULT 20,
  `descripcion` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `horarios`
--

INSERT INTO `horarios` (`id_horario`, `nombre`, `hora_entrada`, `hora_salida`, `tolerancia_entrada`, `limite_retardo`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, 'Horario Default', '09:00:00', '17:00:00', 10, 20, 'Horario estándar de 9 AM a 5 PM', '2025-08-22 18:17:44', '2025-08-22 19:43:32'),
(2, 'Horario Matutino', '07:00:00', '14:30:00', 10, 20, 'Horario matutino de 7 AM a 2:30 PM', '2025-08-22 18:17:44', '2025-08-22 18:17:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subsecretaria`
--

CREATE TABLE `subsecretaria` (
  `id_subsecretaria` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `subsecretaria`
--

INSERT INTO `subsecretaria` (`id_subsecretaria`, `nombre`) VALUES
(1, 'Subsecretaria de Cultura Politica Ambiental Planeacion y Mejora Regulatoria de Archivo'),
(2, 'Subsecretaria de Gestion y Proteccion Ambiental'),
(3, 'Subsecretaria de Desarrollo Sostenible y Cambio Climatico');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD PRIMARY KEY (`id_asistencia`),
  ADD KEY `fk2` (`dni`);

--
-- Indices de la tabla `cargo`
--
ALTER TABLE `cargo`
  ADD PRIMARY KEY (`id_cargo`);

--
-- Indices de la tabla `direccion`
--
ALTER TABLE `direccion`
  ADD PRIMARY KEY (`id_direccion`);

--
-- Indices de la tabla `empleado`
--
ALTER TABLE `empleado`
  ADD PRIMARY KEY (`id_empleado`),
  ADD UNIQUE KEY `dni_unique` (`dni`),
  ADD KEY `fk1` (`cargo`),
  ADD KEY `id_horario` (`id_horario`);

--
-- Indices de la tabla `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`id_empresa`);

--
-- Indices de la tabla `horarios`
--
ALTER TABLE `horarios`
  ADD PRIMARY KEY (`id_horario`);

--
-- Indices de la tabla `subsecretaria`
--
ALTER TABLE `subsecretaria`
  ADD PRIMARY KEY (`id_subsecretaria`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  MODIFY `id_asistencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de la tabla `cargo`
--
ALTER TABLE `cargo`
  MODIFY `id_cargo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `direccion`
--
ALTER TABLE `direccion`
  MODIFY `id_direccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `empleado`
--
ALTER TABLE `empleado`
  MODIFY `id_empleado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `empresa`
--
ALTER TABLE `empresa`
  MODIFY `id_empresa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `horarios`
--
ALTER TABLE `horarios`
  MODIFY `id_horario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `subsecretaria`
--
ALTER TABLE `subsecretaria`
  MODIFY `id_subsecretaria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD CONSTRAINT `fk2` FOREIGN KEY (`dni`) REFERENCES `empleado` (`dni`) ON DELETE CASCADE;

--
-- Filtros para la tabla `empleado`
--
ALTER TABLE `empleado`
  ADD CONSTRAINT `empleado_ibfk_1` FOREIGN KEY (`id_horario`) REFERENCES `horarios` (`id_horario`),
  ADD CONSTRAINT `fk1` FOREIGN KEY (`cargo`) REFERENCES `cargo` (`id_cargo`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
