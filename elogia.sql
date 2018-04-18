-- phpMyAdmin SQL Dump
-- version 4.4.14
-- http://www.phpmyadmin.net
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 19-04-2018 a las 00:30:50
-- Versión del servidor: 5.6.26
-- Versión de PHP: 5.6.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `elogia`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hashtags`
--

CREATE TABLE IF NOT EXISTS `hashtags` (
  `idTweet` varchar(64) COLLATE utf8_bin NOT NULL,
  `texto` varchar(64) COLLATE utf8_bin NOT NULL,
  `posI` int(4) NOT NULL,
  `posF` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `media`
--

CREATE TABLE IF NOT EXISTS `media` (
  `idTweet` varchar(64) COLLATE utf8_bin NOT NULL,
  `tipo` varchar(32) COLLATE utf8_bin NOT NULL,
  `url_mostrar` varchar(120) COLLATE utf8_bin NOT NULL,
  `url` varchar(256) COLLATE utf8_bin NOT NULL,
  `ancho` int(5) NOT NULL,
  `alto` int(5) NOT NULL,
  `posI` int(4) NOT NULL,
  `posF` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `menciones`
--

CREATE TABLE IF NOT EXISTS `menciones` (
  `idTweet` varchar(64) COLLATE utf8_bin NOT NULL,
  `nombre` varchar(120) COLLATE utf8_bin NOT NULL,
  `nick` varchar(120) COLLATE utf8_bin NOT NULL,
  `posI` int(4) NOT NULL,
  `posF` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tweets`
--

CREATE TABLE IF NOT EXISTS `tweets` (
  `id` varchar(64) COLLATE utf8_bin NOT NULL,
  `fecha` datetime NOT NULL,
  `texto` text COLLATE utf8_bin NOT NULL,
  `fuente` varchar(256) COLLATE utf8_bin NOT NULL,
  `respuestas` int(11) NOT NULL,
  `retweets` int(11) NOT NULL,
  `favoritos` int(11) NOT NULL,
  `idioma` varchar(4) COLLATE utf8_bin NOT NULL,
  `resp_idUsuario` varchar(64) COLLATE utf8_bin NOT NULL,
  `resp_nickUsuario` varchar(120) COLLATE utf8_bin NOT NULL,
  `coord_long` float NOT NULL,
  `coord_lat` float NOT NULL,
  `idUsuario` varchar(64) COLLATE utf8_bin NOT NULL,
  `nombreUsuario` varchar(120) COLLATE utf8_bin NOT NULL,
  `nick` varchar(120) COLLATE utf8_bin NOT NULL,
  `imgUsuario` varchar(500) COLLATE utf8_bin NOT NULL,
  `sentimiento` varchar(36) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `urls`
--

CREATE TABLE IF NOT EXISTS `urls` (
  `idTweet` varchar(64) COLLATE utf8_bin NOT NULL,
  `url_mostrar` varchar(120) COLLATE utf8_bin NOT NULL,
  `url` varchar(256) COLLATE utf8_bin NOT NULL,
  `posI` int(4) NOT NULL,
  `posF` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `hashtags`
--
ALTER TABLE `hashtags`
  ADD KEY `idTweet` (`idTweet`);

--
-- Indices de la tabla `media`
--
ALTER TABLE `media`
  ADD KEY `idTweet` (`idTweet`);

--
-- Indices de la tabla `menciones`
--
ALTER TABLE `menciones`
  ADD KEY `idTweet` (`idTweet`);

--
-- Indices de la tabla `tweets`
--
ALTER TABLE `tweets`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `urls`
--
ALTER TABLE `urls`
  ADD KEY `idTweet` (`idTweet`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
