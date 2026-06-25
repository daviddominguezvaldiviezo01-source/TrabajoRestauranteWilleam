-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-06-2026 a las 20:39:01
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
-- Base de datos: `restaurant_bd`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `anuncios`
--

CREATE TABLE `anuncios` (
  `id_anuncio` int(11) NOT NULL,
  `titulo` varchar(120) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `enlace` varchar(255) DEFAULT NULL,
  `imagen` varchar(255) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `anuncios`
--

INSERT INTO `anuncios` (`id_anuncio`, `titulo`, `descripcion`, `enlace`, `imagen`, `activo`, `creado_en`) VALUES
(6, 'POLLOS', '2X1', '', 'images/anuncios/ad_6a22e40e2fa2e.png', 1, '2026-06-05 09:58:22'),
(9, '', '', '', 'images/anuncios/ad_6a231fb94ccc5.png', 1, '2026-06-05 14:12:57'),
(10, '', '', '', 'images/anuncios/ad_6a231fc6c1126.png', 1, '2026-06-05 14:13:10'),
(11, '', '', '', 'images/anuncios/ad_6a231fd44f9d3.png', 1, '2026-06-05 14:13:24'),
(12, '', '', '', 'images/anuncios/ad_6a23212c28673.png', 1, '2026-06-05 14:19:08'),
(13, '', '', '', 'images/anuncios/ad_6a2321f710a24.png', 1, '2026-06-05 14:22:31'),
(14, '', '', '', 'images/anuncios/ad_6a2324496c950.png', 1, '2026-06-05 14:32:25'),
(17, '', '', '', 'images/anuncios/ad_6a2324de0e66d.png', 1, '2026-06-05 14:34:54'),
(18, '', '', '', 'images/anuncios/ad_6a232628bb855.png', 1, '2026-06-05 14:40:24'),
(19, '', '', '', 'images/anuncios/ad_6a2326d7481ef.png', 1, '2026-06-05 14:43:19'),
(20, '', '', '', 'images/anuncios/ad_6a2326e72dab5.png', 1, '2026-06-05 14:43:35'),
(21, '', '', '', 'images/anuncios/ad_6a2326f2a099a.png', 1, '2026-06-05 14:43:46'),
(22, '', '', '', 'images/anuncios/ad_6a232768527fe.png', 1, '2026-06-05 14:45:44'),
(23, '', '', '', 'images/anuncios/ad_6a2327bc1546c.png', 1, '2026-06-05 14:47:08'),
(24, '', '', '', 'images/anuncios/ad_6a2327ecae10a.png', 1, '2026-06-05 14:47:56'),
(25, '', '', '', 'images/anuncios/ad_6a232819b6399.png', 1, '2026-06-05 14:48:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre`) VALUES
(1, 'Ceviches y Tiraditos'),
(2, 'Leches de Tigre'),
(3, 'Clásicos Calientes'),
(4, 'Chilcanos y Caldos'),
(5, 'Combos y Tríos'),
(6, 'Extras y Porciones'),
(7, 'Bebidas Tradicionales'),
(8, 'Gaseosas y Cervezas'),
(9, 'Hamburguesas'),
(10, 'CHIFA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedido`
--

CREATE TABLE `detalle_pedido` (
  `id_detalle` int(11) NOT NULL,
  `id_pedido` int(11) DEFAULT NULL,
  `id_producto` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_pedido`
--

INSERT INTO `detalle_pedido` (`id_detalle`, `id_pedido`, `id_producto`, `cantidad`, `subtotal`) VALUES
(1, 1, 26, 1, 4.50),
(2, 2, 26, 1, 4.50),
(3, 3, 26, 1, 4.50),
(4, 4, 8, 1, 22.00),
(5, 4, 16, 1, 28.00),
(6, 4, 14, 1, 35.00),
(7, 4, 11, 1, 35.00),
(8, 5, 13, 1, 10.00),
(9, 6, 13, 1, 10.00),
(10, 7, 8, 1, 22.00),
(11, 8, 13, 1, 10.00),
(12, 8, 1, 1, 18.00),
(13, 8, 14, 1, 35.00),
(14, 8, 16, 2, 56.00),
(15, 8, 22, 1, 3.00),
(16, 9, 13, 1, 10.00),
(17, 10, 3, 1, 30.00),
(18, 11, 13, 1, 10.00),
(19, 12, 17, 1, 35.00),
(20, 13, 18, 1, 38.00),
(21, 14, 14, 1, 35.00),
(22, 15, 8, 1, 22.00),
(23, 16, 8, 2, 44.00),
(24, 17, 6, 1, 12.00),
(25, 17, 8, 1, 22.00),
(26, 18, 14, 1, 35.00),
(27, 19, 22, 1, 3.00),
(28, 20, 6, 1, 12.00),
(29, 21, 25, 1, 4.00),
(30, 22, 11, 1, 35.00),
(31, 23, 3, 1, 30.00),
(32, 23, 22, 1, 3.00),
(33, 23, 8, 1, 22.00),
(34, 23, 6, 1, 12.00),
(35, 23, 17, 1, 35.00),
(36, 24, 7, 1, 15.00),
(37, 25, 6, 1, 12.00),
(38, 25, 28, 1, 9.00),
(39, 26, 16, 1, 28.00),
(43, 29, 1, 3, 54.00),
(44, 29, 6, 3, 36.00),
(45, 29, 8, 2, 44.00),
(46, 29, 23, 1, 10.00),
(47, 30, 8, 1, 22.00),
(48, 30, 13, 1, 10.00),
(49, 30, 28, 2, 18.00),
(50, 31, 6, 1, 12.00),
(51, 31, 28, 1, 9.00),
(52, 31, 17, 1, 35.00),
(53, 31, 23, 1, 10.00),
(54, 32, 8, 2, 44.00),
(55, 33, 6, 1, 12.00),
(56, 33, 1, 1, 18.00),
(57, 34, 6, 1, 12.00),
(58, 34, 28, 1, 9.00),
(59, 35, 6, 1, 12.00),
(60, 35, 28, 1, 9.00),
(61, 36, 8, 1, 22.00),
(62, 37, 28, 1, 9.00),
(63, 38, 8, 1, 22.00),
(64, 38, 13, 1, 10.00),
(65, 39, 1, 1, 18.00),
(66, 40, 16, 1, 28.00),
(67, 41, 16, 1, 28.00),
(68, 42, 16, 1, 28.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direcciones`
--

CREATE TABLE `direcciones` (
  `id_direccion` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `direccion` text NOT NULL,
  `referencia` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `direcciones`
--

INSERT INTO `direcciones` (`id_direccion`, `id_usuario`, `direccion`, `referencia`) VALUES
(1, 1, 'Andres Araujo 217', 'Al costado de la ANTENA'),
(2, 3, 'prolongacion pumacahua manzana A8 lote 03', 'despues de la primera cancha'),
(3, 3, 'prolongacion pumacahua manzana A8 lote 03', 'despues de la primera cancha'),
(4, 4, 'prolongacion pumacahua manzana A8 lote 03', 'despues de la primera cancha'),
(5, 4, 'prolongacion pumacahua manzana A8 lote 03', 'despues de la primera cancha'),
(6, 4, 'prolongacion pumacahua manzana A8 lote 03', 'despues de la primera cancha'),
(7, 4, 'prolongacion pumacahua manzana A8 lote 03', 'despues de la primera cancha'),
(8, 4, 'prolongacion pumacahua manzana A8 lote 03', 'despues de la primera cancha'),
(9, 4, 'prolongacion pumacahua manzana A8 lote 03', 'despues de la primera cancha'),
(10, 4, 'prolongacion pumacahua manzana A8 lote 03', 'despues de la primera cancha'),
(11, 4, 'prolongacion pumacahua manzana A8 lote 03', 'despues de la primera cancha'),
(12, 4, 'prolongacion pumacahua manzana A8 lote 03', 'despues de la primera cancha'),
(13, 4, 'prolongacion pumacahua manzana A8 lote 03', 'despues de la primera cancha'),
(14, 4, 'prolongacion pumacahua manzana A8 lote 03', 'despues de la primera cancha'),
(15, 4, 'prolongacion pumacahua manzana A8 lote 03', ''),
(16, 4, 'prolongacion pumacahua manzana A8 lote 03', ''),
(17, 4, 'prolongacion pumacahua manzana A8 lote 03', 'despues de la primera cancha'),
(18, 4, 'prolongacion pumacahua manzana A8 lote 03', 'despues de la primera cancha'),
(19, 4, 'prolongacion pumacahua manzana A8 lote 03', 'despues de la primera cancha'),
(20, 4, 'prolongacion pumacahua manzana A8 lote 03', 'despues de la primera cancha'),
(21, 5, 'prolongacion pumacahua manzana A8 lote 03', 'despues de la primera cancha'),
(22, 5, 'prolongacion pumacahua manzana A8 lote 03', ''),
(23, 3, 'prolongacion pumacahua manzana A8 lote 03', 'despues de la primera cancha'),
(24, 3, 'prolongacion pumacahua manzana A8 lote 03', 'despues de la primera cancha'),
(25, 3, 'prolongacion pumacahua manzana A8 lote 03', 'despues de la primera cancha'),
(26, 3, 'prolongacion pumacahua manzana A8 lote 03', 'despues de la primera cancha'),
(27, 3, 'prolongacion pumacahua manzana A8 lote 03', 'despues de la primera cancha'),
(28, 3, 'prolongacion pumacahua manzana A8 lote 03', 'despues de la primera cancha'),
(29, 3, 'prolongacion pumacahua manzana A8 lote 03', 'despues de la primera cancha'),
(30, 3, 'prolongacion pumacahua manzana A8 lote 03', 'despues de la primera cancha'),
(31, 7, 'Av. Zumiko', 'por donde las putas'),
(32, 8, 'Av. Navarrete', ''),
(33, 9, 'Av. San Bolivar', 'frente de la casa de armando'),
(34, 10, 'avenida chiquito N° 234', 'frente a el estadio de sanjuan'),
(35, 10, 'avenida chiquito N° 234', 'frente a el estadio de sanjuan'),
(36, 10, 'avenida chiquito N° 234', 'frente a el estadio de sanjuan'),
(37, 10, 'avenida chiquito N° 234', 'frente a el estadio de sanjuan'),
(38, 10, 'avenida chiquito N° 234', 'frente a el estadio de sanjuan'),
(39, 11, 'av. panamericana norte 504', 'el cruce peatoanl'),
(40, 12, 'Av Mariscal', 'frente al taller');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id_pago` int(11) NOT NULL,
  `id_pedido` int(11) DEFAULT NULL,
  `metodo` enum('efectivo','tarjeta','yape','plin') NOT NULL,
  `estado` enum('pendiente','pagado') DEFAULT 'pendiente',
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id_pago`, `id_pedido`, `metodo`, `estado`, `fecha`) VALUES
(1, 3, 'efectivo', 'pendiente', '2026-05-16 16:48:21'),
(2, 4, 'efectivo', 'pendiente', '2026-05-16 16:49:38'),
(3, 5, 'efectivo', 'pagado', '2026-05-16 16:52:59'),
(4, 6, 'efectivo', 'pagado', '2026-05-16 16:53:27'),
(5, 7, 'efectivo', 'pagado', '2026-05-16 16:55:47'),
(6, 8, 'efectivo', 'pagado', '2026-05-16 17:01:55'),
(7, 9, 'efectivo', 'pagado', '2026-05-16 17:04:41'),
(8, 10, 'efectivo', 'pagado', '2026-05-16 17:08:32'),
(9, 11, 'efectivo', 'pagado', '2026-05-16 17:44:37'),
(10, 12, 'efectivo', 'pagado', '2026-05-16 17:45:18'),
(11, 13, 'efectivo', 'pagado', '2026-05-16 20:15:26'),
(12, 14, 'efectivo', 'pagado', '2026-05-16 20:16:06'),
(13, 15, 'efectivo', 'pagado', '2026-05-16 20:24:44'),
(14, 16, 'efectivo', 'pagado', '2026-05-17 22:09:24'),
(15, 17, 'efectivo', 'pagado', '2026-05-17 22:11:56'),
(16, 18, 'yape', 'pagado', '2026-05-17 22:21:39'),
(17, 19, 'efectivo', 'pagado', '2026-05-17 22:51:11'),
(18, 20, 'plin', 'pagado', '2026-05-18 13:48:11'),
(19, 21, 'plin', 'pagado', '2026-05-18 14:27:22'),
(20, 22, 'plin', 'pagado', '2026-05-18 14:55:53'),
(21, 23, 'efectivo', 'pagado', '2026-05-18 14:57:37'),
(22, 24, 'plin', 'pagado', '2026-05-27 21:54:03'),
(23, 25, 'plin', 'pagado', '2026-05-30 00:28:39'),
(24, 26, 'plin', 'pagado', '2026-05-30 17:10:20'),
(27, 29, 'efectivo', 'pagado', '2026-06-04 15:11:53'),
(28, 30, 'efectivo', 'pagado', '2026-06-04 15:26:55'),
(29, 31, 'efectivo', 'pagado', '2026-06-05 10:32:08'),
(30, 32, 'efectivo', 'pagado', '2026-06-05 10:36:39'),
(31, 33, 'efectivo', 'pagado', '2026-06-05 14:02:25'),
(32, 34, 'yape', 'pagado', '2026-06-05 14:07:21'),
(33, 35, 'plin', 'pagado', '2026-06-05 14:13:07'),
(34, 36, 'efectivo', 'pagado', '2026-06-05 14:16:17'),
(35, 37, 'efectivo', 'pagado', '2026-06-05 14:24:00'),
(36, 38, 'yape', 'pagado', '2026-06-05 14:52:38'),
(37, 39, 'efectivo', 'pagado', '2026-06-12 13:52:31'),
(38, 40, 'efectivo', 'pagado', '2026-06-12 13:56:50'),
(39, 41, 'efectivo', 'pagado', '2026-06-12 14:11:18'),
(40, 42, 'efectivo', 'pagado', '2026-06-12 14:15:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id_pedido` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `id_direccion` int(11) DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `estado` enum('pendiente','preparando','ir a recoger','en camino','entregado','cancelado') DEFAULT 'pendiente',
  `total` decimal(10,2) DEFAULT NULL,
  `id_repartidor` int(11) DEFAULT NULL,
  `imagen_pedido` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id_pedido`, `id_usuario`, `id_direccion`, `fecha`, `estado`, `total`, `id_repartidor`, `imagen_pedido`) VALUES
(1, 4, 6, '2026-05-16 16:46:40', 'entregado', 10.31, 6, NULL),
(2, 4, 7, '2026-05-16 16:46:46', 'pendiente', 10.31, NULL, NULL),
(3, 4, 8, '2026-05-16 16:48:21', 'pendiente', 10.31, NULL, NULL),
(4, 4, 9, '2026-05-16 16:49:38', 'pendiente', 146.60, NULL, NULL),
(5, 4, 10, '2026-05-16 16:52:59', 'pendiente', 16.80, NULL, NULL),
(6, 4, 11, '2026-05-16 16:53:27', 'pendiente', 16.80, NULL, NULL),
(7, 4, 12, '2026-05-16 16:55:47', 'pendiente', 30.96, NULL, NULL),
(8, 4, 13, '2026-05-16 17:01:55', 'pendiente', 148.96, NULL, NULL),
(9, 4, 14, '2026-05-16 17:04:41', 'pendiente', 16.80, NULL, NULL),
(10, 4, 15, '2026-05-16 17:08:32', 'pendiente', 40.40, NULL, NULL),
(11, 4, 16, '2026-05-16 17:44:37', 'pendiente', 16.80, NULL, NULL),
(12, 4, 17, '2026-05-16 17:45:18', 'pendiente', 46.30, NULL, NULL),
(13, 4, 18, '2026-05-16 20:15:26', 'pendiente', 49.84, NULL, NULL),
(14, 4, 19, '2026-05-16 20:16:06', 'pendiente', 46.30, NULL, NULL),
(15, 4, 20, '2026-05-16 20:24:44', 'entregado', 30.96, NULL, NULL),
(16, 5, 21, '2026-05-17 22:09:24', 'entregado', 56.92, 6, NULL),
(17, 5, 22, '2026-05-17 22:11:56', 'pendiente', 45.12, NULL, NULL),
(18, 3, 23, '2026-05-17 22:21:39', 'pendiente', 46.30, NULL, NULL),
(19, 3, 24, '2026-05-17 22:51:11', 'entregado', 8.54, 6, NULL),
(20, 3, 25, '2026-05-18 13:48:11', 'entregado', 19.16, NULL, NULL),
(21, 3, 26, '2026-05-18 14:27:22', 'entregado', 9.72, 6, NULL),
(22, 3, 27, '2026-05-18 14:55:53', 'entregado', 46.30, 6, NULL),
(23, 3, 28, '2026-05-18 14:57:37', 'entregado', 125.36, 6, NULL),
(24, NULL, NULL, '2026-05-27 21:54:03', 'entregado', 22.70, 6, NULL),
(25, 3, 29, '2026-05-30 00:28:39', 'entregado', 29.78, 6, NULL),
(26, 3, 30, '2026-05-30 17:10:20', 'entregado', 38.04, 6, NULL),
(29, NULL, NULL, '2026-06-04 15:11:53', 'entregado', 174.92, 6, NULL),
(30, 8, 32, '2026-06-04 15:26:55', 'entregado', 64.00, 6, NULL),
(31, NULL, NULL, '2026-06-05 10:32:08', 'pendiente', 82.88, NULL, NULL),
(32, 9, 33, '2026-06-05 10:36:39', 'entregado', 56.92, 6, NULL),
(33, 10, 34, '2026-06-05 14:02:25', 'entregado', 40.40, 6, NULL),
(34, 10, 35, '2026-06-05 14:07:21', 'pendiente', 29.78, NULL, NULL),
(35, 10, 36, '2026-06-05 14:13:07', 'cancelado', 29.78, 6, NULL),
(36, 10, 37, 'cancelado', 30.96, 6, NULL),
(37, 10, 38, '2026-06-05 14:24:00', 'cancelado', 15.62, 6, NULL),
(38, 11, 39, '2026-06-05 14:52:38', 'cancelado', 42.76, 6, NULL),
(39, 12, 40, '2026-06-12 13:52:31', 'cancelado', 26.24, 6, NULL),
(40, NULL, NULL, '2026-06-12 13:56:50', 'cancelado', 38.04, 6, NULL),
(41, NULL, NULL, '2026-06-12 14:11:18', 'cancelado', 38.04, 6, NULL),
(42, NULL, NULL, '2026-06-12 14:15:01', 'cancelado', 38.04, 6, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `disponible` tinyint(1) DEFAULT 1,
  `id_categoria` int(11) DEFAULT NULL,
  `favorito` tinyint(1) DEFAULT 0,
  `estrella` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `nombre`, `descripcion`, `precio`, `imagen`, `stock`, `disponible`, `id_categoria`, `favorito`, `estrella`) VALUES
(1, 'Ceviche de Pescado', 'Clásico carretillero con su camote y zarandaja.', 18.00, 'https://tse2.mm.bing.net/th/id/OIP.17SoV8pLUYgyxdwuE1ewlgHaHa?rs=1&pid=ImgDetMain&o=7&rm=3', 43, 1, 1, 1, 0),
(2, 'Ceviche Mixto', 'Pescado, pota, langostinos y caracol.', 25.00, 'https://buenazo.cronosmedia.glr.pe/original/2020/09/09/5f58f8c082c2f615f804ffdb.jpg', 40, 1, 1, 0, 1),
(3, 'Ceviche de Conchas Negras', 'Directo de Tumbes, con harto limón y cebolla.', 30.00, 'https://micevichedehoy.com/assets/images/ceviche-de-conchas-negras_800x534.webp', 18, 1, 1, 0, 1),
(4, 'Tiradito al Ají Amarillo', 'Láminas de pescado en crema de ají amarillo.', 22.00, 'https://www.ajinomoto.com.pe:8085/img/receta/174.-Tiradito-de-pescado-a-la-crema-de-aji-amarillo--2.jpg', 30, 1, 1, 0, 0),
(5, 'Leche de Tigre Clásica', 'Vaso mediano con trozos de pescado y harta galleta.', 8.00, 'https://labarra12cevicheria.com.pe/wp-content/uploads/2023/10/leche-de-tigre.jpg', 100, 1, 2, 0, 0),
(6, 'Leche de Tigre Especial', 'Vaso grande con chicharrón de pota encima.', 12.00, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ2x96XValE10K7yqwsZtCOsQZajFcJSwgveQ&s', 69, 1, 2, 1, 1),
(7, 'Leche de Pantera', 'A base de conchas negras, pura energía.', 15.00, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQaeXAqOWGwhBmUtrEhvh35_7TfPvAy1dJY4A&s', 39, 1, 2, 0, 0),
(8, 'Arroz con Mariscos', 'Bien meloso y con harto marisco.', 22.00, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSVFi5GZzm4u56E3Cefr0K9YXJf7FisZK21Mw&s', 31, 1, 3, 1, 0),
(9, 'Chicharrón de Pota', 'Pota crujiente con yuca frita y sarsa.', 15.00, 'https://tse3.mm.bing.net/th/id/OIP.ESzguam1btB_1ZvCWWdUNgHaE8?w=1000&h=667&rs=1&pid=ImgDetMain&o=7&rm=3', 60, 1, 3, 0, 0),
(10, 'Chicharrón de Pescado', 'Trozos de pescado frito al estilo carretilla.', 20.00, 'https://tse1.mm.bing.net/th/id/OIP.t9FcJmCtaMTF_imhJFKYwQHaEK?rs=1&pid=ImgDetMain&o=7&rm=3', 40, 1, 3, 0, 0),
(11, 'Jalea Mixta', 'Combinación de mariscos fritos para compartir.', 35.00, 'https://tse1.mm.bing.net/th/id/OIP.9EYKZaDEylWKfDK1RXxVDgHaEJ?rs=1&pid=ImgDetMain&o=7&rm=3', 23, 1, 3, 0, 1),
(12, 'Arroz Chaufa de Pescado', 'Salteado al wok con trozos de pescado frito.', 18.00, 'https://th.bing.com/th/id/R.cfbd7a727040524a3eac168c52594a1a?rik=cMlBM5r4C5bZEg&pid=ImgRaw&r=0', 50, 1, 3, 0, 0),
(13, 'Chilcano de Pescado', 'Caldo puro para levantar muertos.', 10.00, 'https://tse3.mm.bing.net/th/id/OIP.wCfJvys0K_ZFhwzX5GzGbgHaEK?rs=1&pid=ImgDetMain&o=7&rm=3', 93, 1, 4, 1, 0),
(14, 'Parihuela', 'Concentrado de mariscos y pescado, bien potente.', 35.00, 'https://tse4.mm.bing.net/th/id/OIP.aRUF1vQ3DuZHOAhcwJc05QHaHa?rs=1&pid=ImgDetMain&o=7&rm=3', 11, 1, 4, 0, 1),
(15, 'Chupe de Langostinos', 'Sopa cremosa con leche, huevo y langostinos.', 32.00, 'https://th.bing.com/th/id/R.0545bd1792a4f8179e1d0182935f12cc?rik=fret%2b%2bQ10ofYNg&pid=ImgRaw&r=0', 20, 1, 4, 0, 0),
(16, 'Dúo Marino', 'Ceviche de pescado + Chicharrón de pota.', 28.00, 'https://tse2.mm.bing.net/th/id/OIP.zS-_YDHFS5gr0hYgBuur5QHaE8?rs=1&pid=ImgDetMain&o=7&rm=3', 46, 1, 5, 1, 0),
(17, 'Trío Clásico', 'Ceviche + Arroz con Mariscos + Chicharron de pota.', 35.00, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQRjnp1xT2Q93Ik91HZTbE8WB6JVYvn2Zbs4g&s', 36, 1, 5, 0, 1),
(18, 'Trío Norteño', 'Ceviche + Majado de Yuca + Chicharrón.', 38.00, 'https://tse1.mm.bing.net/th/id/OIP.A0B8PjGMY61wjrSS2S9klwHaJ5?rs=1&pid=ImgDetMain&o=7&rm=3', 19, 1, 5, 0, 0),
(19, 'Porción de Canchita', 'Maíz chulpi bien tostado.', 2.00, 'https://tse1.mm.bing.net/th/id/OIP._AaHzu3iqLNkQ3DrAgQ5TQHaEE?rs=1&pid=ImgDetMain&o=7&rm=3', 200, 1, 6, 0, 0),
(20, 'Porción de Camote', 'Dos rodajas de camote glaseado.', 2.00, 'https://th.bing.com/th/id/R.5142bb6526cc8c1c9ab38affcdccff0b?rik=qlLMzX6UlT%2fqZA&pid=ImgRaw&r=0', 200, 1, 6, 0, 0),
(21, 'Yuca Frita', 'Porción de yucas bien doraditas.', 5.00, 'https://th.bing.com/th/id/R.d9ec067bd164a83322a32dc841f9e0a6?rik=UkpYBqaP6rTltA&pid=ImgRaw&r=0', 100, 1, 6, 0, 0),
(22, 'Chicha Morada (Vaso)', 'Casera y refrescante.', 3.00, 'https://th.bing.com/th/id/R.794145c245daf1cbf461611588a168ea?rik=TWFRlIt2Ab%2bXnw&pid=ImgRaw&r=0', 147, 1, 7, 1, 0),
(23, 'Chicha Morada (Jarra 1L)', 'Ideal para la familia.', 10.00, 'https://th.bing.com/th/id/R.a1e69af729892ee50543a662ac8db365?rik=VmoaWKIZ36NsiA&pid=ImgRaw&r=0', 48, 1, 7, 0, 0),
(24, 'Cebada Heladita', 'Refresco de cebada tostada.', 2.50, 'https://tse2.mm.bing.net/th/id/OIP.SQae3WRKD1OE8qFkEq3ATwHaE8?rs=1&pid=ImgDetMain&o=7&rm=3', 100, 1, 7, 0, 0),
(25, 'Clarito', 'Bebida ancestral de jora.', 4.00, 'https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEhWmaZXsmLBEjnnt9FLUCuThmbIKaKgolTDQJo0d3qYLZeMDRGz2UTrXgkdWVz_TpB-iD0NgQFk12TtIuebzHcOEM8WS2JFhAblm1tOg6z-JufQS2NrNafKdFv3jeNsK7ElCyGnRW8HMWo/s1600/clarito+en+poto.JPG', 59, 1, 7, 0, 0),
(26, 'Inca Kola 500ml', 'Sabor nacional.', 4.50, 'https://www.ofimarket.pe/cdn/shop/files/551418.jpg?v=1723645019', 97, 1, 8, 0, 0),
(27, 'Coca Cola 500ml', 'Clásica helada.', 4.50, 'https://i5-mx.walmartimages.com/gr/images/product-images/img_large/00000007500980L.jpg', 100, 1, 8, 0, 0),
(28, 'Cerveza Pilsen 630ml', 'Para acompañar el ceviche.', 9.00, 'https://imagedelivery.net/4fYuQyy-r8_rpBpcY7lH_A/falabellaPE/sku16420030_1/public', 113, 1, 8, 1, 0),
(30, 'bacon Burguer', 'hamburguesa doble de carne + porcion de pepinillos + trozos de tocino + papas fritas', 24.00, 'https://s7d1.scene7.com/is/image/mcdonalds/DC_202201_4295-005_BaconQPC_1564x1564-1?wid=1000&hei=1000&dpr=off', 50, 1, 9, 0, 0),
(31, 'Cheese Burguer', 'hamburguesa doble de carne + queso chedar + porcion de papas', 19.90, 'https://s7d1.scene7.com/is/image/mcdonaldsstage/DC_202309_4282_QuarterPounderCheeseDeluxe_Shredded_1564x1564?wid=1000&hei=1000&dpr=off', 45, 1, 9, 1, 0),
(32, 'monstruosa burguer', 'hamburguesa de tripe carne + porcion de queso chedar + ensalada + papas + bebida', 24.90, 'https://s7d1.scene7.com/is/image/mcdonalds/DC_202302_0005-999_BigMac_1564x1564-1?wid=1000&hei=1000&dpr=off', 35, 1, 9, 0, 0),
(33, 'pollo ti pa kay', 'plato de pollo ti pa kay', 25.00, 'https://delosi-pidelo.s3.amazonaws.com/madam-tusan/products/pollo-ti-pa-kay-202603160507142356.webp', 35, 1, 10, 0, 0),
(34, 'Pato asado', 'porcion de pato asado', 45.00, 'https://delosi-pidelo.s3.amazonaws.com/madam-tusan/products/1-2-pato-asado-202603160506572054.webp', 30, 1, 10, 0, 0),
(35, 'tallarines de pollo', 'tallarines de pollo en trozos con verduras', 19.90, 'https://delosi-pidelo.s3.amazonaws.com/madam-tusan/products/tallarin-de-pollo-en-trozos-con-verduras-202603160506510375.webp', 30, 1, 10, 0, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resenas`
--

CREATE TABLE `resenas` (
  `id_resena` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `comentario` text DEFAULT NULL,
  `calificacion` tinyint(4) DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id_reserva` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `fecha` datetime DEFAULT NULL,
  `personas` int(11) DEFAULT NULL,
  `estado` enum('activa','cancelada','completada') DEFAULT 'activa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('cliente','admin','delivery') DEFAULT 'cliente',
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `email`, `telefono`, `password`, `rol`, `fecha_registro`) VALUES
(1, 'David', 'davixitoxd143@gmail.com', '908074316', '$2y$10$FZkI2UgS37Wsi8Wh3PHGCeMjbFaVywkFKeMGfSACIA58Ft.9YxCyW', 'admin', '2026-05-15 19:18:44'),
(2, 'Armando', 'jcedilloinfantead@gmail.com', '940955251', '$2y$10$pc7a3TILlBcGEverYhoe..hapZI0M9n9vpMd59ngWdosjdZPwH44q', 'cliente', '2026-05-15 19:18:44'),
(3, 'David', 'armandoshai@gmail.com', '940955251', '$2y$10$0cphMyC0Aue.U7tHEzJrROLoh9dbeNcPuTOGLwVmIuHGC3fe9leVO', 'admin', '2026-05-15 14:24:42'),
(4, 'roberto antonio carlos', 'roberto@gmail.com', '922453069', '$2y$10$6/A7IE7pwqVtgNI8XRpJReq4osYh61162gRPd97VBX3hHXBr8vWKe', 'cliente', '2026-05-16 16:42:56'),
(5, 'juan pedro', 'juanpedro@gmail.com', '922453069', '$2y$10$dNXe/QVvvzCooAM/4GsjHOrzVlfTRDILJqJXcbaf5r7TH2BhvoIQq', 'cliente', '2026-05-17 22:09:09'),
(6, 'Delivery Usuario', 'delivery@restaurante.test', '000000000', '$2y$10$b/sECWjeaP9S.G33hgEz2.1etbyUvDz.FS.TT1JBUDSmNbHodW3gK', 'delivery', '2026-05-31 20:07:52'),
(7, 'Matias Shai Infante', 'matiasinfann@gmail.com', '934731463', '$2y$10$SmqkBzagL/gwSbqgk1qdVeNLxmM/Pa.oE519u54x2JI5/xGBMz0Te', 'cliente', '2026-05-31 21:03:39'),
(8, 'Carlos Eduardo Ramírez Mendoza', 'carlosediardo203@gmail.com', '983475923', '$2y$10$cZoaSRRv8OdBAFJaHNLB6ubwnPLIJE2THb6.EFDiQf0ioTeuPddpe', 'cliente', '2026-06-04 15:26:30'),
(9, 'Karla  Zapata Dominguez Smith', 'karla@gmail.com', 'armandoshai@gmail.co', '$2y$10$HjD62eCZG/bP0OUU8F7mNOdH2C3dti6Pz/yjzbNYTthmwldfGpjVq', 'cliente', '2026-06-05 10:36:29'),
(10, 'juancito', 'j@gmail.com', '654456654', '$2y$10$QuWDi1rz8O8Sa/4XkqITc.pND6I9SJkbR0bQEwGXvq0Phixt3q/KS', 'cliente', '2026-06-05 13:59:56'),
(11, 'Guillermo', 'riveralopezguillermo33@gmail.com', '960668572', '$2y$10$i1ZCMG5Kc8vBVVkKNxPg/u0vF2v2cv2XXo2tyR.G25H7aaZo7LVOa', 'admin', '2026-06-05 14:11:38'),
(12, 'Fernanda Gutierrez Zapata', 'fernandita123@gmail.com', '984574923', '$2y$10$5x1.g4r0smKNzM.MWG36Vui2MOCbQBrbppANqVukzsXCir6krXxw.', 'cliente', '2026-06-12 13:52:12');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_ventas`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_ventas` (
`id_pedido` int(11)
,`cliente` varchar(100)
,`total` decimal(10,2)
,`estado` enum('pendiente','preparando','ir a recoger','en camino','entregado','cancelado')
,`fecha` datetime
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_ventas`
--
--
DROP TABLE IF EXISTS `vista_ventas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_ventas`  AS SELECT `p`.`id_pedido` AS `id_pedido`, `u`.`nombre` AS `cliente`, `p`.`total` AS `total`, `p`.`estado` AS `estado`, `p`.`fecha` AS `fecha` FROM (`pedidos` `p` join `usuarios` `u` on(`p`.`id_usuario` = `u`.`id_usuario`)) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `anuncios`
--
ALTER TABLE `anuncios`
  ADD PRIMARY KEY (`id_anuncio`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indices de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_pedido` (`id_pedido`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `direcciones`
--
ALTER TABLE `direcciones`
  ADD PRIMARY KEY (`id_direccion`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `id_pedido` (`id_pedido`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_direccion` (`id_direccion`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indices de la tabla `resenas`
--
ALTER TABLE `resenas`
  ADD PRIMARY KEY (`id_resena`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id_reserva`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `anuncios`
--
ALTER TABLE `anuncios`
  MODIFY `id_anuncio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT de la tabla `direcciones`
--
ALTER TABLE `direcciones`
  MODIFY `id_direccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de la tabla `resenas`
--
ALTER TABLE `resenas`
  MODIFY `id_resena` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id_reserva` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD CONSTRAINT `detalle_pedido_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`),
  ADD CONSTRAINT `detalle_pedido_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

--
-- Filtros para la tabla `direcciones`
--
ALTER TABLE `direcciones`
  ADD CONSTRAINT `direcciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`);

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`id_direccion`) REFERENCES `direcciones` (`id_direccion`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`);

--
-- Filtros para la tabla `resenas`
--
ALTER TABLE `resenas`
  ADD CONSTRAINT `resenas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
