-- base.sql

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Base de datos, `local_colegio`
--
CREATE DATABASE IF NOT EXISTS `local_colegio` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `local_colegio`;

--
-- Estructura de tabla para la tabla `permisos`
--
DROP TABLE IF EXISTS `permisos`;
CREATE TABLE IF NOT EXISTS `permisos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `permiso` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `llave` varchar(38) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `editable` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Estructura de tabla para la tabla `roles`
--
DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `editable` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Estructura de tabla para la tabla `usuarios`
--
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `clave` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` int NOT NULL,
  `codigo` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activado` tinyint(1) DEFAULT 0,,
  `enlinea` tinyint(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  `user_id` int DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Estructura de tabla para la tabla `perfiles`
--
DROP TABLE IF EXISTS `perfiles`;
CREATE TABLE IF NOT EXISTS `perfiles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `alias` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `apellido` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rut` varchar(13) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `celular` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `region` int DEFAULT 0,
  `provincia` int DEFAULT 0,
  `comuna` int DEFAULT 0,
  `resumen` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `imagen` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Estructura de tabla para la tabla `permiso_role`
--
DROP TABLE IF EXISTS `permiso_role`;
CREATE TABLE IF NOT EXISTS `permiso_role` (
  `idRole` int NOT NULL,
  `idPermiso` int NOT NULL,
  `valor` tinyint(4) NOT NULL,
  UNIQUE KEY `role` (`idRole`,`idPermiso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Estructura de tabla para la tabla `permiso_usuario`
--
DROP TABLE IF EXISTS `permiso_usuario`;
CREATE TABLE IF NOT EXISTS `permiso_usuario` (
  `idUsuario` int NOT NULL,
  `idPermiso` int NOT NULL,
  `valor` tinyint(4) NOT NULL,
  UNIQUE KEY `permiso` (`idUsuario`,`idPermiso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Filtros para la tabla `permiso_role`
--
ALTER TABLE `permiso_role`
  ADD CONSTRAINT `fk_permisos_role` FOREIGN KEY (`idPermiso`) REFERENCES `permisos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_roles` FOREIGN KEY (`idRole`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `permiso_usuario`
--
ALTER TABLE `permiso_usuario`
  ADD CONSTRAINT `fk_permisos_usuarios` FOREIGN KEY (`idPermiso`) REFERENCES `permisos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuarios` FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Volcado de datos para la tabla `perfiles`
--
INSERT INTO `perfiles` (`id`, `alias`, `nombre`, `apellido`, `rut`, `telefono`, `celular`, `direccion`, `region`, `provincia`, `comuna`, `resumen`, `imagen`, `user_id`) VALUES
(1, 'tesis', 'Juan Carlos', 'Marchant hernandez', '13114010-k', '452223325', '961881674', 'Ecuador 2278', 0, 0, 0, 'Administrador de BlockPC.\rCreador del Framework BlockPc', 'tesis.png', 1),
(2, 'admin', 'Juanito', 'Perez', '11111111-1', '99999999', '', '', 0, 0, 0, 'Administrador general del sitio', 'admin.png', 2),
(3, 'usuario', 'Usuario', 'Para Pruebas', '11111111-1', '11111111', NULL, '', 0, 0, 0, 'Sin Informacion', 'usuario.png', 3);

--
-- Volcado de datos para la tabla `usuarios`
--
INSERT INTO `usuarios` (`id`, `email`, `clave`, `role`, `created_at`, `deleted_at`, `updated_at`, `codigo`, `activado`, `enlinea`, `user_id`) VALUES
(1, 'juan.marchant@gmail.com', '$2y$10$Te1rq9dk8cbYSS64NBy3muyb8fyKIx1QhEZOAtAnjiXGwc71uIbM.', 1, '2019-11-19 14:40:13', NULL, '2020-06-09 15:32:01', '5z84ouzi6mixiqy2tnzwcqvl3', 1, 1, 0),
(2, 'administrador@mail.com', '$2y$10$fPXAF1UCv6L7CaI0VRm.b.C4dlkG7boQ6edseT.KxO02gjsvq5JTS', 2, '2019-11-19 14:40:13', NULL, '2020-06-09 15:31:54', 'V54f@.LuJal_WA', 1, 0, 0),
(3, 'usuario@mail.cl', '$2y$10$wK.Q91n/kjMRGueWsgKrOu7n3e30g5rdFtAA94QgxhWdwb5LodHsa', 3, '2019-11-19 14:40:13', '2019-11-29 21:47:07', NULL, 'iZ@0RMalZaUKfY', 0, 0, 0);

--
-- Volcado de datos para la tabla `permisos`
--
INSERT INTO `permisos` (`id`, `permiso`, `llave`, `descripcion`, `editable`) VALUES
(1, 'Administracion General', 'sudo_acces', 'Cuenta que posee todos los permisos', 0),
(2, 'Tareas de Administracion Local', 'admin_acces', 'Otorga permisos mas específicos como crear, editar y eliminar respecto a cualquier modulo del sistema', 0),
(3, 'Acceso General', 'general_acces', 'Otorga permisos basicos, entre los cuales incluyen el permiso de acceso al perfil de una cuenta.', 0);

--
-- Volcado de datos para la tabla `roles`
--
INSERT INTO `roles` (`id`, `role`, `descripcion`, `editable`) VALUES
(1, 'SuperAdministrador', 'Administración total de la aplicación', 0),
(2, 'Administrador', 'Administrador de la aplicación', 0),
(3, 'Usuario', 'Usuario de la aplicación', 0);

--
-- Volcado de datos para la tabla `permiso_role`
--
INSERT INTO `permiso_role` (`idRole`, `idPermiso`, `valor`) VALUES
(1, 1, 1), (1, 2, 1), (1, 3, 1),
(2, 1, 0), (2, 2, 1), (2, 3, 1),
(3, 2, 0), (3, 3, 1);


SET FOREIGN_KEY_CHECKS=1;
COMMIT;