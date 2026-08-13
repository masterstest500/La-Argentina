-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-08-2026 a las 20:05:49
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
-- Base de datos: `heladeria_bbdd`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `captaciones`
--

CREATE TABLE `captaciones` (
  `id` int(11) NOT NULL,
  `fecha_visita` date NOT NULL,
  `ruta` varchar(100) NOT NULL,
  `frecuencia` varchar(50) NOT NULL,
  `nombre_cliente` varchar(150) NOT NULL,
  `rif_cliente` varchar(20) DEFAULT NULL,
  `posicion_itinerario` varchar(100) DEFAULT NULL,
  `comentarios` text DEFAULT NULL,
  `instalacion_nevera` varchar(10) DEFAULT 'NO',
  `vendedor` varchar(100) NOT NULL,
  `formato_path` varchar(255) DEFAULT NULL,
  `acta_path` varchar(255) DEFAULT NULL,
  `rif_path` varchar(255) DEFAULT NULL,
  `cedula_path` varchar(255) DEFAULT NULL,
  `recibo_path` varchar(255) DEFAULT NULL,
  `fachada_path` varchar(255) DEFAULT NULL,
  `firma_path` varchar(255) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `captaciones`
--
ALTER TABLE `captaciones`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `captaciones`
--
ALTER TABLE `captaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
