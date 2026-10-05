CREATE DATABASE  IF NOT EXISTS `dealer_auto` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `dealer_auto`;
-- MySQL dump 10.13  Distrib 8.0.40, for macos14 (arm64)
--
-- Host: localhost    Database: dealer_auto
-- ------------------------------------------------------
-- Server version	9.1.0

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
-- Table structure for table `clienti`
--

DROP TABLE IF EXISTS `clienti`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clienti` (
  `id_client` int NOT NULL AUTO_INCREMENT,
  `nume_client` varchar(100) NOT NULL,
  `prenume_client` varchar(100) NOT NULL,
  `telefon_client` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `adresa` text,
  `tip_client` enum('privat','juridic') NOT NULL,
  PRIMARY KEY (`id_client`)
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clienti`
--

LOCK TABLES `clienti` WRITE;
/*!40000 ALTER TABLE `clienti` DISABLE KEYS */;
INSERT INTO `clienti` VALUES (101,'Popescu','Ion','0723456789','ion.popescu@email.com','Str. Mihai Eminescu, Nr. 10, București','privat'),(102,'Ionescu','Maria','0734567890','maria.ionescu@email.com','Str. Victoriei, Nr. 25, Cluj-Napoca','privat'),(103,'Georgescu','Alex','07453323','georgescu@gmail.com','Bucuresti, Sector 6','privat'),(104,'Dumitrescu','Andreea','0756789012','andreea.dumitrescu@email.com','Str. Republicii, Nr. 17, Iași','juridic'),(105,'Radu','Mihai','07000000','exemplu1@gmail.com','la mine acasa','juridic'),(106,'Marin','Elena','0778901234','elena.marin@email.com','Str. Libertății, Nr. 3, Constanța','privat'),(107,'alex','-','0757664736','raoullungulescu@gmail.com','Oras Baia de Arama, strada Marasesti','privat');
/*!40000 ALTER TABLE `clienti` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dotari`
--

DROP TABLE IF EXISTS `dotari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dotari` (
  `id_dotari` int NOT NULL AUTO_INCREMENT,
  `nume_dotare` varchar(100) NOT NULL,
  `descriere_dotare` text,
  `tip_dotare` enum('Standard','Optional','Premium') NOT NULL,
  `valoare` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_dotari`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dotari`
--

LOCK TABLES `dotari` WRITE;
/*!40000 ALTER TABLE `dotari` DISABLE KEYS */;
INSERT INTO `dotari` VALUES (60,'Aer condiționat','Sistem de aer condiționat automat','Optional',2000.00),(61,'Senzori parcare','Senzori pentru parcare, față și spate','Standard',800.00),(62,'Jante din aluminiu','Jante din aluminiu ușor și rezistent','Premium',1500.00),(63,'Sistem audio premium','Sistem audio de calitate superioară','Optional',2500.00),(64,'Faruri LED','Faruri de tip LED pentru o vizibilitate mai bună','Standard',1800.00),(65,'Scaune încălzite','Scaune încălzite pentru confort în sezonul rece','Premium',1200.00),(66,'Navigație GPS','Sistem de navigație cu hartă actualizată','Optional',2200.00),(67,'Cameră de marșarier','Cameră pentru asistarea manevrelor de parcare','Standard',1000.00),(68,'Volan încălzit','Volan cu sistem de încălzire pentru confort','Premium',1300.00),(69,'Asistență la frânare de urgență','Sistem de frânare automată în caz de urgență','Standard',3200.00);
/*!40000 ALTER TABLE `dotari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Furnizor`
--

DROP TABLE IF EXISTS `Furnizor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Furnizor` (
  `id_furnizor` int NOT NULL AUTO_INCREMENT,
  `nume_furnizor` varchar(255) NOT NULL,
  `adresa` varchar(255) DEFAULT NULL,
  `telefon` varchar(15) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `tip_furnizor` enum('dealer_auto','particular') NOT NULL,
  PRIMARY KEY (`id_furnizor`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Furnizor`
--

LOCK TABLES `Furnizor` WRITE;
/*!40000 ALTER TABLE `Furnizor` DISABLE KEYS */;
INSERT INTO `Furnizor` VALUES (1,'AutoDealers SRL','Strada Exemplu 1, București','0745000000','contact@autodealers.ro','dealer_auto'),(2,'CarWarehouse','Strada Test 10, Cluj-Napoca','0756000000','info@carwarehouse.ro','particular'),(3,'DriveNow','Aleea Noastra 12, Timișoara','0767000000','sales@drivenow.ro','particular');
/*!40000 ALTER TABLE `Furnizor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `masina_dotari`
--

DROP TABLE IF EXISTS `masina_dotari`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `masina_dotari` (
  `id_masina` int NOT NULL,
  `id_dotari` int NOT NULL,
  `nr_dotari_masina` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_masina`,`id_dotari`),
  KEY `id_dotari` (`id_dotari`),
  CONSTRAINT `masina_dotari_ibfk_1` FOREIGN KEY (`id_masina`) REFERENCES `masini` (`id_masina`) ON DELETE CASCADE,
  CONSTRAINT `masina_dotari_ibfk_2` FOREIGN KEY (`id_dotari`) REFERENCES `dotari` (`id_dotari`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `masina_dotari`
--

LOCK TABLES `masina_dotari` WRITE;
/*!40000 ALTER TABLE `masina_dotari` DISABLE KEYS */;
INSERT INTO `masina_dotari` VALUES (29,63,1),(30,64,4);
/*!40000 ALTER TABLE `masina_dotari` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `masini`
--

DROP TABLE IF EXISTS `masini`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `masini` (
  `id_masina` int NOT NULL AUTO_INCREMENT,
  `id_furnizor` int DEFAULT NULL,
  `id_tranzactie` int DEFAULT NULL,
  `Marca` varchar(50) NOT NULL,
  `Model` varchar(50) NOT NULL,
  `An_fabricatie` int NOT NULL,
  `Tip_Motor` enum('Diesel','Electric','Benzina') NOT NULL,
  `Putere_Motor` int NOT NULL,
  `Culoare` varchar(30) DEFAULT NULL,
  `Kilometraj` int DEFAULT NULL,
  `Disponibilitate` varchar(30) NOT NULL,
  `Pret_masina` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_masina`),
  KEY `fk_furnizor` (`id_furnizor`),
  KEY `fk_tranzactie` (`id_tranzactie`),
  CONSTRAINT `fk_furnizor` FOREIGN KEY (`id_furnizor`) REFERENCES `Furnizor` (`id_furnizor`),
  CONSTRAINT `fk_tranzactie` FOREIGN KEY (`id_tranzactie`) REFERENCES `tranzactii` (`id_tranzactie`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `masini`
--

LOCK TABLES `masini` WRITE;
/*!40000 ALTER TABLE `masini` DISABLE KEYS */;
INSERT INTO `masini` VALUES (29,2,NULL,'Volkswagen','Golf',2019,'Benzina',130,'Roșu',30000,'1',17000.00),(30,3,9,'Ford','Focus',2021,'Diesel',120,'Gri',10000,'1',20000.00),(32,1,NULL,'Honda','Civic',2018,'Benzina',140,'Portocaliu',35000,'1',14000.00),(34,3,5,'Nissan','Leaf',2023,'Electric',250,'Alb',0,'1',35000.00),(39,NULL,NULL,'BMW','Seria4',2010,'Diesel',300,'Alb',100000,'1',1000000.00),(40,NULL,9,'BMW','Seria4',2010,'Diesel',300,'Alb',100000,'1',1000000.00),(42,NULL,NULL,'Ford','Focus',2008,'Electric',75,'Gri Sobolan',20000,'1',999999.00);
/*!40000 ALTER TABLE `masini` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tranzactii`
--

DROP TABLE IF EXISTS `tranzactii`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tranzactii` (
  `id_tranzactie` int NOT NULL AUTO_INCREMENT,
  `id_client` int DEFAULT NULL,
  `data_vanzare` datetime NOT NULL,
  `pret` decimal(10,2) NOT NULL,
  `metoda_plata` enum('cash','card','rate') NOT NULL,
  PRIMARY KEY (`id_tranzactie`),
  KEY `id_client` (`id_client`),
  CONSTRAINT `tranzactii_ibfk_1` FOREIGN KEY (`id_client`) REFERENCES `clienti` (`id_client`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tranzactii`
--

LOCK TABLES `tranzactii` WRITE;
/*!40000 ALTER TABLE `tranzactii` DISABLE KEYS */;
INSERT INTO `tranzactii` VALUES (1,101,'2024-11-01 00:00:00',25000.00,'card'),(2,102,'2024-11-05 00:00:00',32000.00,'rate'),(4,104,'2024-11-15 00:00:00',28000.00,'card'),(5,105,'2024-11-18 00:00:00',20000.00,'card'),(6,106,'2024-11-20 00:00:00',45000.00,'rate'),(7,105,'2024-12-30 14:46:33',100000.00,'card'),(9,107,'2026-10-06 01:00:26',1000000.00,'card'),(10,107,'2026-10-06 01:03:22',239.00,'card');
/*!40000 ALTER TABLE `tranzactii` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `utilizatori`
--

DROP TABLE IF EXISTS `utilizatori`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `utilizatori` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `parola` varchar(255) NOT NULL,
  `role` enum('admin','furnizor') NOT NULL DEFAULT 'furnizor',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `utilizatori`
--

LOCK TABLES `utilizatori` WRITE;
/*!40000 ALTER TABLE `utilizatori` DISABLE KEYS */;
INSERT INTO `utilizatori` VALUES (1,'admin','a3a2754f94b4f8c1ca8d29290bc37ba90cedf0e13a9e702a829740835e5ed564','admin'),(2,'raoul','277ffe5ecc49ca3d27ab41c35b9ebc6cf3cc073c027b837406523dfddae0d8c1','furnizor'),(3,'tedy','aa58b21b01d6b8a99c1a5856962dbac36c758a79dc0a77c2e013ce2c39ecdc8a','furnizor'),(4,'tei','a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3','furnizor'),(5,'marcel','a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3','furnizor'),(6,'Popescu','912ee1d17d1d3594ebece22ed4bd433d510467e7b7b5723be8d49ed2457c7b2c','furnizor'),(13,'Radu','f62a17341e3875c73b72994c574c58fe7d30a644cd6f81e295d9cb3c9202304d','furnizor'),(17,'of','28391d3bc64ec15cbb090426b04aa6b7649c3cc85f11230bb0105e02d15e3624','furnizor'),(18,'eu','a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3','furnizor'),(20,'este','b3aa33b34e1b14e4cf3eded0cac6717a8385caa1e948448d0055e59eff4def69','furnizor'),(21,'mihai','63882f335c4129769c7821ee0b1831e3b49efbd5d954f9fe35845f29341fc64f','furnizor'),(22,'alex','4135aa9dc1b842a653dea846903ddb95bfb8c5a10c504a7fa16e10bc31d1fdf0','furnizor');
/*!40000 ALTER TABLE `utilizatori` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-06  1:20:32
