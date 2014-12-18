-- MySQL dump 10.13  Distrib 5.5.38, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: bv
-- ------------------------------------------------------
-- Server version	5.5.38-0+wheezy1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `booklog`
--

DROP TABLE IF EXISTS `booklog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booklog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `book` int(10) unsigned NOT NULL,
  `type` tinyint(3) unsigned NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `book` (`book`)
) ENGINE=InnoDB AUTO_INCREMENT=1781 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `books`
--

DROP TABLE IF EXISTS `books`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `books` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `projectname` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `authors` text NOT NULL,
  `publisher` varchar(255) NOT NULL,
  `collection` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `dateprecision` tinyint(4) NOT NULL,
  `isbn` varchar(13) NOT NULL,
  `lang` char(3) NOT NULL,
  `license` int(11) NOT NULL DEFAULT '1',
  `changed` int(11) NOT NULL,
  `meta_ts` int(10) unsigned NOT NULL DEFAULT '0',
  `scan_ts` int(10) unsigned NOT NULL DEFAULT '0',
  `scantailor_ts` int(10) unsigned NOT NULL DEFAULT '0',
  `booktif_ts` int(10) unsigned NOT NULL DEFAULT '0',
  `bookpdf_ts` int(10) unsigned NOT NULL DEFAULT '0',
  `ocr_ts` int(10) unsigned NOT NULL DEFAULT '0',
  `odt_ts` int(10) unsigned NOT NULL DEFAULT '0',
  `epub_ts` int(11) NOT NULL,
  `attribs` text NOT NULL,
  `note` text NOT NULL,
  `locked` int(10) unsigned NOT NULL,
  `locktime` datetime NOT NULL,
  `status` int(10) unsigned DEFAULT '10',
  `privateid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `changed` (`changed`),
  KEY `collection` (`collection`),
  KEY `locked` (`locked`),
  KEY `locktime` (`locktime`),
  KEY `status` (`status`),
  FULLTEXT KEY `projectname` (`projectname`,`title`,`authors`,`publisher`,`isbn`,`lang`)
) ENGINE=MyISAM AUTO_INCREMENT=282 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `collections`
--

DROP TABLE IF EXISTS `collections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collections` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `license`
--

DROP TABLE IF EXISTS `license`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `license` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `free` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1 COMMENT='Nom des licences';
/*!40101 SET character_set_client = @saved_cs_client */;


LOCK TABLES `license` WRITE;
/*!40000 ALTER TABLE `license` DISABLE KEYS */;
INSERT INTO `license` VALUES (1,'Tous droits réservés',0),(2,'CC-by-nc-nd',1),(3,'CC-by-nd',1),(4,'CC-by-sa',1),(5,'CC-by',1),(6,'CC0',1),(7,'Domaine Public',1);
/*!40000 ALTER TABLE `license` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `login` varchar(128) NOT NULL,
  `pass` varchar(128) NOT NULL,
  `role` int(11) NOT NULL,
  `lastlogin` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;


LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Benjamin','Sonntag','benjamin@sonntag.fr','benjamin','oxOQ4qgzSkd46',1,'2014-12-17 21:13:32');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-12-18 15:51:36
