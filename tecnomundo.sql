-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: db
-- Tiempo de generación: 16-11-2025 a las 20:52:12
-- Versión del servidor: 10.5.29-MariaDB-ubu2004
-- Versión de PHP: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `tecnomundo`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `id_cliente` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `direccion` varchar(100) NOT NULL,
  `num_direccion` varchar(10) NOT NULL,
  `id_empresa` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`id_cliente`, `nombre`, `apellido`, `telefono`, `correo`, `direccion`, `num_direccion`, `id_empresa`) VALUES
(1, 'Ana Laura', 'Sánchez', '3312345678', 'laura.sanchez@email.com', 'Av. México', '3000', 1),
(2, 'Miguel', 'Ramírez', '3387654321', 'miguel.r@email.com', 'Calle Manuel Acuña', '1500', 1),
(3, 'Verónica', 'Jiménez', '3311223344', 'vero.j@email.com', 'Av. Patria', '850', 1),
(4, 'Ricardo', 'Torres', '5511223344', 'ricardo.t@email.com', 'Av. de los Poetas', '200', 2),
(5, 'Gabriela', 'Mendoza', '5599887766', 'gaby.m@email.com', 'Calle Amsterdam', '89', 2),
(6, 'Oscar', 'Vargas', '5555667788', 'oscar.v@email.com', 'Bosque de Duraznos', '65', 2),
(7, 'Mariana', 'Castillo', '8112345678', 'mariana.c@email.com', 'Av. Fundidora', '501', 3),
(8, 'Javier', 'Rojas', '8187654321', 'javier.r@email.com', 'Río Orinoco', '175', 3),
(11, 'Juan', 'Prez Hernandez', '3321457349', 'juan34@gmail.com', 'Av valle de bravo ', '3219', 1),
(12, 'Alejandro', 'Carbajal Bañuelos ', '3321877324', 'alejandroprue@gmail.com', 'Av Revolucion ', '8765', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_reparacion`
--

CREATE TABLE `detalle_reparacion` (
  `id_detalle_reparacion` int(11) NOT NULL,
  `id_reparacion` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `subtotal` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_reparacion`
--

INSERT INTO `detalle_reparacion` (`id_detalle_reparacion`, `id_reparacion`, `id_producto`, `cantidad`, `subtotal`) VALUES
(1, 1, 1, 1, 1200),
(2, 2, 3, 1, 251),
(3, 3, 9, 1, 220),
(4, 4, 2, 1, 851),
(5, 5, 1, 1, 1200),
(6, 6, 2, 1, 851),
(11, 18, 1, 1, 1200),
(12, 19, 1, 1, 1200),
(14, 21, 1, 1, 1200),
(15, 22, 2, 1, 851);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_venta`
--

CREATE TABLE `detalle_venta` (
  `id_detalle_venta` int(11) NOT NULL,
  `id_venta` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `subtotal` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_venta`
--

INSERT INTO `detalle_venta` (`id_detalle_venta`, `id_venta`, `id_producto`, `cantidad`, `subtotal`) VALUES
(1, 1, 14, 1, 25999),
(2, 2, 5, 1, 280),
(3, 3, 12, 1, 950),
(4, 4, 5, 1, 280),
(5, 5, 7, 1, 600),
(6, 19, 12, 1, 950),
(7, 20, 8, 1, 750),
(8, 21, 13, 1, 6500),
(9, 22, 13, 1, 6500);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa`
--

CREATE TABLE `empresa` (
  `id_empresa` int(11) NOT NULL,
  `nombre_empresa` varchar(100) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `direccion` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empresa`
--

INSERT INTO `empresa` (`id_empresa`, `nombre_empresa`, `telefono`, `correo`, `direccion`) VALUES
(1, 'CellFix GDL', '3322446688', 'contacto@cellfixgdl.com', 'Av. Vallarta 1234, Col. Americana, Guadalajara'),
(2, 'TecnoShop CDMX', '5599887766', 'ventas@tecnoshopcdmx.mx', 'Paseo de la Reforma 567, Col. Cuauhtémoc, CDMX'),
(3, 'Regio Móvil MTY', '8133445566', 'info@regiomovil.com', 'Av. Eugenio Garza Sada 2501, Col. Tecnológico, Monterrey');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipo`
--

CREATE TABLE `equipo` (
  `id_equipo` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `marca` varchar(100) NOT NULL,
  `modelo` varchar(100) NOT NULL,
  `observaciones` varchar(100) NOT NULL,
  `fecha` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `equipo`
--

INSERT INTO `equipo` (`id_equipo`, `id_cliente`, `marca`, `modelo`, `observaciones`, `fecha`) VALUES
(1, 1, 'Apple', 'iPhone 12', 'Pantalla estrellada en la esquina superior derecha. Batería al 78% de capacidad.', '2025-10-01'),
(2, 2, 'Samsung', 'Galaxy A51', 'No carga, el puerto de carga parece obstruido o dañado.', '2025-10-03'),
(3, 3, 'Xiaomi', 'Redmi Note 9', 'Se reinicia constantemente después de una actualización de software.', '2025-10-05'),
(4, 4, 'Motorola', 'Moto G Stylus', 'El lápiz óptico no es detectado por el dispositivo.', '2025-10-02'),
(5, 5, 'Apple', 'iPhone 13 Pro', 'El Face ID no funciona. El cliente menciona que se le cayó.', '2025-10-06'),
(6, 6, 'Samsung', 'Galaxy S22 Ultra', 'La cámara principal no enfoca correctamente a la distancia.', '2025-10-08'),
(7, 7, 'Huawei', 'P30 Pro', 'Micrófono no funciona durante las llamadas, solo en altavoz.', '2025-10-04'),
(8, 8, 'Google', 'Pixel 6', 'Cliente solicita cambio de batería por bajo rendimiento.', '2025-10-07'),
(13, 8, 'Google', 'Pixel 6', 'Pantalla', '2025-11-12'),
(14, 8, 'Google', 'Pixel 6', 'Pantalla', '2025-11-12'),
(15, 8, 'Google', 'Pixel 6', 'Pantalla Rota', '2025-11-12'),
(16, 5, 'Appel', 'iPhone 13 Pro', 'Pantalla', '2025-11-12'),
(17, 12, 'Xiaomi', 'Note 13', 'Falla de bateria ', '2025-11-16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura`
--

CREATE TABLE `factura` (
  `id_factura` int(11) NOT NULL,
  `id_reparacion` int(11) DEFAULT NULL,
  `id_venta` int(11) DEFAULT NULL,
  `fecha_emision` date NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `detalle` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `factura`
--

INSERT INTO `factura` (`id_factura`, `id_reparacion`, `id_venta`, `fecha_emision`, `id_cliente`, `id_empresa`, `detalle`) VALUES
(1, NULL, 1, '2025-10-09', 2, 1, 'Venta de 1x Samsung Galaxy S23 Ultra 512GB'),
(2, NULL, 2, '2025-10-09', 5, 2, 'Venta de 1x Mica de Cristal Templado 9H Spigen'),
(5, 5, NULL, '2025-10-09', 1, 1, 'Reparación de pantalla para iPhone 11'),
(7, 1, NULL, '2025-10-02', 1, 1, 'Servicio de reparación folio #1'),
(8, 2, NULL, '2025-10-03', 2, 1, 'Servicio de reparación folio #2'),
(9, 3, NULL, '2025-10-08', 5, 2, 'Servicio de reparación folio #3'),
(10, 4, NULL, '2025-10-07', 8, 3, 'Servicio de reparación folio #4'),
(11, 5, NULL, '2025-10-09', 1, 1, 'Servicio de reparación folio #5'),
(12, 6, NULL, '2025-10-09', 4, 2, 'Servicio de reparación folio #6'),
(13, NULL, 3, '2025-10-09', 6, 2, 'Venta de 1x Audífonos Inalámbricos TWS SoundPEATS'),
(14, NULL, 4, '2025-10-09', 7, 3, 'Venta de 1x Mica de Cristal Templado 9H'),
(15, NULL, 5, '2025-10-09', 1, 1, 'Venta de 1x Kit de Herramientas Reparación iFixit'),
(16, 18, NULL, '2025-11-15', 8, 3, 'Servicio de reparación folio #18'),
(17, 19, NULL, '2025-11-15', 8, 3, 'Servicio de reparación folio #19'),
(18, 21, NULL, '2025-11-15', 5, 2, 'Servicio de reparación folio #21'),
(19, NULL, 19, '2025-11-15', 8, 3, 'Venta de productos folio #19'),
(20, NULL, 20, '2025-11-15', 8, 3, 'Venta de productos folio #20'),
(21, NULL, 21, '2025-11-16', 4, 2, 'Venta de productos folio #21'),
(22, NULL, 22, '2025-11-16', 1, 1, 'Venta de productos folio #22'),
(23, 22, NULL, '2025-11-16', 12, 3, 'Servicio de reparación folio #22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `garantia`
--

CREATE TABLE `garantia` (
  `id_garantia` int(11) NOT NULL,
  `id_reparacion` int(11) DEFAULT NULL,
  `id_venta` int(11) DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `condiciones` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `garantia`
--

INSERT INTO `garantia` (`id_garantia`, `id_reparacion`, `id_venta`, `fecha_inicio`, `fecha_fin`, `condiciones`) VALUES
(1, 1, NULL, '2025-10-02', '2025-11-02', 'Garantía de 30 días sobre la pieza reemplazada y la mano de obra. No cubre daños por mal uso o humedad.'),
(2, 2, NULL, '2025-10-03', '2025-11-03', 'Garantía de 30 días sobre la pieza reemplazada y la mano de obra. No cubre daños por mal uso o humedad.'),
(3, 3, NULL, '2025-10-08', '2025-11-08', 'Garantía de 30 días sobre la pieza reemplazada y la mano de obra. No cubre daños por mal uso o humedad.'),
(4, 4, NULL, '2025-10-07', '2025-11-07', 'Garantía de 30 días sobre la pieza reemplazada y la mano de obra. No cubre daños por mal uso o humedad.'),
(5, 5, NULL, '2025-10-09', '2025-11-09', 'Garantía de 30 días sobre la pieza reemplazada y la mano de obra. No cubre daños por mal uso o humedad.'),
(6, 6, NULL, '2025-10-09', '2025-11-09', 'Garantía de 30 días sobre la pieza reemplazada y la mano de obra. No cubre daños por mal uso o humedad.'),
(7, NULL, 1, '2025-10-09', '2026-10-09', 'Garantía de 1 año directamente con el fabricante por defectos de fábrica. No aplica en accesorios.'),
(8, NULL, 2, '2025-10-09', '2025-11-09', 'Garantía de 30 días en accesorios por defectos de fábrica.'),
(9, NULL, 3, '2025-10-09', '2025-11-09', 'Garantía de 30 días en accesorios por defectos de fábrica.'),
(10, NULL, 4, '2025-10-09', '2025-11-09', 'Garantía de 30 días en accesorios por defectos de fábrica.'),
(11, NULL, 5, '2025-10-09', '2025-11-09', 'Garantía de 30 días en accesorios por defectos de fábrica.'),
(12, 18, NULL, '2025-11-15', '2025-12-15', 'Garantía de 30 días sobre la reparación #18'),
(13, 19, NULL, '2025-11-15', '2025-12-15', 'Garantía de 30 días sobre la reparación #19'),
(14, 21, NULL, '2025-11-15', '2025-12-15', 'Garantía de 30 días sobre la reparación #21'),
(15, NULL, 19, '2025-11-15', '2025-12-15', 'Garantía de 30 días sobre la venta #19'),
(16, NULL, 20, '2025-11-15', '2025-12-15', 'Garantía de 30 días sobre la venta #20'),
(17, NULL, 21, '2025-11-16', '2025-12-16', 'Garantía de 30 días sobre la venta #21'),
(18, NULL, 22, '2025-11-16', '2025-12-16', 'Garantía de 30 días sobre la venta #22'),
(19, 22, NULL, '2025-11-16', '2025-12-16', 'Garantía de 30 días sobre la reparación #22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pago`
--

CREATE TABLE `pago` (
  `id_pago` int(11) NOT NULL,
  `id_factura` int(11) NOT NULL,
  `fecha_pago` date NOT NULL,
  `monto_pago` decimal(10,0) NOT NULL,
  `metodo_pago` varchar(100) NOT NULL,
  `detalle` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pago`
--

INSERT INTO `pago` (`id_pago`, `id_factura`, `fecha_pago`, `monto_pago`, `metodo_pago`, `detalle`) VALUES
(1, 1, '2025-10-09', 25999, 'Tarjeta de Crédito', 'Pago con AMEX terminación 8002'),
(2, 2, '2025-10-09', 280, 'Efectivo', 'Pago en sucursal por accesorio'),
(5, 5, '2025-10-09', 1600, 'Tarjeta de Débito', 'Pago por servicio de reparación folio #5'),
(7, 7, '2025-10-02', 1600, 'Tarjeta de Crédito', 'Liquidación de factura #7'),
(8, 8, '2025-10-03', 551, 'Efectivo', 'Liquidación de factura #8'),
(9, 9, '2025-10-08', 420, 'Transferencia', 'Liquidación de factura #9'),
(10, 10, '2025-10-07', 1150, 'Tarjeta de Débito', 'Liquidación de factura #10'),
(11, 11, '2025-10-09', 1600, 'Tarjeta de Crédito', 'Liquidación de factura #11'),
(12, 12, '2025-10-09', 1151, 'Efectivo', 'Liquidación de factura #12'),
(13, 13, '2025-10-09', 950, 'Tarjeta de Crédito', 'Pago por audífonos'),
(14, 14, '2025-10-09', 280, 'Efectivo', 'Pago por mica protectora'),
(15, 15, '2025-10-09', 600, 'Transferencia', 'Pago por kit de herramientas'),
(16, 16, '2025-11-15', 1200, 'Efectivo', 'Pago de factura #16'),
(17, 17, '2025-11-15', 1200, 'Efectivo', 'Pago de factura #17'),
(18, 18, '2025-11-15', 1200, 'Efectivo', 'Pago de factura #18'),
(19, 19, '2025-11-15', 950, 'Efectivo', 'Pago de factura #19'),
(20, 20, '2025-11-15', 750, 'Efectivo', 'Pago de factura #20'),
(21, 21, '2025-11-16', 6500, 'Efectivo', 'Pago de factura #21'),
(22, 22, '2025-11-16', 6500, 'Efectivo', 'Pago de factura #22'),
(23, 23, '2025-11-16', 851, 'Efectivo', 'Pago de factura #23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `id_producto` int(11) NOT NULL,
  `id_proveedor` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `marca` varchar(100) NOT NULL,
  `modelo_compatible` varchar(100) NOT NULL,
  `tipo_producto` enum('repuesto','accesorio','dispositivo') NOT NULL,
  `precio` decimal(10,0) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `min_stock` int(11) NOT NULL DEFAULT 5,
  `nivel_alerta` enum('low','critical','normal') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`id_producto`, `id_proveedor`, `nombre`, `marca`, `modelo_compatible`, `tipo_producto`, `precio`, `stock`, `min_stock`, `nivel_alerta`) VALUES
(1, 1, 'Pantalla LCD + Táctil', 'Genérica', 'iPhone 11', 'repuesto', 1200, 5, 5, 'low'),
(2, 1, 'Batería Interna', 'OEM', 'Samsung Galaxy S20', 'repuesto', 851, 5, 5, 'low'),
(3, 1, 'Centro de Carga USB-C', 'Genérica', 'Varios', 'repuesto', 250, 4, 5, 'low'),
(4, 2, 'Funda de Silicón', 'Caseology', 'iPhone 13 Pro', 'accesorio', 350, 4, 5, 'low'),
(5, 2, 'Mica de Cristal Templado 9H', 'Spigen', 'Samsung Galaxy A52', 'accesorio', 280, 0, 5, 'critical'),
(6, 2, 'Cargador de Pared 20W', 'Anker', 'Varios', 'accesorio', 450, 6, 5, 'normal'),
(7, 3, 'Kit de Herramientas Reparación', 'iFixit', 'Universal', 'accesorio', 600, 9, 5, 'normal'),
(8, 3, 'Cámara Trasera Principal', 'OEM', 'Xiaomi Redmi Note 10', 'repuesto', 750, 3, 5, 'critical'),
(9, 4, 'Bocina Altavoz', 'Genérica', 'Moto G Power (2021)', 'repuesto', 220, 7, 5, 'normal'),
(10, 4, 'Flexor de Encendido', 'OEM', 'iPhone XR', 'repuesto', 310, 0, 5, 'critical'),
(11, 6, 'Pantalla OLED', 'OEM', 'Samsung Galaxy Note 20', 'repuesto', 3500, 0, 5, 'critical'),
(12, 7, 'Audífonos Inalámbricos TWS', 'SoundPEATS', 'Universal', 'accesorio', 950, 0, 5, 'critical'),
(13, 7, 'Xiaomi Redmi Note 12 Pro 256GB', 'Xiaomi', 'N/A', 'dispositivo', 6500, 0, 5, 'critical'),
(14, 7, 'Samsung Galaxy S23 Ultra 512GB', 'Samsung', 'N/A', 'dispositivo', 25999, 0, 5, 'critical');

--
-- Disparadores `producto`
--
DELIMITER $$
CREATE TRIGGER `trg_before_producto_insert` BEFORE INSERT ON `producto` FOR EACH ROW BEGIN
    -- Usa la misma lógica para productos nuevos
    SET NEW.nivel_alerta = CASE
        WHEN NEW.stock <= 3 THEN 'critical'
        WHEN NEW.stock = 4 THEN 'low'
        WHEN NEW.stock = 5 THEN 'low'
        WHEN NEW.stock > 5 THEN 'normal'
        ELSE NULL
    END;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_before_producto_update` BEFORE UPDATE ON `producto` FOR EACH ROW BEGIN
    -- Define el 'nivel_alerta' basado en el nuevo stock
    SET NEW.nivel_alerta = CASE
        WHEN NEW.stock <= 3 THEN 'critical'
        WHEN NEW.stock = 4 THEN 'low'
        WHEN NEW.stock = 5 THEN 'low'
        ELSE NULL -- Si es 6 o más, el campo es NULL (sin alerta)
    END;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor`
--

CREATE TABLE `proveedor` (
  `id_proveedor` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `pieza_accesorio` varchar(100) NOT NULL,
  `telefono` int(10) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `direccion` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedor`
--

INSERT INTO `proveedor` (`id_proveedor`, `id_empresa`, `nombre`, `pieza_accesorio`, `telefono`, `correo`, `direccion`) VALUES
(1, 1, 'Global Parts Center', 'Pieza', 331234568, 'ventas@globalparts.com', 'Calle de la Tecnología 123, Zapopan'),
(2, 1, 'MobiAccesorios GDL', 'Accesorio', 339876532, 'contacto@mobiacc.mx', 'Av. Innovación 45, Guadalajara'),
(3, 1, 'TodoComponentes SA', 'Pieza y Accesorio', 335566788, 'info@todocomponentes.com', 'Industria Electrónica 90, Tlaquepaque'),
(4, 2, 'FixIt Supplies', 'Pieza', 551122334, 'support@fixit.com', 'Reforma 222, CDMX'),
(5, 2, 'Case & Cover World', 'Accesorio', 558776655, 'pedidos@caseworld.com', 'Insurgentes Sur 100, CDMX'),
(6, 3, 'Repuestos Express MTY', 'Pieza', 811234976, 'mty@repuestosexpress.com', 'Av. Constitución 500, Monterrey'),
(7, 3, 'Tech Gadgets Pro', 'Accesorio', 816543198, 'pro@techgadgets.net', 'Calzada del Valle 32, San Pedro');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reparacion`
--

CREATE TABLE `reparacion` (
  `id_reparacion` int(11) NOT NULL,
  `id_equipo` int(11) NOT NULL,
  `id_trabajador` int(11) NOT NULL,
  `fecha_ingreso` date NOT NULL,
  `fecha_terminado` date NOT NULL,
  `costo` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reparacion`
--

INSERT INTO `reparacion` (`id_reparacion`, `id_equipo`, `id_trabajador`, `fecha_ingreso`, `fecha_terminado`, `costo`) VALUES
(1, 1, 1, '2025-11-06', '2025-11-07', 1600),
(2, 2, 3, '2025-10-03', '2025-11-11', 551),
(3, 5, 6, '2025-10-06', '2025-10-08', 420),
(4, 8, 8, '2025-11-14', '2025-12-05', 1150),
(5, 1, 1, '2025-10-09', '2025-10-09', 1600),
(6, 4, 1, '2025-10-09', '2025-10-09', 1151),
(18, 13, 1, '2025-11-15', '2025-12-02', 1200),
(19, 14, 1, '2025-11-12', '2025-11-15', 1200),
(21, 16, 8, '2025-11-13', '2025-12-02', 1200),
(22, 17, 8, '2025-11-16', '2025-11-24', 851);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajador`
--

CREATE TABLE `trabajador` (
  `id_trabajador` int(11) NOT NULL,
  `contrasena` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `direccion` varchar(100) NOT NULL,
  `num_direccion` varchar(10) NOT NULL,
  `especialidad` varchar(100) NOT NULL,
  `rol` enum('admin','trabajador') NOT NULL DEFAULT 'trabajador',
  `id_empresa` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `trabajador`
--

INSERT INTO `trabajador` (`id_trabajador`, `contrasena`, `nombre`, `apellido`, `telefono`, `correo`, `direccion`, `num_direccion`, `especialidad`, `rol`, `id_empresa`) VALUES
(1, 'pass123', 'Juan', 'Pérez López', '331122344', 'juan.perez@cellfixgdl.com', 'Av. Juárez', '45A', 'Técnico de Hardware', 'admin', 1),
(2, 'pass123', 'Ana', 'García Martínez', '335566788', 'ana.garcia@cellfixgdl.com', 'Calle Ocampo', '112', 'Atención al Cliente', 'trabajador', 1),
(3, 'pass123', 'Carlos', 'Rodríguez Solís', '339987766', 'carlos.r@cellfixgdl.com', 'Calle Hidalgo', '87B', 'Técnico de Software', 'trabajador', 1),
(4, 'pass123', 'Sofía', 'Hernández Cruz', '551235678', 'sofia.h@tecnoshopcdmx.mx', 'Calle Madero', '230', 'Gerente de Tienda', 'trabajador', 2),
(5, 'pass123', 'Luis', 'Martínez Gómez', '558765321', 'luis.martinez@tecnoshopcdmx.mx', 'Av. Insurgentes', '1500', 'Ventas', 'admin', 2),
(6, 'pass123', 'Valeria', 'Chávez Ortiz', '554432216', 'valeria.c@tecnoshopcdmx.mx', 'Calle 5 de Mayo', '55', 'Técnico de Hardware', 'trabajador', 2),
(7, 'pass123', 'Diego', 'González Flores', '811123344', 'diego.g@regiomovil.com', 'Av. Lázaro Cárdenas', '2100', 'Ventas', 'trabajador', 3),
(8, 'pass123', 'Fernanda', 'Díaz Reyes', '815567788', 'fernanda.d@regiomovil.com', 'Calle Morelos', '845', 'Técnico Especialista Apple', 'trabajador', 3),
(9, 'pass123', 'Alberto ', 'Morales Hernandez ', '3345346709', 'albertotrabajo@gmail.com', 'Av Ajolotes', '5021', 'Tecnico', 'trabajador', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `venta`
--

CREATE TABLE `venta` (
  `id_venta` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_producto` int(11) DEFAULT NULL,
  `fecha_venta` date NOT NULL,
  `total` decimal(10,0) NOT NULL,
  `id_trabajador` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `venta`
--

INSERT INTO `venta` (`id_venta`, `id_cliente`, `id_producto`, `fecha_venta`, `total`, `id_trabajador`) VALUES
(1, 2, 14, '2025-11-09', 25999, NULL),
(2, 5, 5, '2025-10-09', 280, NULL),
(3, 6, 12, '2025-10-09', 950, NULL),
(4, 7, 5, '2025-10-09', 280, NULL),
(5, 1, 7, '2025-10-09', 600, NULL),
(19, 8, NULL, '2025-11-15', 950, NULL),
(20, 8, NULL, '2025-11-15', 750, NULL),
(21, 4, NULL, '2025-11-16', 6500, 8),
(22, 1, NULL, '2025-11-16', 6500, 8);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_ranking_tecnicos`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_ranking_tecnicos` (
`id_trabajador` int(11)
,`nombre` varchar(100)
,`apellido` varchar(100)
,`total_ordenes` bigint(21)
);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`id_cliente`),
  ADD KEY `id_empresa` (`id_empresa`);

--
-- Indices de la tabla `detalle_reparacion`
--
ALTER TABLE `detalle_reparacion`
  ADD PRIMARY KEY (`id_detalle_reparacion`),
  ADD KEY `id_producto` (`id_producto`),
  ADD KEY `id_reparacion` (`id_reparacion`);

--
-- Indices de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD PRIMARY KEY (`id_detalle_venta`),
  ADD KEY `id_producto` (`id_producto`),
  ADD KEY `id_venta` (`id_venta`);

--
-- Indices de la tabla `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`id_empresa`);

--
-- Indices de la tabla `equipo`
--
ALTER TABLE `equipo`
  ADD PRIMARY KEY (`id_equipo`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- Indices de la tabla `factura`
--
ALTER TABLE `factura`
  ADD PRIMARY KEY (`id_factura`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `id_empresa` (`id_empresa`),
  ADD KEY `id_reparacion` (`id_reparacion`),
  ADD KEY `id_venta` (`id_venta`);

--
-- Indices de la tabla `garantia`
--
ALTER TABLE `garantia`
  ADD PRIMARY KEY (`id_garantia`),
  ADD KEY `id_reparacion` (`id_reparacion`),
  ADD KEY `id_venta` (`id_venta`);

--
-- Indices de la tabla `pago`
--
ALTER TABLE `pago`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `id_factura` (`id_factura`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `id_proveedor` (`id_proveedor`);

--
-- Indices de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD PRIMARY KEY (`id_proveedor`),
  ADD KEY `id_empresa` (`id_empresa`);

--
-- Indices de la tabla `reparacion`
--
ALTER TABLE `reparacion`
  ADD PRIMARY KEY (`id_reparacion`),
  ADD KEY `id_equipo` (`id_equipo`),
  ADD KEY `id_trabajador` (`id_trabajador`);

--
-- Indices de la tabla `trabajador`
--
ALTER TABLE `trabajador`
  ADD PRIMARY KEY (`id_trabajador`),
  ADD KEY `id_empresa` (`id_empresa`);

--
-- Indices de la tabla `venta`
--
ALTER TABLE `venta`
  ADD PRIMARY KEY (`id_venta`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `id_producto` (`id_producto`),
  ADD KEY `fk_venta_trabajador` (`id_trabajador`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `detalle_reparacion`
--
ALTER TABLE `detalle_reparacion`
  MODIFY `id_detalle_reparacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  MODIFY `id_detalle_venta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `equipo`
--
ALTER TABLE `equipo`
  MODIFY `id_equipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `factura`
--
ALTER TABLE `factura`
  MODIFY `id_factura` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `garantia`
--
ALTER TABLE `garantia`
  MODIFY `id_garantia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `pago`
--
ALTER TABLE `pago`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;

--
-- AUTO_INCREMENT de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `reparacion`
--
ALTER TABLE `reparacion`
  MODIFY `id_reparacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `trabajador`
--
ALTER TABLE `trabajador`
  MODIFY `id_trabajador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `venta`
--
ALTER TABLE `venta`
  MODIFY `id_venta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_ranking_tecnicos`
--
DROP TABLE IF EXISTS `vista_ranking_tecnicos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `vista_ranking_tecnicos`  AS SELECT `t`.`id_trabajador` AS `id_trabajador`, `t`.`nombre` AS `nombre`, `t`.`apellido` AS `apellido`, count(`r`.`id_reparacion`) AS `total_ordenes` FROM (`reparacion` `r` join `trabajador` `t` on(`r`.`id_trabajador` = `t`.`id_trabajador`)) GROUP BY `t`.`id_trabajador` ORDER BY count(`r`.`id_reparacion`) DESC ;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `cliente_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id_empresa`);

--
-- Filtros para la tabla `detalle_reparacion`
--
ALTER TABLE `detalle_reparacion`
  ADD CONSTRAINT `detalle_reparacion_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`),
  ADD CONSTRAINT `detalle_reparacion_ibfk_2` FOREIGN KEY (`id_reparacion`) REFERENCES `reparacion` (`id_reparacion`);

--
-- Filtros para la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD CONSTRAINT `detalle_venta_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`),
  ADD CONSTRAINT `detalle_venta_ibfk_2` FOREIGN KEY (`id_venta`) REFERENCES `venta` (`id_venta`);

--
-- Filtros para la tabla `equipo`
--
ALTER TABLE `equipo`
  ADD CONSTRAINT `equipo_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`);

--
-- Filtros para la tabla `factura`
--
ALTER TABLE `factura`
  ADD CONSTRAINT `factura_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`),
  ADD CONSTRAINT `factura_ibfk_2` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id_empresa`),
  ADD CONSTRAINT `factura_ibfk_4` FOREIGN KEY (`id_reparacion`) REFERENCES `reparacion` (`id_reparacion`),
  ADD CONSTRAINT `factura_ibfk_5` FOREIGN KEY (`id_venta`) REFERENCES `venta` (`id_venta`);

--
-- Filtros para la tabla `garantia`
--
ALTER TABLE `garantia`
  ADD CONSTRAINT `garantia_ibfk_1` FOREIGN KEY (`id_reparacion`) REFERENCES `reparacion` (`id_reparacion`),
  ADD CONSTRAINT `garantia_ibfk_2` FOREIGN KEY (`id_venta`) REFERENCES `venta` (`id_venta`);

--
-- Filtros para la tabla `pago`
--
ALTER TABLE `pago`
  ADD CONSTRAINT `pago_ibfk_1` FOREIGN KEY (`id_factura`) REFERENCES `factura` (`id_factura`);

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedor` (`id_proveedor`);

--
-- Filtros para la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD CONSTRAINT `proveedor_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id_empresa`);

--
-- Filtros para la tabla `reparacion`
--
ALTER TABLE `reparacion`
  ADD CONSTRAINT `reparacion_ibfk_1` FOREIGN KEY (`id_equipo`) REFERENCES `equipo` (`id_equipo`),
  ADD CONSTRAINT `reparacion_ibfk_2` FOREIGN KEY (`id_trabajador`) REFERENCES `trabajador` (`id_trabajador`);

--
-- Filtros para la tabla `trabajador`
--
ALTER TABLE `trabajador`
  ADD CONSTRAINT `trabajador_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id_empresa`);

--
-- Filtros para la tabla `venta`
--
ALTER TABLE `venta`
  ADD CONSTRAINT `fk_venta_trabajador` FOREIGN KEY (`id_trabajador`) REFERENCES `trabajador` (`id_trabajador`),
  ADD CONSTRAINT `venta_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`),
  ADD CONSTRAINT `venta_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
