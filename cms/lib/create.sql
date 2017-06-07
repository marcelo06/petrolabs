-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Versión del servidor: 5.5.16
-- Versión de PHP: 5.3.8

--
-- Base de datos: `easywebsite`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `accion`
--

CREATE TABLE `accion` (
  `acc_pk` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `acc_nombre` varchar(100) NOT NULL,
  PRIMARY KEY (`acc_pk`),
  UNIQUE KEY `acc_nombre` (`acc_nombre`)
) ENGINE=MyISAM;
-- SEPARADOR

--
-- Volcado de datos para la tabla `accion`
--

INSERT INTO `accion` (`acc_pk`, `acc_nombre`) VALUES
(1, 'Configurar base de datos'),
(2, 'Configurar CMS'),
(3, 'Configurar módulos'),
(4, 'Configurar página (Global)'),
(5, 'Configurar Slider'),
(6, 'Gestionar perfiles de usuario'),
(7, 'Gestionar usuarios'),
(8, 'Crear página'),
(9, 'Borrar página'),
(10, 'Crear texto (Página)'),
(11, 'Configurar página');
-- SEPARADOR

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bloque`
--

CREATE TABLE `bloque` (
  `blo_pk` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `sec_pk` smallint(5) unsigned NOT NULL,
  `blo_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`blo_pk`),
  KEY `IDX_bloque1` (`sec_pk`)
) ENGINE=MyISAM;
-- SEPARADOR

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bloque_txt`
--

CREATE TABLE `bloque_txt` (
  `btx_pk` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `blo_pk` mediumint(8) unsigned NOT NULL,
  `idi_pk` tinyint(3) unsigned NOT NULL,
  `btx_contenido` text,
  PRIMARY KEY (`btx_pk`),
  KEY `IDX_bloque_txt1` (`blo_pk`)
) ENGINE=MyISAM;
-- SEPARADOR

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `nombre_sitio` varchar(100) DEFAULT NULL,
  `correo_contacto` varchar(100) DEFAULT NULL,
  `twitter` varchar(50) DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `analytics` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- SEPARADOR

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`nombre_sitio`, `correo_contacto`, `twitter`, `facebook`, `analytics`) VALUES
('easyWebsite :: ', NULL, NULL, NULL, NULL);
-- SEPARADOR

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `idioma`
--

CREATE TABLE `idioma` (
  `idi_pk` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `idi_locale` varchar(20) NOT NULL,
  `idi_txt` varchar(40) NOT NULL,
  `idi_img` varchar(40) DEFAULT NULL,
  `idi_estado` char(1) NOT NULL DEFAULT '0',
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_keywords` varchar(255) DEFAULT NULL,
  `seo_description` text,
  PRIMARY KEY (`idi_pk`),
  UNIQUE KEY `idi_locale` (`idi_locale`)
) ENGINE=MyISAM;
-- SEPARADOR

--
-- Volcado de datos para la tabla `idioma`
--

INSERT INTO `idioma` (`idi_pk`, `idi_locale`, `idi_txt`, `idi_img`, `idi_estado`, `seo_title`, `seo_keywords`, `seo_description`) VALUES
(1, 'ES', 'Español', NULL, '1', NULL, NULL, NULL),
(2, 'EN', 'English', NULL, '0', NULL, NULL, NULL);
-- SEPARADOR

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `log`
--

