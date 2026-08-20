-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: tacomenu
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `bcv_history`
--

DROP TABLE IF EXISTS `bcv_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bcv_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rate_date` date DEFAULT NULL,
  `rate` decimal(10,4) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bcv_history`
--

LOCK TABLES `bcv_history` WRITE;
/*!40000 ALTER TABLE `bcv_history` DISABLE KEYS */;
INSERT INTO `bcv_history` VALUES (1,'2026-08-11',761.2167,'2026-08-11 11:08:23'),(2,'2026-08-11',761.2167,'2026-08-11 11:20:30'),(3,'2026-08-11',761.2167,'2026-08-11 13:00:10'),(4,'2026-08-11',761.2167,'2026-08-11 13:10:10'),(5,'2026-08-11',761.2167,'2026-08-11 13:20:11'),(6,'2026-08-12',761.2167,'2026-08-11 18:57:12'),(7,'2026-08-12',761.2167,'2026-08-11 19:35:13'),(8,'2026-08-12',761.2167,'2026-08-11 20:56:39'),(9,'2026-08-12',761.2167,'2026-08-11 21:07:12'),(10,'2026-08-12',761.2167,'2026-08-11 21:17:12'),(11,'2026-08-12',761.2167,'2026-08-11 21:27:12'),(12,'2026-08-12',764.3486,'2026-08-12 08:47:23'),(13,'2026-08-12',764.3486,'2026-08-12 09:27:40'),(14,'2026-08-12',764.3486,'2026-08-12 09:37:40'),(15,'2026-08-12',764.3486,'2026-08-12 09:47:40'),(16,'2026-08-12',764.3486,'2026-08-12 09:57:40'),(17,'2026-08-12',764.3486,'2026-08-12 10:07:40'),(18,'2026-08-12',764.3486,'2026-08-12 10:17:40'),(19,'2026-08-12',764.3486,'2026-08-12 10:27:40'),(20,'2026-08-12',764.3486,'2026-08-12 10:37:40'),(21,'2026-08-12',764.3486,'2026-08-12 10:47:40'),(22,'2026-08-12',764.3486,'2026-08-12 10:57:40'),(23,'2026-08-12',764.3486,'2026-08-12 11:07:40'),(24,'2026-08-12',764.3486,'2026-08-12 13:07:40'),(25,'2026-08-12',764.3486,'2026-08-12 13:17:40'),(26,'2026-08-12',764.3486,'2026-08-12 13:27:40'),(27,'2026-08-12',764.3486,'2026-08-12 13:37:40'),(28,'2026-08-12',764.3486,'2026-08-12 13:47:42'),(29,'2026-08-12',764.3486,'2026-08-12 13:58:48'),(30,'2026-08-12',764.3486,'2026-08-12 14:08:48'),(31,'2026-08-12',764.3486,'2026-08-12 14:18:48'),(32,'2026-08-12',764.3486,'2026-08-12 14:28:48'),(33,'2026-08-12',764.3486,'2026-08-12 14:38:48'),(34,'2026-08-12',764.3486,'2026-08-12 14:48:48'),(35,'2026-08-12',764.3486,'2026-08-12 14:58:48'),(36,'2026-08-12',764.3486,'2026-08-12 15:08:48'),(37,'2026-08-17',772.5441,'2026-08-17 08:51:51'),(38,'2026-08-17',772.5441,'2026-08-17 09:22:45'),(39,'2026-08-17',772.5441,'2026-08-17 09:37:31'),(40,'2026-08-17',772.5441,'2026-08-17 09:57:30'),(41,'2026-08-17',772.5441,'2026-08-17 10:07:30'),(42,'2026-08-17',772.5441,'2026-08-17 10:17:30'),(43,'2026-08-17',772.5441,'2026-08-17 10:27:43'),(44,'2026-08-17',772.5441,'2026-08-17 10:47:30'),(45,'2026-08-17',772.5441,'2026-08-17 10:57:30'),(46,'2026-08-17',772.5441,'2026-08-17 11:17:12'),(47,'2026-08-18',773.3125,'2026-08-18 10:42:53'),(48,'2026-08-18',773.3125,'2026-08-18 11:00:35'),(49,'2026-08-19',775.3356,'2026-08-19 11:59:35'),(50,'2026-08-19',775.3356,'2026-08-19 12:17:01'),(51,'2026-08-19',775.3356,'2026-08-19 12:27:01'),(52,'2026-08-19',775.3356,'2026-08-19 12:56:55'),(53,'2026-08-19',775.3356,'2026-08-19 13:15:39'),(54,'2026-08-19',775.3356,'2026-08-19 13:25:45'),(55,'2026-08-19',775.3356,'2026-08-19 13:45:39'),(56,'2026-08-19',775.3356,'2026-08-19 13:55:39'),(57,'2026-08-19',775.3356,'2026-08-19 14:06:22'),(58,'2026-08-19',775.3356,'2026-08-19 14:16:22'),(59,'2026-08-19',775.3356,'2026-08-19 14:26:22'),(60,'2026-08-19',775.3356,'2026-08-19 14:36:22'),(61,'2026-08-19',775.3356,'2026-08-19 14:46:22'),(62,'2026-08-19',775.3356,'2026-08-19 14:56:22'),(63,'2026-08-19',775.3356,'2026-08-19 15:06:22'),(64,'2026-08-19',775.3356,'2026-08-19 15:16:22'),(65,'2026-08-19',775.3356,'2026-08-19 15:26:22'),(66,'2026-08-19',775.3356,'2026-08-19 15:36:22'),(67,'2026-08-19',775.3356,'2026-08-19 15:46:22'),(68,'2026-08-19',775.3356,'2026-08-19 21:52:02'),(69,'2026-08-19',775.3356,'2026-08-19 22:11:01'),(70,'2026-08-19',775.3356,'2026-08-19 22:21:01'),(71,'2026-08-19',775.3356,'2026-08-19 22:31:01');
/*!40000 ALTER TABLE `bcv_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `buy`
--

