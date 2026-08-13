-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-08-2026 a las 23:23:13
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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `captacion_neveras`
--

CREATE TABLE `captacion_neveras` (
  `id` int(11) NOT NULL,
  `captacion_id` int(11) NOT NULL,
  `modelo_nevera` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `codigo_cliente` varchar(20) DEFAULT NULL,
  `codigo_sucursal` varchar(20) DEFAULT NULL,
  `id_ruta` int(11) DEFAULT NULL,
  `rif` varchar(20) NOT NULL,
  `nombre_negocio` varchar(150) NOT NULL,
  `direccion_fiscal` text NOT NULL,
  `persona_contacto` varchar(100) DEFAULT NULL,
  `latitud` decimal(10,8) DEFAULT NULL,
  `longitud` decimal(11,8) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `ruta_rif` varchar(255) DEFAULT NULL,
  `ruta_acta` varchar(255) DEFAULT NULL,
  `ruta_fachada` varchar(255) DEFAULT NULL,
  `tiene_nevera` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente_equipos`
--

CREATE TABLE `cliente_equipos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `modelo_nevera` varchar(50) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `fecha_asignacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL,
  `clave` varchar(50) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `clave`, `valor`, `fecha_actualizacion`) VALUES
(1, 'tasa_bcv', 755.90, '2026-08-06 14:06:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_catalogo`
--

CREATE TABLE `detalles_catalogo` (
  `id` int(11) NOT NULL,
  `codigo_producto` varchar(50) NOT NULL,
  `nombre_producto` varchar(255) DEFAULT NULL,
  `ingredientes` text DEFAULT NULL,
  `presentacion` varchar(50) DEFAULT NULL,
  `ruta_imagen` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalles_catalogo`
--

INSERT INTO `detalles_catalogo` (`id`, `codigo_producto`, `nombre_producto`, `ingredientes`, `presentacion`, `ruta_imagen`) VALUES
(1, '280451', 'CHOCOLATE TRADICIONAL 700cm3', 'Helado cremoso sabor a chocolate', '700cm3', 'img/helados/chocolate3.png'),
(2, '280448', 'FRESA TRADICIONAL  700cm3', 'Helado cremoso sabor a fresa', '700cm3', 'img/helados/fresa.png'),
(3, '280447', 'MANTECADO TRADICIONAL 700cm3', 'Helado cremoso sabor a mantecado', '700cm3', 'img/helados/mantecado.png'),
(4, '280465', 'FANTOCHE CHOCOLATE 700cm3', 'Helado cremoso sabor a chocolate con sirop de chocolate, lluvia de maní y lluvia de chocolate', '700cm3', 'img/helados/fantoche-chocolate.png'),
(5, '280463', 'FANTOCHE FRESA 700cm3', 'Helado cremoso sabor a fresa con sirop de fresa, lluvia de maní y lluvia de chocolate', '700cm3', 'img/helados/fantofresa.png'),
(6, '280461', 'MANTECADO/FRESA/CHOCOLATE 2 X1', 'Helado cremoso sabor a vainilla, fresa y chocolate', '2L', 'img/helados/trisabor.png'),
(7, '280464', 'FANTOCHE MANTECADO 700cm3', 'Helado cremoso sabor a mantecado con sirop de dulce de leche, lluvia de maní y lluvia de chocolate', '700cm3', 'img/helados/fantoche.png'),
(8, '280190', 'PINTALENGUA 56cm3 X 20 UND', 'Paleta sabor a tutti frutti', '56cm3', 'img/helados/pinta-lengua.png'),
(9, '280503', 'COOKIES AND CREAM 700cm3', 'Helado cremoso sabor a vainilla y trozos de galleta de chocolate', '700cm3', 'img/helados/cookies-and-cream.png'),
(10, '280483', 'TRAMONTANA 700cm3', 'Helado cremoso sabor a vainilla con sirop de dulce de leche y galletas de chocolate', '700cm3', 'img/helados/tramontana2.png'),
(11, '280487', 'RON PASAS 700cm3', 'Helado cremoso sabor a ron pasas', '700cm3', 'img/helados/ron-pasas.png'),
(12, '280482', 'CREMA FANTASIA 700cm3', 'Helado cremoso sabor a mantecado, merengada y cola', '700cm3', 'img/helados/fantasia.png'),
(13, '280485', 'PISTACHO 700cm3', 'Helado cremoso sabor a pistacho', '700cm3', 'img/helados/pistacho.png'),
(14, '280008', 'CHOCOLATE 4,4L', 'Intenso sabor a puro cacao cremoso', '4,4L', 'img/helados/chocolate4,4L.png'),
(15, '280017', 'COOKIES & CREAM 4,4L', 'Crema suave con trozos de galleta', '4,4L', 'img/helados/cookies-&-cream4,4L.png'),
(16, '280018', 'CREMA FANTASIA 4,4L', 'Mezcla mágica y dulce de sabores: Mantecado, Merengada y Cola', '4,4L', 'img/helados/fantasia4,4L.png'),
(17, '280044', 'MANTECADO 4,4L', 'El tradicional toque de vainilla criolla', '4,4L', 'img/helados/mantecado4,4L.png'),
(18, '280053', 'MARMOLADO 4,4L', 'Fusión perfecta de crema y chocolate', '4,4L', 'img/helados/marmolado4,4L.png'),
(19, '280060', 'PISTACHO 4,4L', 'Exótico sabor a pistacho tostado', '4,4L', 'img/helados/pistacho4,4L.png'),
(20, '280062', 'RON PASAS 4,4L', 'Crema al ron con jugosas pasas', '4,4L', 'img/helados/ronpasa4,4L.png'),
(21, '280068', 'TRAMONTANA 4,4L', 'Crema, dulce de leche y galletitas', '4,4L', 'img/helados/tramontana4,4L.png'),
(22, '280134', 'SIROP DE CHOCOLATE 4,5 kg', 'Salsa de chocolate fluida y brillante', '4,5kg', 'img/helados/sirop-chocolate4,5.png'),
(23, '280452', 'VAINILLA SOFT 4K X1', 'Base suave con aroma de vainilla', '4K', 'img/helados/soft-vainilla.png'),
(24, '280454', 'CHOCOLATE SOFT 4K X1', 'Mezcla cremosa de chocolate suave', '4K', 'img/helados/soft-chocolate.png'),
(25, '280455', 'DULCE DE LECHE SOFT 4K X1', 'Sabor tradicional a dulce de leche', '4K', 'img/helados/soft-dulce-leche.png'),
(26, '280069', 'TROMPO LOCO UVA 120cm3X20', 'Helado cremoso sabor a uva con chicle', '120cm3', 'img/helados/trompo-loco.png'),
(27, '280220', 'MAXI CREAM 65 cm3 X 20 UND', 'Paleta de mantecado cubierta de chocolate', '65cm3', 'img/helados/maxi-cream.png'),
(28, '280191', 'SUNNY CREAM 56cm3 X 20 UND', 'Paleta de vainilla con cobertura naranja', '56cm3', 'img/helados/sunny-cream.png'),
(29, '280004', 'CHANTILLY FRESA 4,4L', 'Sabroso sabor de fresa con crema', '4,4L', 'img/helados/chantillyFresa.png'),
(30, '280034', 'FRESA 4,4L', 'Helado tradicional sabor a fresa', '4,4L', 'img/helados/fresa4,4L.png'),
(31, '280135', 'SIROP DE FRESA 4,5 kg', 'Salsa de fresa fluida y brillante', '4,5kg', 'img/helados/sirop-fresa4,5.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedidos`
--

CREATE TABLE `detalle_pedidos` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `disponibilidad_inventario`
--

CREATE TABLE `disponibilidad_inventario` (
  `id` int(11) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `producto` varchar(255) NOT NULL,
  `categoria` varchar(100) NOT NULL,
  `cantidad` int(11) DEFAULT 0,
  `dias_venta` int(11) DEFAULT 0,
  `pen_liberar` int(11) DEFAULT 0,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `disponibilidad_inventario`
--

INSERT INTO `disponibilidad_inventario` (`id`, `codigo`, `producto`, `categoria`, `cantidad`, `dias_venta`, `pen_liberar`, `fecha_actualizacion`) VALUES
(2, '280190', 'PINTALENGUA 56cm3 X 20 UND', 'INDIVIDUAL', 3, 4, 0, '2026-08-04 20:15:55'),
(3, '280069', 'TROMPO LOCO UVA 120cm3X20', 'INDIVIDUAL', 0, 0, 0, '2026-08-06 19:05:45'),
(4, '280220', 'MAXI CREAM 65 cm3 X 20 UND', 'INDIVIDUAL', 0, 0, 0, '2026-08-06 19:05:45'),
(5, '280191', 'SUNNY CREAM 56cm3 X 20 UND', 'INDIVIDUAL', 0, 0, 0, '2026-08-06 19:05:45'),
(6, '280461', 'MANTECADO/FRESA/CHOCOLATE 2 X1', 'HOGAR', 2013, 22, 0, '2026-08-04 20:15:55'),
(7, '280451', 'CHOCOLATE TRADICIONAL 700cm3', 'HOGAR', 3131, 19, 0, '2026-08-04 20:15:55'),
(8, '280448', 'FRESA TRADICIONAL  700cm3', 'HOGAR', 1809, 20, 0, '2026-08-04 20:15:55'),
(9, '280447', 'MANTECADO TRADICIONAL 700cm3', 'HOGAR', 3074, 19, 0, '2026-08-04 20:15:55'),
(10, '280465', 'FANTOCHE CHOCOLATE 700cm3', 'HOGAR', 2575, 18, 0, '2026-08-04 20:15:55'),
(11, '280463', 'FANTOCHE FRESA 700cm3', 'HOGAR', 1479, 19, 0, '2026-08-04 20:15:55'),
(12, '280464', 'FANTOCHE MANTECADO 700cm3', 'HOGAR', 1994, 17, 0, '2026-08-04 20:15:55'),
(13, '280503', 'COOKIES AND CREAM 700cm3', 'HOGAR', 1076, 9, 2532, '2026-08-04 20:15:55'),
(14, '280483', 'TRAMONTANA 700cm3', 'HOGAR', 1190, 32, 2246, '2026-08-04 20:15:55'),
(15, '280487', 'RON PASAS 700cm3', 'HOGAR', 3375, 64, 0, '2026-08-04 20:15:55'),
(16, '280482', 'CREMA FANTASIA 700cm3', 'HOGAR', 654, 20, 0, '2026-08-04 20:15:55'),
(17, '280485', 'PISTACHO 700cm3', 'HOGAR', 2212, 33, 1529, '2026-08-04 20:15:55'),
(18, '280004', 'CHANTILLY FRESA 4,4L', 'COPA', 0, 0, 0, '2026-08-06 19:05:45'),
(19, '280008', 'CHOCOLATE 4,4L', 'COPA', 243, 27, 0, '2026-08-04 20:15:55'),
(20, '280017', 'COOKIES & CREAM 4,4L', 'COPA', 396, 40, 266, '2026-08-04 20:15:55'),
(21, '280018', 'CREMA FANTASIA 4,4L', 'COPA', 196, 59, 0, '2026-08-04 20:15:55'),
(22, '280034', 'FRESA 4,4L', 'COPA', 0, 0, 0, '2026-08-06 19:05:45'),
(23, '280044', 'MANTECADO 4,4L', 'COPA', 952, 25, 0, '2026-08-04 20:15:55'),
(24, '280053', 'MARMOLADO 4,4L', 'COPA', 79, 33, 0, '2026-08-04 20:15:55'),
(25, '280060', 'PISTACHO 4,4L', 'COPA', 22, 4, 352, '2026-08-04 20:15:55'),
(26, '280062', 'RON PASAS 4,4L', 'COPA', 96, 43, 133, '2026-08-04 20:15:55'),
(27, '280068', 'TRAMONTANA 4,4L', 'COPA', 24, 4, 224, '2026-08-04 20:15:55'),
(28, '280134', 'SIROP DE CHOCOLATE 4,5 kg', 'COPA', 57, 28, 0, '2026-08-04 20:15:55'),
(29, '280135', 'SIROP DE FRESA 4,5 kg', 'COPA', 0, 0, 0, '2026-08-06 19:05:45'),
(30, '280452', 'VAINILLA SOFT 4K X1', 'COPA', 4773, 5, 2371, '2026-08-04 20:15:55'),
(31, '280454', 'CHOCOLATE SOFT 4K X1', 'COPA', 38, 1, 807, '2026-08-04 20:15:55'),
(32, '280455', 'DULCE DE LECHE SOFT 4K X1', 'COPA', 323, 2, 0, '2026-08-04 20:15:55'),
(35, '280190', 'PINTALENGUA 56cm3 X 20 UND', 'INDIVIDUAL', 3, 4, 0, '2026-08-07 16:31:44'),
(36, '280069', 'TROMPO LOCO UVA 120cm3X20', 'INDIVIDUAL', 0, 0, 0, '2026-08-07 16:49:31'),
(37, '280220', 'MAXI CREAM 65 cm3 X 20 UND', 'INDIVIDUAL', 0, 0, 0, '2026-08-07 16:49:31'),
(38, '280191', 'SUNNY CREAM 56cm3 X 20 UND', 'INDIVIDUAL', 0, 0, 0, '2026-08-07 16:49:31'),
(39, '280461', 'MANTECADO/FRESA/CHOCOLATE 2 X1', 'HOGAR', 2013, 22, 0, '2026-08-07 16:50:31'),
(40, '280451', 'CHOCOLATE TRADICIONAL 700cm3', 'HOGAR', 3131, 19, 0, '2026-08-07 16:31:44'),
(41, '280448', 'FRESA TRADICIONAL  700cm3', 'HOGAR', 1809, 20, 0, '2026-08-07 16:31:44'),
(42, '280447', 'MANTECADO TRADICIONAL 700cm3', 'HOGAR', 3074, 19, 0, '2026-08-07 16:31:44'),
(43, '280465', 'FANTOCHE CHOCOLATE 700cm3', 'HOGAR', 2575, 18, 0, '2026-08-07 16:31:44'),
(44, '280463', 'FANTOCHE FRESA 700cm3', 'HOGAR', 1479, 19, 0, '2026-08-07 16:31:44'),
(45, '280464', 'FANTOCHE MANTECADO 700cm3', 'HOGAR', 1994, 17, 0, '2026-08-07 16:31:44'),
(46, '280503', 'COOKIES AND CREAM 700cm3', 'HOGAR', 1076, 9, 2532, '2026-08-07 16:31:44'),
(47, '280483', 'TRAMONTANA 700cm3', 'HOGAR', 1190, 32, 2246, '2026-08-07 16:31:44'),
(48, '280487', 'RON PASAS 700cm3', 'HOGAR', 3375, 64, 0, '2026-08-07 16:31:44'),
(49, '280482', 'CREMA FANTASIA 700cm3', 'HOGAR', 654, 20, 0, '2026-08-07 16:31:44'),
(50, '280485', 'PISTACHO 700cm3', 'HOGAR', 2212, 33, 1529, '2026-08-07 16:31:44'),
(51, '280004', 'CHANTILLY FRESA 4,4L', 'COPA', 0, 0, 0, '2026-08-07 16:49:31'),
(52, '280008', 'CHOCOLATE 4,4L', 'COPA', 243, 27, 0, '2026-08-07 16:31:44'),
(53, '280017', 'COOKIES & CREAM 4,4L', 'COPA', 396, 40, 266, '2026-08-07 16:31:44'),
(54, '280018', 'CREMA FANTASIA 4,4L', 'COPA', 196, 59, 0, '2026-08-07 16:31:44'),
(55, '280034', 'FRESA 4,4L', 'COPA', 0, 0, 0, '2026-08-07 16:49:31'),
(56, '280044', 'MANTECADO 4,4L', 'COPA', 952, 25, 0, '2026-08-07 16:31:44'),
(57, '280053', 'MARMOLADO 4,4L', 'COPA', 79, 33, 0, '2026-08-07 16:31:44'),
(58, '280060', 'PISTACHO 4,4L', 'COPA', 22, 4, 352, '2026-08-07 16:31:44'),
(59, '280062', 'RON PASAS 4,4L', 'COPA', 96, 43, 133, '2026-08-07 16:31:44'),
(60, '280068', 'TRAMONTANA 4,4L', 'COPA', 24, 4, 224, '2026-08-07 16:31:44'),
(61, '280134', 'SIROP DE CHOCOLATE 4,5 kg', 'COPA', 57, 28, 0, '2026-08-07 16:31:44'),
(62, '280135', 'SIROP DE FRESA 4,5 kg', 'COPA', 0, 0, 0, '2026-08-07 16:49:31'),
(63, '280452', 'VAINILLA SOFT 4K X1', 'COPA', 4773, 5, 2371, '2026-08-07 16:31:44'),
(64, '280454', 'CHOCOLATE SOFT 4K X1', 'COPA', 38, 1, 807, '2026-08-07 16:31:44'),
(65, '280455', 'DULCE DE LECHE SOFT 4K X1', 'COPA', 323, 2, 0, '2026-08-07 16:31:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `sucursal_id` int(11) DEFAULT NULL,
  `vendedor` varchar(100) NOT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fecha_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(20) DEFAULT 'Completado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `sabor` varchar(100) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `codigo`, `sabor`, `precio`, `fecha_actualizacion`) VALUES
(1, '280190', 'PINTALENGUA 56cm3 X 20 UND', 5.00, '2026-08-07 14:48:12'),
(2, '280069', 'TROMPO LOCO UVA 120cm3X20', 5.00, '2026-08-07 14:48:13'),
(3, '280220', 'MAXI CREAM 65 cm3 X 20 UND', 5.00, '2026-08-07 14:48:13'),
(4, '280191', 'SUNNY CREAM 56cm3 X 20 UND', 5.00, '2026-08-07 14:48:13'),
(5, '280461', 'MANTECADO/FRESA/CHOCOLATE 2 X1', 5.00, '2026-08-07 14:48:13'),
(6, '280451', 'CHOCOLATE TRADICIONAL 700cm3', 5.00, '2026-08-07 14:48:13'),
(7, '280448', 'FRESA TRADICIONAL  700cm3', 5.00, '2026-08-07 14:48:13'),
(8, '280447', 'MANTECADO TRADICIONAL 700cm3', 5.00, '2026-08-07 14:48:13'),
(9, '280465', 'FANTOCHE CHOCOLATE 700cm3', 5.00, '2026-08-07 14:48:13'),
(10, '280463', 'FANTOCHE FRESA 700cm3', 5.00, '2026-08-07 14:48:13'),
(11, '280464', 'FANTOCHE MANTECADO 700cm3', 5.00, '2026-08-07 14:48:13'),
(12, '280503', 'COOKIES AND CREAM 700cm3', 5.00, '2026-08-07 14:48:13'),
(13, '280483', 'TRAMONTANA 700cm3', 5.00, '2026-08-07 14:48:13'),
(14, '280487', 'RON PASAS 700cm3', 5.00, '2026-08-07 14:48:13'),
(15, '280482', 'CREMA FANTASIA 700cm3', 5.00, '2026-08-07 14:48:13'),
(16, '280485', 'PISTACHO 700cm3', 5.00, '2026-08-07 14:48:13'),
(17, '280004', 'CHANTILLY FRESA 4,4L', 5.00, '2026-08-07 14:48:13'),
(18, '280008', 'CHOCOLATE 4,4L', 5.00, '2026-08-07 14:48:13'),
(19, '280017', 'COOKIES & CREAM 4,4L', 5.00, '2026-08-07 14:48:13'),
(20, '280018', 'CREMA FANTASIA 4,4L', 5.00, '2026-08-07 14:48:13'),
(21, '280034', 'FRESA 4,4L', 5.00, '2026-08-07 14:48:13'),
(22, '280044', 'MANTECADO 4,4L', 5.00, '2026-08-07 14:48:13'),
(23, '280053', 'MARMOLADO 4,4L', 5.00, '2026-08-07 14:48:13'),
(24, '280060', 'PISTACHO 4,4L', 5.00, '2026-08-07 14:48:13'),
(25, '280062', 'RON PASAS 4,4L', 5.00, '2026-08-07 14:48:13'),
(49, '280068', 'TRAMONTANA 4,4L', 5.00, '2026-08-07 14:34:04'),
(50, '280134', 'SIROP DE CHOCOLATE 4,5 kg', 5.00, '2026-08-07 14:34:04'),
(51, '280135', 'SIROP DE FRESA 4,5 kg', 5.00, '2026-08-07 14:34:04'),
(52, '280452', 'VAINILLA SOFT 4K X1', 5.00, '2026-08-07 14:34:04'),
(53, '280454', 'CHOCOLATE SOFT 4K X1', 5.00, '2026-08-07 14:34:04'),
(54, '280455', 'DULCE DE LECHE SOFT 4K X1', 5.00, '2026-08-07 14:34:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rutas`
--

CREATE TABLE `rutas` (
  `id` int(11) NOT NULL,
  `nombre_ruta` varchar(50) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `rutas`
--

INSERT INTO `rutas` (`id`, `nombre_ruta`, `fecha_creacion`) VALUES
(1, 'NORTE', '2026-07-26 03:58:15'),
(2, 'SUR', '2026-07-26 03:58:15'),
(3, 'COL', '2026-07-26 03:58:15'),
(4, 'VILLA', '2026-07-26 03:58:15'),
(5, 'CONCEPCIÓN', '2026-07-26 03:58:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sucursales`
--

CREATE TABLE `sucursales` (
  `id` int(11) NOT NULL,
  `codigo_sucursal` varchar(20) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `nombre_sucursal` varchar(150) NOT NULL,
  `direccion` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `cedula` varchar(8) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `cargo` enum('Administrador','Preventista','Ventas','Cobranza') NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `foto_perfil` varchar(255) DEFAULT NULL,
  `pref_stock` tinyint(1) NOT NULL DEFAULT 0,
  `pref_pdf` tinyint(1) NOT NULL DEFAULT 0,
  `pref_datos` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `cedula`, `nombre`, `correo`, `telefono`, `password`, `cargo`, `fecha_registro`, `foto_perfil`, `pref_stock`, `pref_pdf`, `pref_datos`) VALUES
(15, '30468992', 'Admin', 'soporteit.helar@gmail.com', '04121071582', '12345', 'Ventas', '2026-08-07 19:56:00', NULL, 0, 0, 0),
(16, '30478992', 'Admin 2', 'correohelar.@gmail.com', '04246577356', '12345', 'Preventista', '2026-08-07 20:00:50', NULL, 0, 0, 0);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `captaciones`
--
ALTER TABLE `captaciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `captacion_neveras`
--
ALTER TABLE `captacion_neveras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `captacion_id` (`captacion_id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rif` (`rif`),
  ADD KEY `fk_cliente_ruta` (`id_ruta`);

--
-- Indices de la tabla `cliente_equipos`
--
ALTER TABLE `cliente_equipos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_equipo_cliente` (`cliente_id`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clave` (`clave`);

--
-- Indices de la tabla `detalles_catalogo`
--
ALTER TABLE `detalles_catalogo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_producto` (`codigo_producto`);

--
-- Indices de la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `disponibilidad_inventario`
--
ALTER TABLE `disponibilidad_inventario`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `fk_pedido_sucursal` (`sucursal_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `rutas`
--
ALTER TABLE `rutas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `sucursales`
--
ALTER TABLE `sucursales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sucursal_cliente` (`id_cliente`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `captaciones`
--
ALTER TABLE `captaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `captacion_neveras`
--
ALTER TABLE `captacion_neveras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `cliente_equipos`
--
ALTER TABLE `cliente_equipos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `detalles_catalogo`
--
ALTER TABLE `detalles_catalogo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `disponibilidad_inventario`
--
ALTER TABLE `disponibilidad_inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT de la tabla `rutas`
--
ALTER TABLE `rutas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `sucursales`
--
ALTER TABLE `sucursales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `captacion_neveras`
--
ALTER TABLE `captacion_neveras`
  ADD CONSTRAINT `captacion_neveras_ibfk_1` FOREIGN KEY (`captacion_id`) REFERENCES `captaciones` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `fk_cliente_ruta` FOREIGN KEY (`id_ruta`) REFERENCES `rutas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `cliente_equipos`
--
ALTER TABLE `cliente_equipos`
  ADD CONSTRAINT `fk_equipo_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  ADD CONSTRAINT `detalle_pedidos_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detalle_pedidos_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_pedido_sucursal` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `sucursales`
--
ALTER TABLE `sucursales`
  ADD CONSTRAINT `fk_sucursal_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
