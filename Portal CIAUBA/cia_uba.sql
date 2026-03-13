-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-02-2026 a las 14:20:39
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
-- Base de datos: `cia_uba`
--
CREATE DATABASE IF NOT EXISTS `cia_uba` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `cia_uba`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistentes_eventos`
--

DROP TABLE IF EXISTS `asistentes_eventos`;
CREATE TABLE `asistentes_eventos` (
  `evento_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `asistio` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asistentes_eventos`
--

INSERT INTO `asistentes_eventos` (`evento_id`, `usuario_id`, `fecha_registro`, `asistio`) VALUES
(1, 2, '2026-02-24 13:18:21', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias_foro`
--

DROP TABLE IF EXISTS `categorias_foro`;
CREATE TABLE `categorias_foro` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `posicion` int(11) DEFAULT 0,
  `activa` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias_foro`
--

INSERT INTO `categorias_foro` (`id`, `nombre`, `descripcion`, `posicion`, `activa`) VALUES
(1, 'Project Discussions', 'Talk about ongoing and proposed projects', 1, 1),
(2, 'Technical Help', 'Get help with engineering challenges', 2, 1),
(3, 'Resource Sharing', 'Share tutorials, datasheets, and useful links', 3, 1),
(4, 'Club Announcements', 'Official updates from club leadership', 4, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

DROP TABLE IF EXISTS `configuracion`;
CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL,
  `clave` varchar(50) NOT NULL,
  `valor` text DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `clave`, `valor`, `descripcion`) VALUES
(1, 'club_nombre', 'Club de Ingeniería Aplicada UBA', 'Nombre oficial del club'),
(2, 'club_email', 'rhysuba@gmail.com', 'Email de contacto'),
(3, 'club_telefono', '04248313052', 'Teléfono de contacto'),
(4, 'max_miembros', '100', 'Número máximo de miembros permitidos'),
(5, 'require_aprobacion', '1', 'Requerir aprobación admin para nuevos miembros (1=si, 0=no)');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos`
--