DROP TABLE IF EXISTS `buy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `buy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `k` varchar(20) DEFAULT NULL,
  `code` varchar(20) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `coupon_id` int(11) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `paymethod_id` int(11) DEFAULT NULL,
  `delivery_zone_id` int(11) DEFAULT NULL,
  `sede_id` int(11) DEFAULT NULL,
  `capture` varchar(255) DEFAULT NULL,
  `note` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `paymethod_id` (`paymethod_id`),
  KEY `coupon_id` (`coupon_id`),
  KEY `client_id` (`client_id`),
  KEY `status_id` (`status_id`),
  CONSTRAINT `buy_ibfk_1` FOREIGN KEY (`paymethod_id`) REFERENCES `paymethod` (`id`),
  CONSTRAINT `buy_ibfk_2` FOREIGN KEY (`coupon_id`) REFERENCES `coupon` (`id`),
  CONSTRAINT `buy_ibfk_3` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`),
  CONSTRAINT `buy_ibfk_4` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `buy`
--

LOCK TABLES `buy` WRITE;
/*!40000 ALTER TABLE `buy` DISABLE KEYS */;
INSERT INTO `buy` VALUES (1,'','SuyQsGM0ii2',1,NULL,5,'2026-08-11 10:26:00',1,NULL,NULL,NULL,NULL),(2,'','0x5Heci3ajS',1,NULL,5,'2026-08-11 10:40:06',1,NULL,NULL,NULL,NULL),(14,'','yxBeOurusLg',1,NULL,1,'2026-08-11 13:21:51',1,1,NULL,NULL,NULL),(15,'','RR3QRmtCuEC',1,NULL,2,'2026-08-17 11:16:50',1,2,1,NULL,NULL),(16,'','yL2msJly0nz',1,NULL,3,'2026-08-17 11:22:01',1,1,3,NULL,NULL),(17,'','bt0kAMZD1Mc',1,NULL,5,'2026-08-19 22:04:23',1,4,4,NULL,NULL);
/*!40000 ALTER TABLE `buy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `buy_product`
--

DROP TABLE IF EXISTS `buy_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `buy_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `buy_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `q` int(11) DEFAULT NULL,
  `extras` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `buy_id` (`buy_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `buy_product_ibfk_1` FOREIGN KEY (`buy_id`) REFERENCES `buy` (`id`),
  CONSTRAINT `buy_product_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `buy_product`
--

LOCK TABLES `buy_product` WRITE;
/*!40000 ALTER TABLE `buy_product` DISABLE KEYS */;
INSERT INTO `buy_product` VALUES (1,1,1,7,NULL),(2,2,1,3,NULL),(3,1,1,2,NULL),(4,2,1,1,NULL),(13,14,1,2,NULL),(14,14,1,1,'[{\"name\":\"Cebolla\",\"price\":3}]'),(15,15,1,1,'[{\"name\":\"Camarones\",\"price\":3}]'),(16,15,1,1,'[{\"name\":\"Aceitunas negras\",\"price\":3},{\"name\":\"Albahaca\",\"price\":3},{\"name\":\"Anchoas\",\"price\":3}]'),(17,16,1,1,'[{\"name\":\"Champi\\u00f1\\u00f3n\",\"price\":3}]'),(18,17,3,1,'[{\"name\":\"Extra de queso\",\"price\":5},{\"name\":\"Aceitunas negras\",\"price\":3},{\"name\":\"Anchoas\",\"price\":3}]');
/*!40000 ALTER TABLE `buy_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `short_name` varchar(200) DEFAULT NULL,
  `in_home` tinyint(1) DEFAULT 0,
  `in_menu` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `category`
--