CREATE TABLE `log` (
  `log_pk` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usu_pk` smallint(5) unsigned NOT NULL,
  `acc_pk` smallint(5) unsigned NOT NULL,
  `log_fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `log_descripcion` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`log_pk`),
  KEY `IDX_log1` (`usu_pk`),
  KEY `IDX_log2` (`acc_pk`)
) ENGINE=MyISAM;
-- SEPARADOR

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modulo_noticias`
--

CREATE TABLE `modulo_noticias` (
  `pk` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `archivo` varchar(100) NOT NULL,
  `orden` char(1) NOT NULL COMMENT '1: nombre (a-z); 2: más nuevos primero; 3: más nuevos al final; 4: activar campo orden.',
  `fecha` char(1) NOT NULL COMMENT 'Seleccionar fecha. 1: Si; 0: No.',
  `calendario` char(1) NOT NULL DEFAULT '0' COMMENT 'Activar calendario. 1: Si; 0: No.',
  `resumen` char(1) NOT NULL COMMENT 'Activar resumen. 1: Si; 0: No.',
  `editor` char(1) NOT NULL COMMENT 'Activar editor en el contenido. 1: Si; 0: No.',
  `tamano_img` varchar(50) NOT NULL,
  `limite_img` tinyint(3) unsigned NOT NULL,
  `adjuntos` char(1) NOT NULL COMMENT 'Permitir archivos adjuntos. 1: Si; 0: No.',
  `limite_adj` tinyint(3) unsigned NOT NULL,
  `paginador` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`pk`)
) ENGINE=MyISAM;
-- SEPARADOR

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `noa_txt`
--

CREATE TABLE `noa_txt` (
  `noa_pk` int(10) unsigned NOT NULL,
  `idi_pk` tinyint(3) unsigned NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`idi_pk`,`noa_pk`),
  KEY `fk_noa_txt_noticia_adjunto1` (`noa_pk`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- SEPARADOR

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `noi_txt`
--

CREATE TABLE `noi_txt` (
  `noi_pk` int(10) unsigned NOT NULL,
  `idi_pk` tinyint(3) unsigned NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`idi_pk`,`noi_pk`),
  KEY `fk_noi_txt_noticia_img1` (`noi_pk`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- SEPARADOR

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `noticia`
--

CREATE TABLE `noticia` (
  `not_pk` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pk` tinyint(3) unsigned NOT NULL,
  `not_fecha` date NOT NULL,
  PRIMARY KEY (`not_pk`),
  KEY `fk_noticia_modulo_noticias1` (`pk`)
) ENGINE=MyISAM;
-- SEPARADOR

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `noticia_adjunto`
--