DROP TABLE IF EXISTS `eventos`;
CREATE TABLE `eventos` (
  `id` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo` enum('reunion','taller','hackathon','social','otro') DEFAULT 'reunion',
  `lugar` varchar(255) DEFAULT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  `organizador_id` int(11) DEFAULT NULL,
  `max_asistentes` int(11) DEFAULT 0,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `eventos`
--

INSERT INTO `eventos` (`id`, `titulo`, `descripcion`, `tipo`, `lugar`, `fecha_inicio`, `fecha_fin`, `organizador_id`, `max_asistentes`, `creado_en`) VALUES
(1, 'Taller de PCB', 'Aprende a diseñar tus propias placas', 'taller', 'Lab de Realidad Virtual', '2025-03-15 15:00:00', '2025-03-15 18:00:00', 1, 0, '2026-02-24 13:18:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `miembros_proyectos`
--

DROP TABLE IF EXISTS `miembros_proyectos`;
CREATE TABLE `miembros_proyectos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `proyecto_id` int(11) NOT NULL,
  `rol_en_proyecto` varchar(100) DEFAULT 'miembro',
  `fecha_incorporacion` date DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `miembros_proyectos`
--

INSERT INTO `miembros_proyectos` (`id`, `usuario_id`, `proyecto_id`, `rol_en_proyecto`, `fecha_incorporacion`, `activo`) VALUES
(1, 2, 1, 'desarrollador', NULL, 1),
(2, 3, 2, 'diseñadora', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `posts_foro`
--

DROP TABLE IF EXISTS `posts_foro`;
CREATE TABLE `posts_foro` (
  `id` int(11) NOT NULL,
  `tema_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `contenido` text NOT NULL,
  `es_respuesta_a` int(11) DEFAULT NULL,
  `editado` tinyint(1) DEFAULT 0,
  `editado_en` timestamp NULL DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `posts_foro`
--

INSERT INTO `posts_foro` (`id`, `tema_id`, `usuario_id`, `contenido`, `es_respuesta_a`, `editado`, `editado_en`, `creado_en`) VALUES
(1, 1, 1, 'Prueba a usar planos de tierra y vías apantalladas.', NULL, 0, NULL, '2026-02-24 13:18:21'),
(2, 1, 2, 'Gracias, lo intentaré.', NULL, 0, NULL, '2026-02-24 13:18:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyectos`
--

DROP TABLE IF EXISTS `proyectos`;
CREATE TABLE `proyectos` (
  `id` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text NOT NULL,
  `objetivos` text DEFAULT NULL,
  `estado` enum('planning','in_progress','testing','completed','cancelled') DEFAULT 'planning',
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin_estimada` date DEFAULT NULL,
  `fecha_fin_real` date DEFAULT NULL,
  `lider_id` int(11) DEFAULT NULL,
  `presupuesto_asignado` decimal(10,2) DEFAULT 0.00,
  `recursos_asignados` text DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proyectos`
--

INSERT INTO `proyectos` (`id`, `titulo`, `descripcion`, `objetivos`, `estado`, `fecha_inicio`, `fecha_fin_estimada`, `fecha_fin_real`, `lider_id`, `presupuesto_asignado`, `recursos_asignados`, `creado_en`, `actualizado_en`) VALUES
(1, 'Mouse virtual', 'Reconocimiento de manos y expresiones como herramientas de control', NULL, 'completed', NULL, NULL, NULL, 1, 0.00, NULL, '2026-02-24 13:18:21', NULL),
(2, 'Asistente virtual', 'Sistema potenciado por IA y técnicas de machine learning', NULL, 'in_progress', NULL, NULL, NULL, 2, 0.00, NULL, '2026-02-24 13:18:21', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recursos`
--

DROP TABLE IF EXISTS `recursos`;
CREATE TABLE `recursos` (
  `id` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo` enum('enlace','archivo','video','otro') DEFAULT 'enlace',
  `url` varchar(500) DEFAULT NULL,
  `archivo_ruta` varchar(255) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL,
  `proyecto_id` int(11) DEFAULT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `temas_foro`
--

DROP TABLE IF EXISTS `temas_foro`;
CREATE TABLE `temas_foro` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `contenido` text NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `visitas` int(11) DEFAULT 0,
  `fijo` tinyint(1) DEFAULT 0,
  `cerrado` tinyint(1) DEFAULT 0,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `temas_foro`
--

INSERT INTO `temas_foro` (`id`, `titulo`, `contenido`, `usuario_id`, `categoria_id`, `visitas`, `fijo`, `cerrado`, `creado_en`, `actualizado_en`) VALUES
(1, 'PCB Design Best Practices', 'Estoy trabajando en una controladora para drone y tengo problemas de integridad de señal a 2.4GHz. ¿Qué estrategias de layout os han funcionado?', 2, 1, 0, 0, 0, '2026-02-24 13:18:21', NULL),
(2, 'Fallos en impresión 3D', 'Mis piezas se desfasan a la misma altura. Ya revisé tensión de correas...', 3, 2, 0, 0, 0, '2026-02-24 13:18:21', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `carrera` varchar(100) NOT NULL,
  `intereses` text DEFAULT NULL,
  `nivel_experiencia` enum('beginner','intermediate','advanced') DEFAULT 'beginner',
  `rol` enum('user','admin') DEFAULT 'user',
  `estado` enum('pendiente','activo','inactivo') DEFAULT 'pendiente',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `cedula`, `telefono`, `email`, `username`, `password`, `carrera`, `intereses`, `nivel_experiencia`, `rol`, `estado`, `fecha_registro`, `ultimo_acceso`, `avatar`, `created_at`, `updated_at`) VALUES
(1, 'Admin Principal', 'V-12345678', '04121234567', 'admin@club.com', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ingeniería en Sistemas', NULL, 'beginner', 'admin', 'activo', '2026-02-24 13:18:21', NULL, NULL, '2026-02-24 13:18:21', NULL),
(2, 'Juan Pérez', 'V-87654321', '04149876543', 'juan@example.com', 'juanp', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ingeniería Eléctrica', NULL, 'beginner', 'user', 'activo', '2026-02-24 13:18:21', NULL, NULL, '2026-02-24 13:18:21', NULL),
(3, 'María López', 'V-11223344', '04241234567', 'maria@example.com', 'marial', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ingeniería Mecánica', NULL, 'beginner', 'user', 'pendiente', '2026-02-24 13:18:21', NULL, NULL, '2026-02-24 13:18:21', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asistentes_eventos`
--
ALTER TABLE `asistentes_eventos`
  ADD PRIMARY KEY (`evento_id`,`usuario_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `categorias_foro`
--
ALTER TABLE `categorias_foro`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clave` (`clave`);

--
-- Indices de la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organizador_id` (`organizador_id`);

--
-- Indices de la tabla `miembros_proyectos`
--
ALTER TABLE `miembros_proyectos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario_proyecto` (`usuario_id`,`proyecto_id`),
  ADD KEY `proyecto_id` (`proyecto_id`);

--
-- Indices de la tabla `posts_foro`
--
ALTER TABLE `posts_foro`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tema_id` (`tema_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `es_respuesta_a` (`es_respuesta_a`);

--
-- Indices de la tabla `proyectos`
--
ALTER TABLE `proyectos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lider_id` (`lider_id`);

--
-- Indices de la tabla `recursos`
--
ALTER TABLE `recursos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `proyecto_id` (`proyecto_id`);

--
-- Indices de la tabla `temas_foro`
--
ALTER TABLE `temas_foro`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias_foro`
--
ALTER TABLE `categorias_foro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `miembros_proyectos`
--
ALTER TABLE `miembros_proyectos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `posts_foro`
--
ALTER TABLE `posts_foro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `proyectos`
--
ALTER TABLE `proyectos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `recursos`
--
ALTER TABLE `recursos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `temas_foro`
--
ALTER TABLE `temas_foro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asistentes_eventos`
--
ALTER TABLE `asistentes_eventos`
  ADD CONSTRAINT `asistentes_eventos_ibfk_1` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `asistentes_eventos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD CONSTRAINT `eventos_ibfk_1` FOREIGN KEY (`organizador_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `miembros_proyectos`
--
ALTER TABLE `miembros_proyectos`
  ADD CONSTRAINT `miembros_proyectos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `miembros_proyectos_ibfk_2` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `posts_foro`
--
ALTER TABLE `posts_foro`
  ADD CONSTRAINT `posts_foro_ibfk_1` FOREIGN KEY (`tema_id`) REFERENCES `temas_foro` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `posts_foro_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `posts_foro_ibfk_3` FOREIGN KEY (`es_respuesta_a`) REFERENCES `posts_foro` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `proyectos`
--
ALTER TABLE `proyectos`
  ADD CONSTRAINT `proyectos_ibfk_1` FOREIGN KEY (`lider_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `recursos`
--
ALTER TABLE `recursos`
  ADD CONSTRAINT `recursos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recursos_ibfk_2` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `temas_foro`
--
ALTER TABLE `temas_foro`
  ADD CONSTRAINT `temas_foro_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `temas_foro_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_foro` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