LOCK TABLES `category` WRITE;
/*!40000 ALTER TABLE `category` DISABLE KEYS */;
INSERT INTO `category` VALUES (1,'Basico','basico',0,0,1),(2,'Pizza Gigante','pizzag',0,0,1),(3,'Pizza Familiar','pizzaf',0,0,1),(4,'Pizza Pequeña','pizzap',0,0,1),(5,'Pastas','pastas',0,0,1),(6,'Focaccia','focaccia',0,0,1);
/*!40000 ALTER TABLE `category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `client`
--

DROP TABLE IF EXISTS `client`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `password` varchar(60) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client`
--

LOCK TABLES `client` WRITE;
/*!40000 ALTER TABLE `client` DISABLE KEYS */;
INSERT INTO `client` VALUES (1,'kkkkk','','','64654676','saiiask','',1,'2026-08-11 10:26:00');
/*!40000 ALTER TABLE `client` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configuration`
--

DROP TABLE IF EXISTS `configuration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `label` varchar(200) DEFAULT NULL,
  `kind` int(11) DEFAULT NULL,
  `val` text DEFAULT NULL,
  `cfg_id` int(11) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuration`
--

LOCK TABLES `configuration` WRITE;
/*!40000 ALTER TABLE `configuration` DISABLE KEYS */;
INSERT INTO `configuration` VALUES (1,'general_main_title','Titulo Principal',1,'KATANA LITE',1),(2,'general_main_email','Email Principal',1,'tuemail@tudominio.com',1),(3,'general_country','Pais',1,'MX',1),(4,'general_coin','Moneda',1,'$',1),(5,'general_iva_txt','Impuesto Texto',1,'I.V.A',1),(6,'general_iva','Impuesto IVA (%)',2,'16',1),(7,'general_img_default','Imagen Default',1,'res/img/default.png',1),(8,'bank_titular','Titular de la cuenta',1,'',1),(9,'bank_name','Nombre del Banco',1,'',1),(10,'bank_account','Numero de Cuenta',1,'',1),(11,'bank_card','Numero de Tarjeta',1,'',1),(12,'general_whatsapp','Numero de WhatsApp (ej: 521...)',1,'+5215574506232',1),(13,'pago_movil_bank','Pago Móvil - Banco',1,'mercantil',1),(14,'pago_movil_ci','Pago Móvil - Cédula',1,'31088933',1),(15,'pago_movil_phone','Pago Móvil - Teléfono',1,'0412',1),(16,'pago_movil_titular','Pago Móvil - Titular',1,'keyler',1),(17,'zelle_contact','Zelle - Correo/Contacto',1,'',1),(18,'binance_contact','Binance - Contacto/ID',1,'',1),(19,'bcv_rate','Tasa BCV (Bs por US$)',1,'775.3356',1),(20,'bcv_rate_updated','Ultima actualizacion BCV',1,'2026-08-19 22:31:01',1),(21,'hero_hand','Texto mano (cursiva) - inicio',1,'2 x 1',1),(22,'hero_title','Titulo hero - inicio',1,'promo',1),(23,'hero_sub','Subtitulo hero - inicio',1,'pizzas y platillos caseros preparados al momento. Ordena desde tu celular y recÝbelo caliente donde estÚs.',1),(24,'flotante_img','Imagen flotante hero',1,'',1),(25,'flotante_product_id','Producto enlazado al flotante',1,'1',1),(26,'horario_lunes','Horario Lunes',1,'10:00 - 22:00',1),(27,'horario_martes','Horario Martes',1,'10:00 - 22:00',1),(28,'horario_miercoles','Horario MiÚrcoles',1,'10:00 - 22:00',1),(29,'horario_jueves','Horario Jueves',1,'10:00 - 22:00',1),(30,'horario_viernes','Horario Viernes',1,'10:00 - 22:00',1),(31,'horario_sabado','Horario Sßbado',1,'10:00 - 22:00',1),(32,'horario_domingo','Horario Domingo',1,'10:00 - 22:00',1),(33,'horario_open','Hora de apertura (cintas abierto/cerrado)',1,'10:00',1),(34,'horario_close','Hora de cierre (cintas abierto/cerrado)',1,'23:00',1);
/*!40000 ALTER TABLE `configuration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `country`
--

DROP TABLE IF EXISTS `country`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `country` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `country`
--

LOCK TABLES `country` WRITE;
/*!40000 ALTER TABLE `country` DISABLE KEYS */;
INSERT INTO `country` VALUES (1,'Argentina'),(2,'Chile'),(3,'Colombia'),(4,'Espa??a'),(5,'Mexico');
/*!40000 ALTER TABLE `country` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupon`
--

DROP TABLE IF EXISTS `coupon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `val` double DEFAULT NULL,
  `kind` int(11) DEFAULT 1,
  `is_multiple` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `start_at` date DEFAULT NULL,
  `finish_at` date DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `coupon_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupon`
--

LOCK TABLES `coupon` WRITE;
/*!40000 ALTER TABLE `coupon` DISABLE KEYS */;
/*!40000 ALTER TABLE `coupon` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `delivery_zone`
--

DROP TABLE IF EXISTS `delivery_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delivery_zone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delivery_zone`
--

LOCK TABLES `delivery_zone` WRITE;
/*!40000 ALTER TABLE `delivery_zone` DISABLE KEYS */;
INSERT INTO `delivery_zone` VALUES (1,'Villa Roca 1, 2, 3 / Roca Terra',1.00),(2,'Cabudare',2.00),(3,'Agua Viva / Piedad Norte y Sur / Vista Verde / Trigaleña / Atapaima',2.50),(4,'Barquisimeto - Este hasta Calle 40',2.00),(5,'Barquisimeto - Resto',3.50);
/*!40000 ALTER TABLE `delivery_zone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `paymethod`
--

DROP TABLE IF EXISTS `paymethod`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paymethod` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `short_name` varchar(100) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paymethod`
--

LOCK TABLES `paymethod` WRITE;
/*!40000 ALTER TABLE `paymethod` DISABLE KEYS */;
INSERT INTO `paymethod` VALUES (1,'pago_movil','Pago Móvil',1),(2,'transferencia','Transferencia Bancaria',1),(3,'zelle','Zelle',1),(4,'binance','Binance / USDT',1),(5,'efectivo','Efectivo',1),(6,'punto_venta','Punto de Venta / Tarjeta',1);
/*!40000 ALTER TABLE `paymethod` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product`
--

DROP TABLE IF EXISTS `product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `short_name` varchar(20) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `code` varchar(200) DEFAULT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `offer_txt` varchar(1000) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` int(11) DEFAULT 1,
  `is_public` tinyint(1) DEFAULT 0,
  `in_existence` tinyint(1) DEFAULT 0,
  `is_offert` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `order_at` datetime DEFAULT NULL,
  `price` float DEFAULT NULL,
  `price_llevar` decimal(10,2) DEFAULT NULL,
  `offer_price` float DEFAULT NULL,
  `offer_finish` date DEFAULT NULL,
  `free_ingredients` int(11) NOT NULL DEFAULT 0,
  `category_id` int(11) DEFAULT NULL,
  `sede_id` int(11) DEFAULT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `meta_title` varchar(100) DEFAULT NULL,
  `meta_description` varchar(255) DEFAULT NULL,
  `meta_keywords` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `unit_id` (`unit_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `product_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `unit` (`id`),
  CONSTRAINT `product_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product`
--

LOCK TABLES `product` WRITE;
/*!40000 ALTER TABLE `product` DISABLE KEYS */;
INSERT INTO `product` VALUES (1,'KJ4eiDiK02m','asdas','asdasdaasd','asdas1',NULL,'photo_5175077885788753392_y.jpg','',1,0,1,1,1,'2026-08-11 10:03:52',NULL,10000,NULL,NULL,NULL,0,1,NULL,1,NULL,NULL,NULL),(2,'PGB','Pizza Gigante Básica','PGB-001','Salsa de la casa, queso mozzarella, jamón y pimentón.',NULL,'pizza.jpg',NULL,1,1,1,1,0,'2026-08-19 12:14:24',NULL,12.99,NULL,NULL,NULL,0,2,NULL,6,NULL,NULL,NULL),(3,'PGT','Pizza Gigante Tocineta','PGT-001','Salsa de la casa, queso mozzarella, tocineta, jamón y maíz.',NULL,'pizza2.jpg',NULL,1,1,1,1,0,'2026-08-19 12:14:24',NULL,15.99,NULL,NULL,NULL,0,2,NULL,6,NULL,NULL,NULL),(4,'PGP','Pizza Gigante Pepperoni','PGP-001','Salsa de la casa, queso mozzarella, pepperoni, jamón y maíz.',NULL,'pizza23.jpg',NULL,1,1,1,1,0,'2026-08-19 12:14:24',NULL,16.99,NULL,NULL,NULL,0,2,NULL,6,NULL,NULL,NULL),(5,'PGV','Pizza Gigante Vegetariana','PGV-001','Salsa de la casa, queso mozzarella, aceitunas negras, cebolla, champiñón, pimentón y maíz.',NULL,'pizza-con-camarones.png',NULL,1,1,1,1,0,'2026-08-19 12:14:24',NULL,16.99,NULL,NULL,NULL,0,2,NULL,6,NULL,NULL,NULL),(6,'PGH','Pizza Gigante Hawaiana','PGH-001','Salsa de la casa, queso mozzarella, jamón y piña.',NULL,'pizza2_1.jpg',NULL,1,1,1,1,0,'2026-08-19 12:14:24',NULL,15.99,NULL,NULL,NULL,0,2,NULL,6,NULL,NULL,NULL),(7,'PGA','Pizza Gigante Anchoa','PGA-001','Salsa de la casa, queso mozzarella, anchoa, cebolla, aceitunas negras y pimentón.',NULL,'pizza2_2.jpg',NULL,0,1,1,1,0,'2026-08-19 12:14:24',NULL,17.99,NULL,NULL,NULL,0,2,NULL,6,NULL,NULL,NULL),(8,'PG4','Pizza Gigante 4 Estaciones','PG4-001','4 sabores de pizza a tu preferencia.',NULL,'pizza2_3.jpg',NULL,0,1,1,1,0,'2026-08-19 12:14:24',NULL,17.99,NULL,NULL,NULL,0,2,NULL,6,NULL,NULL,NULL),(9,'PG2','Pizza Gigante 2 Estaciones','PG2-001','2 sabores de pizza a tu preferencia.',NULL,'pizza2_4.jpg',NULL,0,1,1,1,0,'2026-08-19 12:14:24',NULL,17.99,NULL,NULL,NULL,0,2,NULL,6,NULL,NULL,NULL),(10,'PF40','Pizza Familiar 40 cm','PF40-001','Elige 3 ingredientes de tu preferencia. Comer aquí 11,99$ · Para llevar 13,50$.',NULL,'pizza2_5.jpg',NULL,1,1,1,1,0,'2026-08-19 12:14:24',NULL,11.99,13.50,NULL,NULL,3,3,NULL,7,NULL,NULL,NULL),(11,'PP25','Pizza Pequeña 25 cm','PP25-001','Elige 3 ingredientes de tu preferencia. Comer aquí 6,00$ · Para llevar 7,50$.',NULL,'pizza2_6.jpg',NULL,1,1,1,1,0,'2026-08-19 12:14:24',NULL,6,7.50,NULL,NULL,3,4,NULL,8,NULL,NULL,NULL),(12,'PN01','Pasta Napolitana','PN-001','Pasta con salsa napolitana. Pasta y refresco.',NULL,'pizzasfondo.jpg',NULL,0,1,1,1,0,'2026-08-19 12:14:24',NULL,5.99,NULL,NULL,NULL,0,5,4,9,NULL,NULL,NULL),(13,'PB01','Pasta Boloñesa','PB-001','Pasta con salsa boloñesa. Pasta y refresco.',NULL,'WhatsApp-Image-2026-08-06-at-10.36.58-AM.jpeg',NULL,0,1,1,1,0,'2026-08-19 12:14:24',NULL,8.99,NULL,NULL,NULL,0,5,4,9,NULL,NULL,NULL),(14,'PA01','Pasta Alfredo','PA-001','Pasta con salsa Alfredo. Pasta y refresco.',NULL,'pizza2_5.jpg',NULL,0,1,1,1,0,'2026-08-19 12:14:24',NULL,8.99,NULL,NULL,NULL,0,5,4,9,NULL,NULL,NULL),(15,'P4Q','Pasta 4 Quesos','P4Q-001','Pasta con mezcla de 4 quesos. Pasta y refresco.',NULL,'pizza2_4.jpg',NULL,0,1,1,1,0,'2026-08-19 12:14:24',NULL,8.99,NULL,NULL,NULL,0,5,4,9,NULL,NULL,NULL),(16,'PCH','Pasta Cheddar','PCH-001','Pasta con salsa cheddar. Pasta y refresco.',NULL,'pizza2_3.jpg',NULL,0,1,1,1,0,'2026-08-19 12:14:24',NULL,8.99,NULL,NULL,NULL,0,5,4,9,NULL,NULL,NULL),(17,'PPE','Pasta Pesto','PPE-001','Pasta con salsa pesto. Pasta y refresco.',NULL,'pizza2_2.jpg',NULL,0,1,1,1,0,'2026-08-19 12:14:24',NULL,8.99,NULL,NULL,NULL,0,5,4,9,NULL,NULL,NULL),(18,'PAF','Pasta al Forno','PAF-001','Pasta al forno. Pasta y refresco.',NULL,'pizza2_1.jpg',NULL,0,1,1,1,0,'2026-08-19 12:14:24',NULL,11.99,NULL,NULL,NULL,0,5,4,9,NULL,NULL,NULL),(19,'PSC','Pasticho','PSC-001','Pasticho tradicional. Con refresco.',NULL,'pizza2_6.jpg',NULL,0,1,1,1,0,'2026-08-19 12:14:24',NULL,10,NULL,NULL,NULL,0,5,4,9,NULL,NULL,NULL),(20,'FC1','Focaccia 1','FC-001','Tomate deshidratado, queso parmesano, mortadela con pistacho, pesto y aceite de oliva.',NULL,'pizza.jpg',NULL,0,1,1,1,0,'2026-08-19 12:14:24',NULL,0,NULL,NULL,NULL,0,6,4,10,NULL,NULL,NULL),(21,'FC2','Focaccia 2','FC-002','Stracciatella, rúgula, mortadela con pistacho, pesto y aceite de oliva.',NULL,'pizza2.jpg',NULL,0,1,1,1,0,'2026-08-19 12:14:24',NULL,0,NULL,NULL,NULL,0,6,4,10,NULL,NULL,NULL),(22,'FC3','Focaccia 3','FC-003','Queso mozzarella, albahaca, tomate, pesto y aceite de oliva.',NULL,'pizza23.jpg',NULL,0,1,1,1,0,'2026-08-19 12:14:24',NULL,0,NULL,NULL,NULL,0,6,4,10,NULL,NULL,NULL),(23,'FC4','Focaccia 4','FC-004','Stracciatella, pavo, rúgula, tomate, pesto y aceite de oliva.',NULL,'pizza-con-camarones.png',NULL,0,1,1,1,0,'2026-08-19 12:14:24',NULL,0,NULL,NULL,NULL,0,6,4,10,NULL,NULL,NULL);
/*!40000 ALTER TABLE `product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_extra`
--

DROP TABLE IF EXISTS `product_extra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_extra` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `group_key` varchar(40) DEFAULT NULL,
  `is_ingredient` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=238 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_extra`
--

LOCK TABLES `product_extra` WRITE;
/*!40000 ALTER TABLE `product_extra` DISABLE KEYS */;
INSERT INTO `product_extra` VALUES (88,2,'Maiz',3.00,'g17',1),(89,3,'Maiz',3.00,'g17',1),(90,4,'Maiz',3.00,'g17',1),(91,5,'Maiz',3.00,'g17',1),(92,6,'Maiz',3.00,'g17',1),(93,7,'Maiz',3.00,'g17',1),(94,8,'Maiz',3.00,'g17',1),(95,9,'Maiz',3.00,'g17',1),(96,10,'Maiz',3.00,'g17',1),(97,11,'Maiz',3.00,'g17',1),(98,2,'Pepperoni',3.00,'g18',1),(99,3,'Pepperoni',3.00,'g18',1),(100,4,'Pepperoni',3.00,'g18',1),(101,5,'Pepperoni',3.00,'g18',1),(102,6,'Pepperoni',3.00,'g18',1),(103,7,'Pepperoni',3.00,'g18',1),(104,8,'Pepperoni',3.00,'g18',1),(105,9,'Pepperoni',3.00,'g18',1),(106,10,'Pepperoni',3.00,'g18',1),(107,11,'Pepperoni',3.00,'g18',1),(108,2,'Tocineta',3.00,'g19',1),(109,3,'Tocineta',3.00,'g19',1),(110,4,'Tocineta',3.00,'g19',1),(111,5,'Tocineta',3.00,'g19',1),(112,6,'Tocineta',3.00,'g19',1),(113,7,'Tocineta',3.00,'g19',1),(114,8,'Tocineta',3.00,'g19',1),(115,9,'Tocineta',3.00,'g19',1),(116,10,'Tocineta',3.00,'g19',1),(117,11,'Tocineta',3.00,'g19',1),(118,2,'Aceitunas negras',3.00,'g21',1),(119,3,'Aceitunas negras',3.00,'g21',1),(120,4,'Aceitunas negras',3.00,'g21',1),(121,5,'Aceitunas negras',3.00,'g21',1),(122,6,'Aceitunas negras',3.00,'g21',1),(123,7,'Aceitunas negras',3.00,'g21',1),(124,8,'Aceitunas negras',3.00,'g21',1),(125,9,'Aceitunas negras',3.00,'g21',1),(126,10,'Aceitunas negras',3.00,'g21',1),(127,11,'Aceitunas negras',3.00,'g21',1),(128,2,'Pimentón',3.00,'g22',1),(129,3,'Pimentón',3.00,'g22',1),(130,4,'Pimentón',3.00,'g22',1),(131,5,'Pimentón',3.00,'g22',1),(132,6,'Pimentón',3.00,'g22',1),(133,7,'Pimentón',3.00,'g22',1),(134,8,'Pimentón',3.00,'g22',1),(135,9,'Pimentón',3.00,'g22',1),(136,10,'Pimentón',3.00,'g22',1),(137,11,'Pimentón',3.00,'g22',1),(138,2,'Cebolla',3.00,'g23',1),(139,3,'Cebolla',3.00,'g23',1),(140,4,'Cebolla',3.00,'g23',1),(141,5,'Cebolla',3.00,'g23',1),(142,6,'Cebolla',3.00,'g23',1),(143,7,'Cebolla',3.00,'g23',1),(144,8,'Cebolla',3.00,'g23',1),(145,9,'Cebolla',3.00,'g23',1),(146,10,'Cebolla',3.00,'g23',1),(147,11,'Cebolla',3.00,'g23',1),(148,2,'Anchoas',3.00,'g24',1),(149,3,'Anchoas',3.00,'g24',1),(150,4,'Anchoas',3.00,'g24',1),(151,5,'Anchoas',3.00,'g24',1),(152,6,'Anchoas',3.00,'g24',1),(153,7,'Anchoas',3.00,'g24',1),(154,8,'Anchoas',3.00,'g24',1),(155,9,'Anchoas',3.00,'g24',1),(156,10,'Anchoas',3.00,'g24',1),(157,11,'Anchoas',3.00,'g24',1),(158,2,'Piña',3.00,'g25',1),(159,3,'Piña',3.00,'g25',1),(160,4,'Piña',3.00,'g25',1),(161,5,'Piña',3.00,'g25',1),(162,6,'Piña',3.00,'g25',1),(163,7,'Piña',3.00,'g25',1),(164,8,'Piña',3.00,'g25',1),(165,9,'Piña',3.00,'g25',1),(166,10,'Piña',3.00,'g25',1),(167,11,'Piña',3.00,'g25',1),(168,2,'Tomate',3.00,'g26',1),(169,3,'Tomate',3.00,'g26',1),(170,4,'Tomate',3.00,'g26',1),(171,5,'Tomate',3.00,'g26',1),(172,6,'Tomate',3.00,'g26',1),(173,7,'Tomate',3.00,'g26',1),(174,8,'Tomate',3.00,'g26',1),(175,9,'Tomate',3.00,'g26',1),(176,10,'Tomate',3.00,'g26',1),(177,11,'Tomate',3.00,'g26',1),(178,2,'Albahaca',3.00,'g27',1),(179,3,'Albahaca',3.00,'g27',1),(180,4,'Albahaca',3.00,'g27',1),(181,5,'Albahaca',3.00,'g27',1),(182,6,'Albahaca',3.00,'g27',1),(183,7,'Albahaca',3.00,'g27',1),(184,8,'Albahaca',3.00,'g27',1),(185,9,'Albahaca',3.00,'g27',1),(186,10,'Albahaca',3.00,'g27',1),(187,11,'Albahaca',3.00,'g27',1),(188,2,'Salchicha',3.00,'g28',1),(189,3,'Salchicha',3.00,'g28',1),(190,4,'Salchicha',3.00,'g28',1),(191,5,'Salchicha',3.00,'g28',1),(192,6,'Salchicha',3.00,'g28',1),(193,7,'Salchicha',3.00,'g28',1),(194,8,'Salchicha',3.00,'g28',1),(195,9,'Salchicha',3.00,'g28',1),(196,10,'Salchicha',3.00,'g28',1),(197,11,'Salchicha',3.00,'g28',1),(198,2,'Camarones',3.00,'g29',1),(199,3,'Camarones',3.00,'g29',1),(200,4,'Camarones',3.00,'g29',1),(201,5,'Camarones',3.00,'g29',1),(202,6,'Camarones',3.00,'g29',1),(203,7,'Camarones',3.00,'g29',1),(204,8,'Camarones',3.00,'g29',1),(205,9,'Camarones',3.00,'g29',1),(206,10,'Camarones',3.00,'g29',1),(207,11,'Camarones',3.00,'g29',1),(208,2,'Extra de queso',5.00,'g30',0),(209,3,'Extra de queso',5.00,'g30',0),(210,4,'Extra de queso',5.00,'g30',0),(211,5,'Extra de queso',5.00,'g30',0),(212,6,'Extra de queso',5.00,'g30',0),(213,7,'Extra de queso',5.00,'g30',0),(214,8,'Extra de queso',5.00,'g30',0),(215,9,'Extra de queso',5.00,'g30',0),(216,10,'Extra de queso',5.00,'g30',0),(217,11,'Extra de queso',5.00,'g30',0),(218,2,'Jamón',3.00,'g16',1),(219,3,'Jamón',3.00,'g16',1),(220,4,'Jamón',3.00,'g16',1),(221,5,'Jamón',3.00,'g16',1),(222,6,'Jamón',3.00,'g16',1),(223,7,'Jamón',3.00,'g16',1),(224,8,'Jamón',3.00,'g16',1),(225,9,'Jamón',3.00,'g16',1),(226,10,'Jamón',3.00,'g16',1),(227,11,'Jamón',3.00,'g16',1),(228,2,'Champiñón',3.00,'g20',1),(229,3,'Champiñón',3.00,'g20',1),(230,4,'Champiñón',3.00,'g20',1),(231,5,'Champiñón',3.00,'g20',1),(232,6,'Champiñón',3.00,'g20',1),(233,7,'Champiñón',3.00,'g20',1),(234,8,'Champiñón',3.00,'g20',1),(235,9,'Champiñón',3.00,'g20',1),(236,10,'Champiñón',3.00,'g20',1),(237,11,'Champiñón',3.00,'g20',1);
/*!40000 ALTER TABLE `product_extra` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_view`
--

DROP TABLE IF EXISTS `product_view`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_view` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `viewer_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `realip` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `viewer_id` (`viewer_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_view_ibfk_1` FOREIGN KEY (`viewer_id`) REFERENCES `user` (`id`),
  CONSTRAINT `product_view_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_view`
--

LOCK TABLES `product_view` WRITE;
/*!40000 ALTER TABLE `product_view` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_view` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sede`
--

DROP TABLE IF EXISTS `sede`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sede` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `address` varchar(500) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `image` varchar(255) DEFAULT '',
  `maps` varchar(500) DEFAULT '',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sede`
--

LOCK TABLES `sede` WRITE;
/*!40000 ALTER TABLE `sede` DISABLE KEYS */;
INSERT INTO `sede` VALUES (1,'Blissfull Villa Roca','Villa Roca (sucursal principal)','+584122208256','','https://www.google.com/maps/place/Panader%C3%ADa,+Pizzer%C3%ADa+y+Empanadas+Alianza+Blissful+C.A/@10.0244691,-69.2549267,17z/data=!3m1!4b1!4m6!3m5!1s0x8e875fe03bd10c29:0xa291880f30904c28!8m2!3d10.0244638!4d-69.2523518!16s%2Fg%2F11fm4wfg6v?entry=ttu&g_ep=EgoyMDI2MDgxNi4wIKXMDSoASAFQAw%3D%3D',1,'2026-08-12 09:30:29'),(2,'Blissfull Cabudare','Cabudare (sucursal)',' 584120000002','','',0,'2026-08-12 09:30:29'),(3,'Blissfull Agua Viva','Agua Viva (sucursal)','+584126460149','','',1,'2026-08-12 09:30:29'),(4,'Blissfull Metrópolis','Metrópolis, Barquisimeto (sucursal)','+584120000004','','',1,'2026-08-19 12:14:24');
/*!40000 ALTER TABLE `sede` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sede_delivery_zone`
--

DROP TABLE IF EXISTS `sede_delivery_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sede_delivery_zone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sede_id` int(11) DEFAULT NULL,
  `delivery_zone_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sede_zone` (`sede_id`,`delivery_zone_id`),
  KEY `delivery_zone_id` (`delivery_zone_id`),
  CONSTRAINT `sede_delivery_zone_ibfk_1` FOREIGN KEY (`sede_id`) REFERENCES `sede` (`id`),
  CONSTRAINT `sede_delivery_zone_ibfk_2` FOREIGN KEY (`delivery_zone_id`) REFERENCES `delivery_zone` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sede_delivery_zone`
--

LOCK TABLES `sede_delivery_zone` WRITE;
/*!40000 ALTER TABLE `sede_delivery_zone` DISABLE KEYS */;
INSERT INTO `sede_delivery_zone` VALUES (1,1,1,1.00),(2,1,2,2.00),(3,1,3,2.50),(4,1,4,2.00),(5,1,5,3.50),(8,2,1,1.00),(9,3,1,1.00),(10,2,2,2.00),(11,3,2,2.00),(12,2,3,2.50),(13,3,3,2.50),(14,2,4,2.00),(15,3,4,2.00),(16,2,5,3.50),(17,3,5,3.50),(18,4,1,1.00),(19,4,2,2.00),(20,4,3,2.50),(21,4,4,2.00),(22,4,5,3.50);
/*!40000 ALTER TABLE `sede_delivery_zone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `slide`
--

DROP TABLE IF EXISTS `slide`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `slide` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `position` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `slide`
--

LOCK TABLES `slide` WRITE;
/*!40000 ALTER TABLE `slide` DISABLE KEYS */;
INSERT INTO `slide` VALUES (1,',,','valenegra.png',1,NULL,'2026-08-17 08:53:32');
/*!40000 ALTER TABLE `slide` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `status`
--

DROP TABLE IF EXISTS `status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `status`
--

LOCK TABLES `status` WRITE;
/*!40000 ALTER TABLE `status` DISABLE KEYS */;
INSERT INTO `status` VALUES (1,'Pendiente'),(2,'Pagado'),(3,'Cancelado'),(4,'Enviado'),(5,'Finalizado');
/*!40000 ALTER TABLE `status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unit`
--

DROP TABLE IF EXISTS `unit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unit`
--

LOCK TABLES `unit` WRITE;
/*!40000 ALTER TABLE `unit` DISABLE KEYS */;
INSERT INTO `unit` VALUES (1,'Pieza'),(2,'Kit'),(3,'Juego'),(4,'Caja'),(6,'Pizza Gigante'),(7,'Pizza Familiar'),(8,'Pizza Pequeña'),(9,'Plato'),(10,'Focaccia');
/*!40000 ALTER TABLE `unit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(60) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'Admin','','admin','','90b9aa7e25f80cf4f64e990b78a9fc5ebd6cecad',1,1,'2026-08-11 10:01:34');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-20  9:08:11
