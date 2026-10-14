-- MySQL dump 10.13  Distrib 8.4.11, for Linux (x86_64)
--
-- Host: localhost    Database: mesa360_database
-- ------------------------------------------------------
-- Server version	8.4.11-0ubuntu0.26.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `CATEGORIA`
--

DROP TABLE IF EXISTS `CATEGORIA`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `CATEGORIA` (
  `id_categoria` int NOT NULL AUTO_INCREMENT,
  `nombre_categoria` varchar(255) DEFAULT NULL,
  `descripcion` text,
  `orden` int DEFAULT NULL,
  `activo` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id_categoria`),
  CONSTRAINT `CATEGORIA_chk_1` CHECK ((`orden` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `CLIENTE`
--

DROP TABLE IF EXISTS `CLIENTE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `CLIENTE` (
  `id_cliente` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) DEFAULT NULL,
  `telefono` varchar(255) DEFAULT NULL,
  `correo` varchar(255) DEFAULT NULL,
  `observaciones` text,
  PRIMARY KEY (`id_cliente`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `DETALLEPEDIDO`
--

DROP TABLE IF EXISTS `DETALLEPEDIDO`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `DETALLEPEDIDO` (
  `id_detalle` int NOT NULL AUTO_INCREMENT,
  `id_pedido` int DEFAULT NULL,
  `id_producto` int DEFAULT NULL,
  `cantidad` int DEFAULT NULL,
  `precio_unitario` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `notas_preparacion` text,
  PRIMARY KEY (`id_detalle`),
  KEY `id_pedido` (`id_pedido`),
  KEY `id_producto` (`id_producto`),
  CONSTRAINT `DETALLEPEDIDO_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `PEDIDO` (`id_pedido`),
  CONSTRAINT `DETALLEPEDIDO_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `PRODUCTO` (`id_producto`),
  CONSTRAINT `DETALLEPEDIDO_chk_1` CHECK ((`cantidad` > 0)),
  CONSTRAINT `DETALLEPEDIDO_chk_2` CHECK ((`precio_unitario` >= 0)),
  CONSTRAINT `DETALLEPEDIDO_chk_3` CHECK ((`subtotal` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `EMPLEADO`
--

DROP TABLE IF EXISTS `EMPLEADO`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `EMPLEADO` (
  `id_empleado` int NOT NULL AUTO_INCREMENT,
  `cedula` varchar(20) DEFAULT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `cargo` varchar(50) DEFAULT NULL,
  `fecha_contratacion` date DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `direccion` text,
  `activo` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id_empleado`),
  UNIQUE KEY `cedula` (`cedula`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ESTADOPEDIDO`
--

DROP TABLE IF EXISTS `ESTADOPEDIDO`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ESTADOPEDIDO` (
  `id_historial_estado` int NOT NULL AUTO_INCREMENT,
  `id_pedido` int DEFAULT NULL,
  `estado` enum('Pendiente','Preparando','Listo','Servido','Cancelado') DEFAULT NULL,
  `fecha_cambio` datetime DEFAULT NULL,
  `id_empleado` int DEFAULT NULL,
  PRIMARY KEY (`id_historial_estado`),
  KEY `id_pedido` (`id_pedido`),
  KEY `id_empleado` (`id_empleado`),
  CONSTRAINT `ESTADOPEDIDO_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `PEDIDO` (`id_pedido`),
  CONSTRAINT `ESTADOPEDIDO_ibfk_2` FOREIGN KEY (`id_empleado`) REFERENCES `EMPLEADO` (`id_empleado`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `HISTORIALACCION`
--

DROP TABLE IF EXISTS `HISTORIALACCION`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `HISTORIALACCION` (
  `id_historial` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int DEFAULT NULL,
  `accion` varchar(255) DEFAULT NULL,
  `modulo` varchar(255) DEFAULT NULL,
  `detalles` text,
  `ip_origen` varchar(255) DEFAULT NULL,
  `fecha_hora` datetime DEFAULT NULL,
  PRIMARY KEY (`id_historial`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `HISTORIALACCION_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `USUARIO` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `INSUMO`
--

DROP TABLE IF EXISTS `INSUMO`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `INSUMO` (
  `id_insumo` int NOT NULL AUTO_INCREMENT,
  `nombre_insumo` varchar(255) DEFAULT NULL,
  `unidad_medida` varchar(255) DEFAULT NULL,
  `stock_actual` decimal(10,2) DEFAULT NULL,
  `stock_minimo` decimal(10,2) DEFAULT NULL,
  `costo_unitario` decimal(10,2) DEFAULT NULL,
  `proveedor_habitual` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id_insumo`),
  CONSTRAINT `INSUMO_chk_1` CHECK ((`stock_actual` >= 0)),
  CONSTRAINT `INSUMO_chk_2` CHECK ((`stock_minimo` >= 0)),
  CONSTRAINT `INSUMO_chk_3` CHECK ((`costo_unitario` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MESA`
--

DROP TABLE IF EXISTS `MESA`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `MESA` (
  `id_mesa` int NOT NULL AUTO_INCREMENT,
  `numero_mesa` int DEFAULT NULL,
  `capacidad` int DEFAULT NULL,
  `ubicacion` varchar(255) DEFAULT NULL,
  `estado` enum('Disponible','Ocupada','Reservada') DEFAULT NULL,
  `activo` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id_mesa`),
  CONSTRAINT `MESA_chk_1` CHECK ((`capacidad` > 0))
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `METODOPAGO`
--

DROP TABLE IF EXISTS `METODOPAGO`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `METODOPAGO` (
  `id_metodo_pago` int NOT NULL AUTO_INCREMENT,
  `nombre_metodo` varchar(255) DEFAULT NULL,
  `descripcion` text,
  `requiere_referencia` tinyint(1) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id_metodo_pago`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MOVIMIENTOINVENTARIO`
--

DROP TABLE IF EXISTS `MOVIMIENTOINVENTARIO`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `MOVIMIENTOINVENTARIO` (
  `id_movimiento` int NOT NULL AUTO_INCREMENT,
  `id_insumo` int DEFAULT NULL,
  `tipo_movimiento` enum('Entrada','Salida') DEFAULT NULL,
  `cantidad` decimal(10,2) DEFAULT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `id_pedido` int DEFAULT NULL,
  `id_empleado` int DEFAULT NULL,
  `fecha_movimiento` datetime DEFAULT NULL,
  PRIMARY KEY (`id_movimiento`),
  KEY `id_insumo` (`id_insumo`),
  KEY `id_pedido` (`id_pedido`),
  KEY `id_empleado` (`id_empleado`),
  CONSTRAINT `MOVIMIENTOINVENTARIO_ibfk_1` FOREIGN KEY (`id_insumo`) REFERENCES `INSUMO` (`id_insumo`),
  CONSTRAINT `MOVIMIENTOINVENTARIO_ibfk_2` FOREIGN KEY (`id_pedido`) REFERENCES `PEDIDO` (`id_pedido`),
  CONSTRAINT `MOVIMIENTOINVENTARIO_ibfk_3` FOREIGN KEY (`id_empleado`) REFERENCES `EMPLEADO` (`id_empleado`),
  CONSTRAINT `MOVIMIENTOINVENTARIO_chk_1` CHECK ((`cantidad` > 0))
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `PAGO`
--

DROP TABLE IF EXISTS `PAGO`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `PAGO` (
  `id_pago` int NOT NULL AUTO_INCREMENT,
  `id_pedido` int DEFAULT NULL,
  `id_metodo_pago` int DEFAULT NULL,
  `monto` decimal(10,2) DEFAULT NULL,
  `monto_recibido` decimal(10,2) DEFAULT NULL,
  `cambio` decimal(10,2) DEFAULT NULL,
  `referencia_transaccion` varchar(255) DEFAULT NULL,
  `fecha_pago` datetime DEFAULT NULL,
  `id_empleado` int DEFAULT NULL,
  PRIMARY KEY (`id_pago`),
  KEY `id_pedido` (`id_pedido`),
  KEY `id_metodo_pago` (`id_metodo_pago`),
  KEY `id_empleado` (`id_empleado`),
  CONSTRAINT `PAGO_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `PEDIDO` (`id_pedido`),
  CONSTRAINT `PAGO_ibfk_2` FOREIGN KEY (`id_metodo_pago`) REFERENCES `METODOPAGO` (`id_metodo_pago`),
  CONSTRAINT `PAGO_ibfk_3` FOREIGN KEY (`id_empleado`) REFERENCES `EMPLEADO` (`id_empleado`),
  CONSTRAINT `PAGO_chk_1` CHECK ((`monto` >= 0)),
  CONSTRAINT `PAGO_chk_2` CHECK ((`monto_recibido` >= 0)),
  CONSTRAINT `PAGO_chk_3` CHECK ((`cambio` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `PEDIDO`
--

DROP TABLE IF EXISTS `PEDIDO`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `PEDIDO` (
  `id_pedido` int NOT NULL AUTO_INCREMENT,
  `id_mesa` int DEFAULT NULL,
  `id_cliente` int DEFAULT NULL,
  `id_empleado_mesero` int DEFAULT NULL,
  `id_empleado_cajero` int DEFAULT NULL,
  `id_metodo_pago` int DEFAULT NULL,
  `fecha_pedido` datetime DEFAULT NULL,
  `tipo_pedido` enum('Local','Para llevar') DEFAULT NULL,
  `estado_actual` enum('Pendiente','Preparando','Listo','Servido','Cancelado') DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `descuento` decimal(10,2) DEFAULT NULL,
  `impuesto` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `observaciones` text,
  PRIMARY KEY (`id_pedido`),
  KEY `id_mesa` (`id_mesa`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_empleado_mesero` (`id_empleado_mesero`),
  KEY `id_empleado_cajero` (`id_empleado_cajero`),
  KEY `id_metodo_pago` (`id_metodo_pago`),
  CONSTRAINT `PEDIDO_ibfk_1` FOREIGN KEY (`id_mesa`) REFERENCES `MESA` (`id_mesa`),
  CONSTRAINT `PEDIDO_ibfk_2` FOREIGN KEY (`id_cliente`) REFERENCES `CLIENTE` (`id_cliente`),
  CONSTRAINT `PEDIDO_ibfk_3` FOREIGN KEY (`id_empleado_mesero`) REFERENCES `EMPLEADO` (`id_empleado`),
  CONSTRAINT `PEDIDO_ibfk_4` FOREIGN KEY (`id_empleado_cajero`) REFERENCES `EMPLEADO` (`id_empleado`),
  CONSTRAINT `PEDIDO_ibfk_5` FOREIGN KEY (`id_metodo_pago`) REFERENCES `METODOPAGO` (`id_metodo_pago`),
  CONSTRAINT `PEDIDO_chk_1` CHECK ((`subtotal` >= 0)),
  CONSTRAINT `PEDIDO_chk_2` CHECK ((`descuento` >= 0)),
  CONSTRAINT `PEDIDO_chk_3` CHECK ((`impuesto` >= 0)),
  CONSTRAINT `PEDIDO_chk_4` CHECK ((`total` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `PERMISO`
--

DROP TABLE IF EXISTS `PERMISO`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `PERMISO` (
  `id_permiso` int NOT NULL AUTO_INCREMENT,
  `id_rol` int DEFAULT NULL,
  `modulo` varchar(255) DEFAULT NULL,
  `accion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_permiso`),
  KEY `id_rol` (`id_rol`),
  CONSTRAINT `PERMISO_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `ROL` (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `PRODUCTO`
--

DROP TABLE IF EXISTS `PRODUCTO`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `PRODUCTO` (
  `id_producto` int NOT NULL AUTO_INCREMENT,
  `id_categoria` int DEFAULT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `descripcion` text,
  `precio` decimal(10,2) DEFAULT NULL,
  `costo` decimal(10,2) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id_producto`),
  KEY `id_categoria` (`id_categoria`),
  CONSTRAINT `PRODUCTO_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `CATEGORIA` (`id_categoria`),
  CONSTRAINT `PRODUCTO_chk_1` CHECK ((`precio` >= 0)),
  CONSTRAINT `PRODUCTO_chk_2` CHECK ((`costo` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `RECETA`
--

DROP TABLE IF EXISTS `RECETA`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `RECETA` (
  `id_receta` int NOT NULL AUTO_INCREMENT,
  `id_producto` int DEFAULT NULL,
  `id_insumo` int DEFAULT NULL,
  `cantidad_requerida` decimal(10,2) DEFAULT NULL,
  `unidad_medida` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_receta`),
  KEY `id_producto` (`id_producto`),
  KEY `id_insumo` (`id_insumo`),
  CONSTRAINT `RECETA_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `PRODUCTO` (`id_producto`),
  CONSTRAINT `RECETA_ibfk_2` FOREIGN KEY (`id_insumo`) REFERENCES `INSUMO` (`id_insumo`),
  CONSTRAINT `RECETA_chk_1` CHECK ((`cantidad_requerida` > 0))
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `RESERVA`
--

DROP TABLE IF EXISTS `RESERVA`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `RESERVA` (
  `id_reserva` int NOT NULL AUTO_INCREMENT,
  `id_mesa` int DEFAULT NULL,
  `id_cliente` int DEFAULT NULL,
  `fecha_reserva` date DEFAULT NULL,
  `hora_reserva` time DEFAULT NULL,
  `cantidad_personas` int DEFAULT NULL,
  `estado` enum('Pendiente','Confirmada','Cancelada') DEFAULT NULL,
  `observaciones` text,
  `fecha_creacion` datetime DEFAULT NULL,
  PRIMARY KEY (`id_reserva`),
  KEY `id_mesa` (`id_mesa`),
  KEY `id_cliente` (`id_cliente`),
  CONSTRAINT `RESERVA_ibfk_1` FOREIGN KEY (`id_mesa`) REFERENCES `MESA` (`id_mesa`),
  CONSTRAINT `RESERVA_ibfk_2` FOREIGN KEY (`id_cliente`) REFERENCES `CLIENTE` (`id_cliente`),
  CONSTRAINT `RESERVA_chk_1` CHECK ((`cantidad_personas` > 0))
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ROL`
--

DROP TABLE IF EXISTS `ROL`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ROL` (
  `id_rol` int NOT NULL AUTO_INCREMENT,
  `nombre_rol` varchar(255) DEFAULT NULL,
  `descripcion` text,
  PRIMARY KEY (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `USUARIO`
--

DROP TABLE IF EXISTS `USUARIO`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `USUARIO` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `nombre_usuario` varchar(50) DEFAULT NULL,
  `contrasena` varchar(255) DEFAULT NULL,
  `id_empleado` int DEFAULT NULL,
  `id_rol` int DEFAULT NULL,
  `activo` tinyint(1) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT NULL,
  `ultimo_acceso` datetime DEFAULT NULL,
  `intentos_fallidos` int DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `nombre_usuario` (`nombre_usuario`),
  KEY `id_empleado` (`id_empleado`),
  KEY `id_rol` (`id_rol`),
  CONSTRAINT `USUARIO_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `EMPLEADO` (`id_empleado`),
  CONSTRAINT `USUARIO_ibfk_2` FOREIGN KEY (`id_rol`) REFERENCES `ROL` (`id_rol`),
  CONSTRAINT `USUARIO_chk_1` CHECK ((`intentos_fallidos` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-14 18:37:20