CREATE TABLE `noticia_adjunto` (
  `noa_pk` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `not_pk` int(10) unsigned NOT NULL,
  `noa_archivo` varchar(100) NOT NULL,
  PRIMARY KEY (`noa_pk`),
  KEY `IDX_noticia_adjunto1` (`not_pk`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- SEPARADOR

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `noticia_img`
--

CREATE TABLE `noticia_img` (
  `noi_pk` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `not_pk` int(10) unsigned NOT NULL,
  `noi_archivo` varchar(100) NOT NULL,
  PRIMARY KEY (`noi_pk`),
  KEY `IDX_noticia_img1` (`not_pk`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- SEPARADOR

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `noticia_txt`
--

CREATE TABLE `noticia_txt` (
  `ntx_pk` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `not_pk` int(10) unsigned NOT NULL,
  `idi_pk` tinyint(3) unsigned NOT NULL,
  `ntx_titulo` varchar(255) NOT NULL,
  `ntx_resumen` text,
  `ntx_contenido` text,
  `ntx_seo_title` varchar(255) DEFAULT NULL,
  `ntx_seo_keywords` varchar(255) DEFAULT NULL,
  `ntx_seo_description` text,
  PRIMARY KEY (`ntx_pk`),
  KEY `IDX_noticia_txt1` (`not_pk`)
) ENGINE=MyISAM;
-- SEPARADOR

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfil`
--

CREATE TABLE `perfil` (
  `per_pk` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `per_nombre` varchar(100) NOT NULL,
  `per_estado` char(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`per_pk`),
  UNIQUE KEY `per_nombre` (`per_nombre`)
) ENGINE=MyISAM;
-- SEPARADOR

--
-- Volcado de datos para la tabla `perfil`
--

INSERT INTO `perfil` (`per_pk`, `per_nombre`, `per_estado`) VALUES
(1, 'Haggen IT', '1');
-- SEPARADOR

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permiso`
--

CREATE TABLE `permiso` (
  `prm_pk` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `per_pk` smallint(5) unsigned NOT NULL,
  `acc_pk` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`prm_pk`),
  KEY `IDX_permiso1` (`per_pk`),
  KEY `IDX_permiso2` (`acc_pk`)
) ENGINE=MyISAM;
-- SEPARADOR

--
-- Volcado de datos para la tabla `permiso`
--

INSERT INTO `permiso` (`prm_pk`, `per_pk`, `acc_pk`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 3),
(4, 1, 4),
(5, 1, 5),
(6, 1, 6),
(7, 1, 7),
(8, 1, 8),
(9, 1, 9),
(10, 1, 10),
(11, 1, 11);
-- SEPARADOR

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sea_txt`
--

CREATE TABLE `sea_txt` (
  `sea_pk` int(10) unsigned NOT NULL,
  `idi_pk` tinyint(3) unsigned NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`idi_pk`,`sea_pk`),
  KEY `fk_sea_txt_seccion_adjunto1` (`sea_pk`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- SEPARADOR

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seccion`
--

CREATE TABLE `seccion` (
  `sec_pk` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `sec_pk_origen` smallint(5) unsigned DEFAULT NULL,
  `sec_orden` tinyint(3) unsigned NOT NULL,
  `sec_index` char(1) NOT NULL DEFAULT '0',
  `sec_imgconf` varchar(50) DEFAULT NULL,
  `sec_lim_img` tinyint(3) unsigned NOT NULL,
  `sec_archivo` varchar(100) NOT NULL,
  `sec_contacto` char(1) NOT NULL DEFAULT '0',
  `sec_slider` char(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`sec_pk`),
  KEY `IDX_seccion2` (`sec_pk_origen`)
) ENGINE=MyISAM;
-- SEPARADOR

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seccion_adjunto`
--

CREATE TABLE `seccion_adjunto` (
  `sea_pk` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sec_pk` smallint(5) unsigned NOT NULL,
  `sea_archivo` varchar(100) NOT NULL,
  `sea_id` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`sea_pk`),
  KEY `IDX_seccion_adjunto1` (`sec_pk`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- SEPARADOR

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seccion_img`
--

CREATE TABLE `seccion_img` (
  `sei_pk` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sec_pk` smallint(5) unsigned NOT NULL,
  `sei_archivo` varchar(100) NOT NULL,
  PRIMARY KEY (`sei_pk`),
  KEY `IDX_seccion_img1` (`sec_pk`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- SEPARADOR

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seccion_txt`
--

CREATE TABLE `seccion_txt` (
  `stx_pk` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `sec_pk` smallint(5) unsigned NOT NULL,
  `idi_pk` tinyint(3) unsigned NOT NULL,
  `stx_nombre` varchar(100) NOT NULL,
  `stx_seo_title` varchar(255) DEFAULT NULL,
  `stx_seo_keywords` varchar(255) DEFAULT NULL,
  `stx_seo_description` text,
  PRIMARY KEY (`stx_pk`),
  KEY `IDX_seccion_txt1` (`sec_pk`)
) ENGINE=MyISAM;
-- SEPARADOR

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seccion_video`
--

CREATE TABLE `seccion_video` (
  `sev_pk` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `sec_pk` smallint(5) unsigned NOT NULL,
  `sev_codigo` text,
  `sev_id` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`sev_pk`),
  KEY `IDX_seccion_video1` (`sec_pk`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- SEPARADOR

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sei_txt`
--

CREATE TABLE `sei_txt` (
  `sei_pk` int(10) unsigned NOT NULL,
  `idi_pk` tinyint(3) unsigned NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`sei_pk`,`idi_pk`),
  KEY `fk_sei_txt_seccion_img1` (`sei_pk`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- SEPARADOR

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sev_txt`
--

CREATE TABLE `sev_txt` (
  `sev_pk` mediumint(8) unsigned NOT NULL,
  `idi_pk` tinyint(3) unsigned NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`idi_pk`,`sev_pk`),
  KEY `fk_sev_txt_seccion_video1` (`sev_pk`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- SEPARADOR

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `usu_pk` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `per_pk` smallint(5) unsigned DEFAULT NULL,
  `usu_login` varchar(100) NOT NULL,
  `usu_clave` varchar(40) NOT NULL,
  `usu_nombre` varchar(100) NOT NULL,
  `usu_email` varchar(100) DEFAULT NULL,
  `usu_estado` char(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`usu_pk`),
  UNIQUE KEY `usu_login` (`usu_login`),
  KEY `IDX_usuario1` (`per_pk`)
) ENGINE=MyISAM;
-- SEPARADOR

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`usu_pk`, `per_pk`, `usu_login`, `usu_clave`, `usu_nombre`, `usu_email`, `usu_estado`) VALUES
(1, 1, 'haggen', 'b4ed49748aaee3dd9809c50de79825a3', 'Haggen IT', NULL, '1');