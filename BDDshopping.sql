CREATE DATABASE  IF NOT EXISTS `shopping` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `shopping`;
-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: shopping
-- ------------------------------------------------------
-- Server version	9.4.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `categoria`
--

DROP TABLE IF EXISTS `categoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categoria` (
  `IDcategoria` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(90) DEFAULT NULL,
  `nombre` varchar(90) DEFAULT NULL,
  PRIMARY KEY (`IDcategoria`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categoria`
--

LOCK TABLES `categoria` WRITE;
/*!40000 ALTER TABLE `categoria` DISABLE KEYS */;
INSERT INTO `categoria` VALUES (1,'usuario que hiso mas de 3 compras mensuales','Inicial'),(2,'usuario que tiene mas de 5 compras  mensuales','Premium'),(4,'usuario  registrado','Medium');
/*!40000 ALTER TABLE `categoria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `local`
--

DROP TABLE IF EXISTS `local`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `local` (
  `IDlocal` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(45) NOT NULL,
  `rubro` varchar(45) NOT NULL,
  `usuarioFK` int NOT NULL,
  `ubicacionFK` int DEFAULT NULL,
  `codigo` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`IDlocal`),
  KEY `usuario-local_idx` (`usuarioFK`),
  KEY `local-ubicacion_idx` (`ubicacionFK`),
  CONSTRAINT `local-ubicacion` FOREIGN KEY (`ubicacionFK`) REFERENCES `ubicacion` (`IDubicacion`),
  CONSTRAINT `usuario-local` FOREIGN KEY (`usuarioFK`) REFERENCES `usuario` (`IDusuario`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `local`
--

LOCK TABLES `local` WRITE;
/*!40000 ALTER TABLE `local` DISABLE KEYS */;
INSERT INTO `local` VALUES (1,'Local 2','vende ojotas',2,2,'Local1336'),(2,'Local 1','vender matess',10,1,'Local11233'),(3,'local 3','vvender zapatos',10,3,'Local33455'),(4,'local4','venta de rifas de chanchos',1,4,'lcoal45678'),(9,'wewewe','wewew',1,2,'LOCAL_68FD696004143');
/*!40000 ALTER TABLE `local` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `novedad`
--

DROP TABLE IF EXISTS `novedad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `novedad` (
  `IDnovedad` int NOT NULL AUTO_INCREMENT,
  `desde` date NOT NULL,
  `hasta` date NOT NULL,
  `usuarioHabilitado` varchar(45) NOT NULL,
  `descripcion` varchar(1000) DEFAULT NULL,
  `cabecera` varchar(3000) DEFAULT NULL,
  `cuerpo` varchar(7000) DEFAULT NULL,
  `imagen` blob,
  PRIMARY KEY (`IDnovedad`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `novedad`
--

LOCK TABLES `novedad` WRITE;
/*!40000 ALTER TABLE `novedad` DISABLE KEYS */;
INSERT INTO `novedad` VALUES (2,'2025-10-16','2025-10-28','Inicial','dsdsdsd','sdsdsdsdsdsdsd','dsdsdsdsdsdsdsdsddsd',NULL),(3,'2025-10-16','2025-10-29','Medium','dsdfsdfsdf','sdfsfddsfsd','sdfsdfsfsf',NULL),(4,'2025-10-16','2025-10-27','Inicial','edfwefwef','wedwefdwed','wedwedwe',NULL),(5,'2025-10-28','2025-10-30','Inicial','descripcion','titulos2','Mucho contenido',NULL),(6,'2025-10-25','2025-11-15','Medium','descripciondescripciondescripcion','titulos3','Mucho contenidoMucho contenidoMucho contenido',NULL),(7,'2025-10-25','2025-11-14','Premium','descripciondescripciondescripciondescripciondescripciondescripciondescripciondescripciondescripcion','titulos4','Mucho contenidoMucho contenidoMucho contenidoMucho contenidoMucho contenidoMucho contenidoMucho contenidoMucho contenidoMucho contenido',NULL);
/*!40000 ALTER TABLE `novedad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `promocion`
--

DROP TABLE IF EXISTS `promocion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `promocion` (
  `IDpromocion` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(45) NOT NULL,
  `desde` date NOT NULL,
  `hasta` date NOT NULL,
  `categoriaHabilitada` varchar(45) NOT NULL,
  `dia` int DEFAULT NULL,
  `estado` varchar(45) NOT NULL,
  `localFk` int NOT NULL,
  PRIMARY KEY (`IDpromocion`),
  KEY `local-promocion_idx` (`localFk`),
  CONSTRAINT `promocion-local` FOREIGN KEY (`localFk`) REFERENCES `local` (`IDlocal`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promocion`
--

LOCK TABLES `promocion` WRITE;
/*!40000 ALTER TABLE `promocion` DISABLE KEYS */;
INSERT INTO `promocion` VALUES (1,'descripcion promocion 1','2025-10-15','2025-10-30','Medium',2,'1',1),(2,'descripcion promocion 2','2025-10-15','2025-10-28','Premium',4,'1',2),(3,'Descripcion deun apromocion ','2025-10-15','2025-10-30','inicial',4,'1',3),(4,'Descripcion de prueba ','2025-10-15','2025-11-05','inicial',2,'1',2),(5,'descripcion de otra promo de otra tienda','2025-10-15','2025-11-05','inicial',2,'1',1),(6,'descripcion de otra promo de otra tienda','2025-10-15','2025-11-05','inicial',3,'1',3),(7,'Prueba desde el formulario','2025-10-22','2025-11-22','Inicial',5,'1',3),(11,'Prueba desde el formulari2','2025-10-22','2025-11-22','Medium',5,'0',1),(12,'descripcion de otra promo de otra tienda','2025-10-22','2025-10-22','Premium',2,'0',2),(13,'descripcion de otra otra  promo de otr','2025-10-22','2025-11-11','Medium',2,'0',2),(14,'PruebaFormulario','2025-10-26','2025-11-01','Medium',3,'0',2);
/*!40000 ALTER TABLE `promocion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rol`
--

DROP TABLE IF EXISTS `rol`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rol` (
  `IDrol` int NOT NULL,
  `nombre` varchar(90) NOT NULL,
  `descripcion` varchar(90) DEFAULT NULL,
  PRIMARY KEY (`IDrol`),
  UNIQUE KEY `nombre_UNIQUE` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rol`
--

LOCK TABLES `rol` WRITE;
/*!40000 ALTER TABLE `rol` DISABLE KEYS */;
INSERT INTO `rol` VALUES (0,'Administrador','persona encargada de llevar adelante el sistema'),(1,'Usuario','persona registrada en el sistema'),(2,'Comerciante','persona que tiene un comercio');
/*!40000 ALTER TABLE `rol` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `solicitud`
--

DROP TABLE IF EXISTS `solicitud`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `solicitud` (
  `IDsolicitud` int NOT NULL AUTO_INCREMENT,
  `rubro` varchar(45) NOT NULL,
  `cuil` varchar(45) NOT NULL,
  `nombre` varchar(45) NOT NULL,
  `email` varchar(45) NOT NULL,
  `contraseña` varchar(1500) NOT NULL,
  `dni` varchar(45) NOT NULL,
  `sexo` varchar(45) NOT NULL,
  `telefono` varchar(45) NOT NULL,
  `ubicacion` int NOT NULL,
  `nombreLocal` varchar(45) DEFAULT NULL,
  `estado` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`IDsolicitud`),
  KEY `solicitud-ubicacion_idx` (`ubicacion`),
  CONSTRAINT `solicitud-ubicacion` FOREIGN KEY (`ubicacion`) REFERENCES `ubicacion` (`IDubicacion`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `solicitud`
--

LOCK TABLES `solicitud` WRITE;
/*!40000 ALTER TABLE `solicitud` DISABLE KEYS */;
INSERT INTO `solicitud` VALUES (1,'Zapateria','20433492812','Hipolito la barba','labarbahipolito@gmail.com','Operacion9','43349282','Masculino','3436448814',1,'Local5','1'),(2,'Indumentaria Deportiva','20433492822','Hipolito la barba','labarbahipolito@gmail.com','Operacion9','43349283','Femenino','3436448814',3,'Local6','1'),(3,'Venta de utensillos de hogar','20433492832','Hipolito la barba','labarbahipolito@gmail.com','Operacion9','43349281','Masculino','3436448814',2,'local7','2'),(4,'Venta de utensillos de automotores','20433472832','jhon doe','jhonDoe@gmail.com','Operacion9','43349285','Masculino','3436448789',4,'local8','0'),(5,'servicio y venta de material de infrae','208934572832','jane doe','janeDoe@gmail.com','Operacion9','43347581','Femenino','3436448789',3,'local9','0'),(6,'NombreRubro','20433492812','pruebaForm ApellidoForm','Grupo4@gmail.com','Operacion9','47313281','Masculino','3436448814',4,'NombreLocaForm','0');
/*!40000 ALTER TABLE `solicitud` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ubicacion`
--

DROP TABLE IF EXISTS `ubicacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ubicacion` (
  `IDubicacion` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(45) NOT NULL,
  `Descripcion` varchar(500) NOT NULL,
  `estado` int NOT NULL,
  PRIMARY KEY (`IDubicacion`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ubicacion`
--

LOCK TABLES `ubicacion` WRITE;
/*!40000 ALTER TABLE `ubicacion` DISABLE KEYS */;
INSERT INTO `ubicacion` VALUES (1,'Local-1 planta baja','descripcion1',0),(2,'local-2 planta baja','descripcion2',0),(3,'local-3 planta alta','descripcion3',0),(4,'local-4 planta media','descripcion 5',0);
/*!40000 ALTER TABLE `ubicacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usopromocion`
--

DROP TABLE IF EXISTS `usopromocion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usopromocion` (
  `usuarioFk` int NOT NULL,
  `promoFK` int NOT NULL,
  `fechaUso` date DEFAULT NULL,
  `estado` varchar(45) NOT NULL,
  PRIMARY KEY (`usuarioFk`,`promoFK`),
  KEY `uso-promocion_idx` (`promoFK`),
  CONSTRAINT `uso-promocion` FOREIGN KEY (`promoFK`) REFERENCES `promocion` (`IDpromocion`),
  CONSTRAINT `uso-usuario` FOREIGN KEY (`usuarioFk`) REFERENCES `usuario` (`IDusuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usopromocion`
--

LOCK TABLES `usopromocion` WRITE;
/*!40000 ALTER TABLE `usopromocion` DISABLE KEYS */;
INSERT INTO `usopromocion` VALUES (9,3,'2025-10-16','1'),(9,6,'2025-10-22','1'),(10,3,'2025-10-22','0'),(10,14,'2025-10-25','0');
/*!40000 ALTER TABLE `usopromocion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `IDusuario` int NOT NULL AUTO_INCREMENT,
  `nombreUsuario` varchar(45) NOT NULL,
  `email` varchar(90) NOT NULL,
  `clave` varchar(600) NOT NULL,
  `telefono` varchar(45) DEFAULT NULL,
  `Sexo` varchar(45) DEFAULT NULL,
  `tipoFK` int NOT NULL,
  `categoriaFK` int NOT NULL,
  `estado` int DEFAULT NULL,
  `DNI` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`IDusuario`),
  KEY `usuario-rol_idx` (`tipoFK`),
  KEY `usuario-categoria_idx` (`categoriaFK`),
  CONSTRAINT `usuario-categoria` FOREIGN KEY (`categoriaFK`) REFERENCES `categoria` (`IDcategoria`),
  CONSTRAINT `usuario-rol` FOREIGN KEY (`tipoFK`) REFERENCES `rol` (`IDrol`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (1,'admin','admin@admin.com','Admin123','3436448814','Masculino',2,1,1,'43349281'),(2,'Hipolito la barba','labarbahipolito@gmail.com','Operacion9','3436448814','Masculino',1,1,1,'43349281'),(9,'Hipolito la barba','labarbahipolito7@gmail.com','$2y$10$ifopBS61YjpKxRJVF.THSOwck0qFcmsdmiB9PyuBErvGF0ZjlylTS','03436448814','Masculino',1,1,1,'43349281'),(10,'Hipolito la barba','tienda@gmail.com','$2y$10$OxIN0n.VvGLd6Ke7UrJyt.1Z0xxO3neaS8JDJ61AveVomheK5sBuS','03436448814','Masculino',2,1,1,'43349281'),(11,'Admin','hipolitoAdmin@gmail.com','$2y$10$ifopBS61YjpKxRJVF.THSOwck0qFcmsdmiB9PyuBErvGF0ZjlylTS','03436448814','Masculino',0,2,1,'43349281');
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-25 23:04:00
