-- MySQL dump 10.15  Distrib 10.1.0-MariaDB, for Linux (x86_64)
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

-- Current Database: `db_Secure`
CREATE DATABASE /*!32312 IF NOT EXISTS*/ `db_Secure` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `db_Secure`;

-- Table structure for table `tb_Cargos`
DROP TABLE IF EXISTS `tb_Cargos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tb_Cargos` (
  `idCargo` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Cargo` varchar(64) NOT NULL,
  `Obs` text,
  `DtUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`idCargo`),
  UNIQUE KEY `Cargo` (`Cargo`),
  KEY `DtUpdate` (`DtUpdate`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

-- Table structure for table `tb_Domain`
DROP TABLE IF EXISTS `tb_Domain`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tb_Domain` (
  `idDomain` tinyint(4) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id da tabela',
  `Domain` varchar(50) NOT NULL DEFAULT '' COMMENT 'Nome do dominio. Vazio=Web',
  `Obs` text,
  `DtUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`idDomain`),
  UNIQUE KEY `Domain` (`Domain`),
  KEY `DtUpdate` (`DtUpdate`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

-- Dumping data for table `tb_Domain`
LOCK TABLES `tb_Domain` WRITE;
/*!40000 ALTER TABLE `tb_Domain` DISABLE KEYS */;
INSERT INTO `tb_Domain` VALUES 
(1,'','Web','2014-04-13 14:36:13'),
(2,'@ws','Webservice','2014-04-13 14:37:00'),
(3,'INTERNAL',NULL,'2014-11-05 12:56:37');
/*!40000 ALTER TABLE `tb_Domain` ENABLE KEYS */;
UNLOCK TABLES;

-- Table structure for table `tb_Permitions`
DROP TABLE IF EXISTS `tb_Permitions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tb_Permitions` (
  `idPermition` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idGrpUsr` smallint(6) unsigned NOT NULL COMMENT '[Grp.Users]Id de Grupo de Usuarios<?\r\n$this->element="ElementCombo";\r\n$this->sql="tb_GrpUsr";\r\n$this->order="Nome";\r\n$this->fields="Nome";\r\n?>',
  `idGrpFile` smallint(6) unsigned NOT NULL COMMENT '[Grp.Files]Id de Grupo de Arquivos<?\r\n$this->element="ElementCombo";\r\n$this->sql="tb_GrpFile";\r\n$this->order=''Nome'';\r\n$this->fields=''Nome'';\r\n?>',
  `C` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Permissao Criar (Create)',
  `R` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Permissao Ler (Read)',
  `U` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Permissao Escrever (Update)',
  `D` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Permissao Exclusao (Delete)',
  `S` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Permissoo Especial (Special)',
  `CRUDS` tinyint(2) unsigned DEFAULT NULL,
  `Obs` text,
  `DtUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`idPermition`),
  UNIQUE KEY `idGrpUsr` (`idGrpUsr`,`idGrpFile`),
  KEY `DtUpdate` (`DtUpdate`),
  KEY `idGrpFile` (`idGrpFile`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AVG_ROW_LENGTH=23;
/*!40101 SET character_set_client = @saved_cs_client */;

-- Dumping triggers for table `tb_Permitions`
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`admin`@`%`*/ /*!50003 TRIGGER tr_tb_Permitions_before_ins
	BEFORE INSERT
	ON tb_Permitions
	FOR EACH ROW
BEGIN
	SET NEW.CRUDS=fn_Any_BuildCRUD(NEW.C,NEW.R,NEW.U,NEW.D,NEW.S);
END */;;
DELIMITER ;

DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`admin`@`%`*/ /*!50003 TRIGGER tr_tb_Permitions_before_upd
	BEFORE UPDATE
	ON tb_Permitions
	FOR EACH ROW
BEGIN
	SET NEW.CRUDS=fn_Any_BuildCRUD(NEW.C,NEW.R,NEW.U,NEW.D,NEW.S);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

-- Table structure for table `tb_Files_x_tb_GrpFile`
DROP TABLE IF EXISTS `tb_Files_x_tb_GrpFile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tb_Files_x_tb_GrpFile` (
  `idFile` int(11) unsigned NOT NULL COMMENT 'Id do arquivo',
  `idGrpFile` smallint(6) unsigned NOT NULL COMMENT 'Id do grupo de arquivos',
  `DtUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`idFile`,`idGrpFile`),
  KEY `DtUpdate` (`DtUpdate`),
  KEY `idGrpFile` (`idGrpFile`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

-- Table structure for table `tb_Files`
DROP TABLE IF EXISTS `tb_Files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tb_Files` (
  `idFile` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id da tabela',
  `File` varchar(255) NOT NULL COMMENT 'Nome do arquivo com caminho completo protocolo://domain/path/file',
  `C` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Permissao Criar (Create)',
  `R` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Permissao Ler (Read)',
  `U` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Permissao Escrever (Update)',
  `D` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Permissao Exclusao (Delete)',
  `S` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Permissao Executar (Special)',
  `L` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Nivel de Compara (Level)\r\n<?\r\n$this->element="ElementCombo";\r\n$this->source=array("Free","Secured","Paranoic");\r\n?>',
  `CRUDS` tinyint(2) unsigned DEFAULT NULL COMMENT '<?$this->edit=>false;?>',
  `Obs` text,
  `DtUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '<?$this->edit=>false;?>',
  `DtGer` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '[Geracao]\r\n<?$this->edit=>false;?>',
  PRIMARY KEY (`idFile`),
  UNIQUE KEY `File` (`File`),
  KEY `DtUpdate` (`DtUpdate`),
  KEY `L` (`L`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

-- Dumping triggers for table `tb_Files`
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`admin`@`%`*/ /*!50003 TRIGGER tr_tb_Files_before_ins
	BEFORE INSERT
	ON tb_Files
	FOR EACH ROW
BEGIN
	SET NEW.DtGer=NOW();
	SET NEW.CRUDS=NEW.C<<4 | NEW.R<<3 | NEW.U<<2 | NEW.D<<1 | NEW.S;
END */;;
DELIMITER ;

DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`admin`@`%`*/ /*!50003 TRIGGER tr_tb_Files_after_ins
	AFTER INSERT
	ON tb_Files
	FOR EACH ROW
BEGIN
	INSERT IGNORE db_Secure.tb_Files_x_tb_GrpFile (idFile,idGrpFile) 
	VALUES (NEW.idFile,1);
END */;;
DELIMITER ;

DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`admin`@`%`*/ /*!50003 TRIGGER tr_tb_Files_before_upd
	BEFORE UPDATE
	ON tb_Files
	FOR EACH ROW
BEGIN
	SET NEW.CRUDS=NEW.C<<4 | NEW.R<<3 | NEW.U<<2 | NEW.D<<1 | NEW.S;
END */;;
DELIMITER ;

DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`admin`@`%`*/ /*!50003 TRIGGER tr_tb_Files_after_del
	AFTER DELETE
	ON tb_Files
	FOR EACH ROW
BEGIN
	DELETE FROM tb_Files_x_tb_GrpFile WHERE idFile=OLD.idFile;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

-- Table structure for table `tb_GrpFile`
DROP TABLE IF EXISTS `tb_GrpFile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tb_GrpFile` (
  `idGrpFile` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id da tabela',
  `GrpFile` varchar(64) NOT NULL COMMENT 'Nome do grupo de arquivos',
  `Obs` text,
  `DtUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`idGrpFile`),
  UNIQUE KEY `GrpFile` (`GrpFile`),
  KEY `DtUpdate` (`DtUpdate`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

-- Dumping data for table `tb_GrpFile`
LOCK TABLES `tb_GrpFile` WRITE;
/*!40000 ALTER TABLE `tb_GrpFile` DISABLE KEYS */;
INSERT INTO `tb_GrpFile` VALUES 
(1,'Todos',NULL,'2014-05-06 21:15:39'),
(2,'Admin',NULL,'2014-05-06 21:16:21'),
(3,'WebService','WebService','2010-06-09 17:33:50');
/*!40000 ALTER TABLE `tb_GrpFile` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`admin`@`%`*/ /*!50003 TRIGGER tr_tb_GrpFile_before_del
	BEFORE DELETE
	ON tb_GrpFile
	FOR EACH ROW
BEGIN
	IF(OLD.idGrpFile<4)THEN
		CALL fail('Não pode ser Apagado');
	END IF;
END */;;
DELIMITER ;

DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`admin`@`%`*/ /*!50003 TRIGGER tr_tb_GrpFile_after_del
	AFTER DELETE
	ON tb_GrpFile
	FOR EACH ROW
BEGIN
	DELETE FROM tb_Permitions WHERE idGrpFile=OLD.idGrpFile;
	DELETE FROM tb_Files_x_tb_GrpFile WHERE idGrpFile=OLD.idGrpFile;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

-- Table structure for table `tb_GrpUsr`
DROP TABLE IF EXISTS `tb_GrpUsr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tb_GrpUsr` (
  `idGrpUsr` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id da tabela',
  `GrpUsr` varchar(64) NOT NULL COMMENT 'Nome do grupo de usuarios',
  `Obs` text,
  `DtUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`idGrpUsr`),
  UNIQUE KEY `GrpUsr` (`GrpUsr`),
  KEY `DtUpdate` (`DtUpdate`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 AVG_ROW_LENGTH=34;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_GrpUsr`
--

LOCK TABLES `tb_GrpUsr` WRITE;
/*!40000 ALTER TABLE `tb_GrpUsr` DISABLE KEYS */;
INSERT INTO `tb_GrpUsr` VALUES (1,'Todos',NULL,'2014-05-06 21:16:46'),(2,'Admin',NULL,'2014-05-06 21:17:00'),(3,'Users',NULL,'2014-05-06 21:17:03'),(4,'WebService',NULL,'2010-06-09 18:04:05');
/*!40000 ALTER TABLE `tb_GrpUsr` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`admin`@`%`*/ /*!50003 TRIGGER tr_tb_GrpUsr_before_del
	BEFORE DELETE
	ON tb_GrpUsr
	FOR EACH ROW
BEGIN
	IF(OLD.idGrpUsr<5)THEN
		CALL fail('Não pode ser apagado');
	END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`admin`@`%`*/ /*!50003 TRIGGER tr_tb_GrpUsr_after_del
	AFTER DELETE
	ON tb_GrpUsr
	FOR EACH ROW
BEGIN
	DELETE FROM tb_Permitions WHERE idGrpUsr=OLD.idGrpUsr;
	DELETE FROM tb_Users_x_tb_GrpUsr WHERE idGrpUsr=OLD.idGrpUsr;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `tb_TryLogin`
--

DROP TABLE IF EXISTS `tb_TryLogin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tb_TryLogin` (
  `IP` varchar(39) NOT NULL,
  `User` varchar(50) DEFAULT NULL,
  `Password` varchar(50) DEFAULT NULL,
  `Url` varchar(255) DEFAULT NULL,
  `DtUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`IP`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_TryLogin`
--

LOCK TABLES `tb_TryLogin` WRITE;
/*!40000 ALTER TABLE `tb_TryLogin` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_TryLogin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tb_Users`
--

DROP TABLE IF EXISTS `tb_Users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tb_Users` (
  `idUser` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id da tabela\r\n<?$this->hidden=false;?>',
  `idDomain` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '[Domain]Id do Dominio\r\n<?\r\n$this->element="ElementCombo";\r\n$this->sql="tb_Domain";\r\n$this->Order="Domain";\r\n$this->fields="Domain";\r\n?>',
  `User` varchar(64) NOT NULL COMMENT 'Nome de usuario\r\n<?\r\n$this->width="200px";\r\n?>',
  `Ativo` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Funcionario',
  `DtUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '<?$this->edit=>false;?>',
  `DtGer` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '[Geracao]\r\n<?$this->edit=>false;?>',
  PRIMARY KEY (`idUser`),
  UNIQUE KEY `Login` (`idDomain`,`User`),
  KEY `Ativo` (`Ativo`),
  KEY `DtUpdate` (`DtUpdate`),
  KEY `User` (`User`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AVG_ROW_LENGTH=31;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_Users`
--

LOCK TABLES `tb_Users` WRITE;
/*!40000 ALTER TABLE `tb_Users` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_Users` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`admin`@`%`*/ /*!50003 TRIGGER tr_tb_Users_beforer_ins
	BEFORE INSERT
	ON tb_Users
	FOR EACH ROW
BEGIN
	SET NEW.DtGer=NOW();
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`admin`@`%`*/ /*!50003 TRIGGER tr_tb_Users_after_ins
	AFTER INSERT
	ON tb_Users
	FOR EACH ROW
BEGIN
	INSERT IGNORE tb_Users_Passwd   SET idUser=NEW.idUser;
	INSERT IGNORE tb_Users_Confirm  SET idUser=NEW.idUser;
	INSERT IGNORE tb_Users_Token    SET idUser=NEW.idUser;
	INSERT IGNORE tb_Users_TryLogin SET idUser=NEW.idUser, DtUpdate='1970-01-01';
	IF(NEW.idDomain=2)THEN
		INSERT IGNORE tb_Users_x_tb_GrpUsr SET idUser=NEW.idUser, idGrpUsr=4;
	END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`admin`@`%`*/ /*!50003 TRIGGER tr_tb_Users_after_del
	AFTER DELETE
	ON tb_Users
	FOR EACH ROW
BEGIN
	DELETE FROM tb_Users_Confirm WHERE idUser=OLD.idUser;
	DELETE FROM tb_Users_Detail WHERE idUser=OLD.idUser;
	DELETE FROM tb_Users_Emails WHERE idUser=OLD.idUser;
	DELETE FROM tb_Users_Enderecos WHERE idUser=OLD.idUser;
	DELETE FROM tb_Users_Ip WHERE idUser=OLD.idUser;
	DELETE FROM tb_Users_Passwd WHERE idUser=OLD.idUser;
	DELETE FROM tb_Users_Telefones WHERE idUser=OLD.idUser;
	DELETE FROM tb_Users_x_tb_GrpUsr WHERE idUser=OLD.idUser;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `tb_Users_Confirm`
--

DROP TABLE IF EXISTS `tb_Users_Confirm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tb_Users_Confirm` (
  `idUser` int(11) unsigned NOT NULL COMMENT 'Id da tabela<?$this->hidden=false;?>',
  `Confirm` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `DtUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualizacao <?$this->edit=false;?>',
  PRIMARY KEY (`idUser`),
  KEY `Confirm` (`Confirm`),
  KEY `DtUpdate` (`DtUpdate`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AVG_ROW_LENGTH=10;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_Users_Confirm`
--

LOCK TABLES `tb_Users_Confirm` WRITE;
/*!40000 ALTER TABLE `tb_Users_Confirm` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_Users_Confirm` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`admin`@`%`*/ /*!50003 TRIGGER tr_tb_Users_Confirm_after_upd
	AFTER UPDATE
	ON tb_Users_Confirm
	FOR EACH ROW
BEGIN
	IF(NEW.Confirm=1)THEN
		UPDATE tb_Users SET Ativo=1 WHERE idUser=NEW.idUser;
	END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `tb_Users_Detail`
--

DROP TABLE IF EXISTS `tb_Users_Detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tb_Users_Detail` (
  `idUser` int(11) unsigned NOT NULL COMMENT 'Id da tabela<?$this->hidden=false;?>',
  `Nome` varchar(64) DEFAULT NULL,
  `Sexo` enum('','Male','Female') DEFAULT NULL,
  `idGestor` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '[Gestor]',
  `idCargo` int(11) unsigned NOT NULL DEFAULT '195' COMMENT '[Cargo]<?\r\n$this->element="ElementCombo";\r\n$this->sql="tb_Cargos";\r\n$this->order=''Cargo'';\r\n$this->fields=''Cargo'';\r\n$this->width=''400px'';\r\n?>',
  `Matricula` varchar(20) NOT NULL DEFAULT '',
  `Niver` date DEFAULT NULL,
  `CentroCusto` varchar(10) NOT NULL COMMENT '<?$this->width=''70px'';?>',
  `Obs` text,
  `DtUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`idUser`),
  KEY `DtUpdate` (`DtUpdate`),
  KEY `idCargo` (`idCargo`),
  KEY `idGestor` (`idGestor`),
  KEY `Matricula` (`Matricula`),
  KEY `Niver` (`Niver`),
  KEY `Nome` (`Nome`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_Users_Detail`
--

LOCK TABLES `tb_Users_Detail` WRITE;
/*!40000 ALTER TABLE `tb_Users_Detail` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_Users_Detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tb_Users_Emails`
--

DROP TABLE IF EXISTS `tb_Users_Emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tb_Users_Emails` (
  `idUser` int(11) unsigned NOT NULL COMMENT 'Id da tabela<?$this->hidden=false;?>',
  `Email` varchar(64) NOT NULL COMMENT '<?$this->element=''ElementEmail'';$this->fn=''Links::mailto'';?>',
  `EmailType` enum('Business','Home','Other') NOT NULL DEFAULT 'Business',
  `Confirm` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `DtUpdate` timestamp NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`idUser`,`Email`),
  UNIQUE KEY `Email` (`Email`),
  KEY `Confirm` (`Confirm`),
  KEY `DtUpdate` (`DtUpdate`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_Users_Emails`
--

LOCK TABLES `tb_Users_Emails` WRITE;
/*!40000 ALTER TABLE `tb_Users_Emails` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_Users_Emails` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`admin`@`%`*/ /*!50003 TRIGGER tr_tb_Users_Emails_before_upd
	BEFORE UPDATE
	ON tb_Users_Emails
	FOR EACH ROW
BEGIN
	IF(NEW.Email!=OLD.Email)THEN
		SET NEW.Confirm=0;
	END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`admin`@`%`*/ /*!50003 TRIGGER tr_tb_Users_Emails_after_upd
	AFTER UPDATE
	ON tb_Users_Emails
	FOR EACH ROW
BEGIN
	IF(NEW.Confirm=1 AND NEW.Confirm!=OLD.Confirm)THEN
		UPDATE db_Secure.tb_Users_Confirm SET Confirm=1 WHERE idUser=NEW.idUser;
	END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `tb_Users_Enderecos`
--

DROP TABLE IF EXISTS `tb_Users_Enderecos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tb_Users_Enderecos` (
  `idUser` int(11) unsigned NOT NULL COMMENT 'Id da tabela<?$this->hidden=false;?>',
  `Pop` varchar(16) NOT NULL DEFAULT 'Principal' COMMENT 'Tipo do Local',
  `EndType` enum('Business','Home','Other') NOT NULL DEFAULT 'Business',
  `Logradouro` varchar(255) DEFAULT NULL,
  `Num` varchar(16) DEFAULT NULL,
  `Complemento` varchar(64) DEFAULT NULL,
  `Bairro` varchar(64) DEFAULT NULL,
  `Cidade` varchar(64) DEFAULT NULL,
  `Uf` char(2) DEFAULT NULL,
  `Pais` varchar(32) NOT NULL DEFAULT 'Brasil',
  `Obs` text,
  `DtUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `DtGer` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`idUser`,`Pop`),
  KEY `DtUpdate` (`DtUpdate`),
  KEY `Pop` (`Pop`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_Users_Enderecos`
--

LOCK TABLES `tb_Users_Enderecos` WRITE;
/*!40000 ALTER TABLE `tb_Users_Enderecos` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_Users_Enderecos` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`admin`@`%`*/ /*!50003 TRIGGER tr_tb_Users_Enderecos_before_inss
	BEFORE INSERT
	ON tb_Users_Enderecos
	FOR EACH ROW
BEGIN
	SET NEW.DtGer=NOW();
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `tb_Users_Ip`
--

DROP TABLE IF EXISTS `tb_Users_Ip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tb_Users_Ip` (
  `idUser` int(11) unsigned NOT NULL COMMENT 'Id da tabela<?$this->hidden=false;?>',
  `Ip` varchar(39) DEFAULT NULL COMMENT '<?$this->edit=false;?>',
  `DtUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualizacao <?$this->edit=false;?>',
  PRIMARY KEY (`idUser`),
  KEY `DtUpdate` (`DtUpdate`),
  KEY `Ip` (`Ip`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_Users_Ip`
--

LOCK TABLES `tb_Users_Ip` WRITE;
/*!40000 ALTER TABLE `tb_Users_Ip` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_Users_Ip` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tb_Users_Passwd`
--

DROP TABLE IF EXISTS `tb_Users_Passwd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tb_Users_Passwd` (
  `idUser` int(11) unsigned NOT NULL COMMENT 'Id da tabela<?$this->hidden=false;?>',
  `Passwd` varchar(64) NOT NULL DEFAULT '' COMMENT '<?$this->element="ElementPasswd"; $this->width=''200px'';?>',
  `DtExpires` datetime DEFAULT NULL,
  `DtUpdate` timestamp NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`idUser`),
  KEY `DtExpires` (`DtExpires`),
  KEY `DtUpdate` (`DtUpdate`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AVG_ROW_LENGTH=36;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_Users_Passwd`
--

LOCK TABLES `tb_Users_Passwd` WRITE;
/*!40000 ALTER TABLE `tb_Users_Passwd` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_Users_Passwd` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`admin`@`%`*/ /*!50003 TRIGGER tr_tb_Users_Passwd_before_ins
	BEFORE INSERT
	ON tb_Users_Passwd
	FOR EACH ROW
BEGIN
	IF (IFNULL(NEW.Passwd,'')='') THEN
		SET NEW.Passwd=fn_user_key(fn_user_getRandPasswd(12),1);
		SET NEW.DtExpires=NOW();
	
	END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`admin`@`%`*/ /*!50003 TRIGGER tr_tb_Users_Passwd_before_upd
	BEFORE UPDATE
	ON tb_Users_Passwd
	FOR EACH ROW
BEGIN
	IF (IFNULL(NEW.Passwd,'')='') THEN
		SET NEW.Passwd=fn_user_key(fn_user_getRandPasswd(12),1);
		SET NEW.DtExpires=NOW();
	
	END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `tb_Users_Telefones`
--

DROP TABLE IF EXISTS `tb_Users_Telefones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tb_Users_Telefones` (
  `idUser` int(11) unsigned NOT NULL COMMENT 'Id da tabela<?$this->hidden=false;?>',
  `Telefone` varchar(20) NOT NULL COMMENT '<?$this->element=''ElementTelefone'';?>',
  `TipoContato` enum('Mobile','Home','Business','Fax','Ramal') NOT NULL DEFAULT 'Mobile',
  `Obs` text,
  `DtUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `DtGer` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`idUser`,`Telefone`),
  KEY `DtUpdate` (`DtUpdate`),
  KEY `Telefone` (`Telefone`),
  KEY `TipoContato` (`TipoContato`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_Users_Telefones`
--

LOCK TABLES `tb_Users_Telefones` WRITE;
/*!40000 ALTER TABLE `tb_Users_Telefones` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_Users_Telefones` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`admin`@`%`*/ /*!50003 TRIGGER tr_tb_Users_Telefones_before_ins
	BEFORE INSERT
	ON tb_Users_Telefones
	FOR EACH ROW
BEGIN
	SET NEW.DtGer=NOW();
	
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `tb_Users_Token`
--

DROP TABLE IF EXISTS `tb_Users_Token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tb_Users_Token` (
  `idUser` int(11) unsigned NOT NULL COMMENT 'Id da tabela<?$this->hidden=false;?>',
  `Token` char(32) DEFAULT NULL,
  `DtUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualizacao <?$this->edit=false;?>',
  PRIMARY KEY (`idUser`),
  KEY `DtUpdate` (`DtUpdate`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_Users_Token`
--

LOCK TABLES `tb_Users_Token` WRITE;
/*!40000 ALTER TABLE `tb_Users_Token` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_Users_Token` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tb_Users_TryLogin`
--

DROP TABLE IF EXISTS `tb_Users_TryLogin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tb_Users_TryLogin` (
  `idUser` int(11) unsigned NOT NULL COMMENT 'Id da tabela<?$this->hidden=false;?>',
  `DtUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data de atualizacao <?$this->edit=false;?>',
  PRIMARY KEY (`idUser`),
  KEY `DtUpdate` (`DtUpdate`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_Users_TryLogin`
--

LOCK TABLES `tb_Users_TryLogin` WRITE;
/*!40000 ALTER TABLE `tb_Users_TryLogin` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_Users_TryLogin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tb_Users_x_tb_GrpUsr`
--

DROP TABLE IF EXISTS `tb_Users_x_tb_GrpUsr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tb_Users_x_tb_GrpUsr` (
  `idUser` int(11) unsigned NOT NULL COMMENT 'Id do usuario',
  `idGrpUsr` smallint(6) unsigned NOT NULL COMMENT '[Grp.Users]Id de Grupo de Usuario <?\r\n$this->element="ElementCombo";\r\n$this->sql="tb_GrpUsr";\r\n$this->order="GrpUsr";\r\n$this->fields="GrpUsr";\r\n?>',
  `isMain` tinyint(1) unsigned DEFAULT NULL,
  `Obs` text,
  `DtUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`idUser`,`idGrpUsr`),
  UNIQUE KEY `isMain` (`isMain`,`idUser`),
  KEY `DtUpdate` (`DtUpdate`),
  KEY `idGrpUsr` (`idGrpUsr`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AVG_ROW_LENGTH=20;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_Users_x_tb_GrpUsr`
--

LOCK TABLES `tb_Users_x_tb_GrpUsr` WRITE;
/*!40000 ALTER TABLE `tb_Users_x_tb_GrpUsr` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_Users_x_tb_GrpUsr` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `vw_digits`
--

DROP TABLE IF EXISTS `vw_digits`;
/*!50001 DROP VIEW IF EXISTS `vw_digits`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `vw_digits` (
  `d` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Dumping events for database 'db_Secure'
--

--
-- Dumping routines for database 'db_Secure'
--
/*!50003 DROP FUNCTION IF EXISTS `fn_File_Create` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` FUNCTION `fn_File_Create`(in_File VARCHAR(255)) RETURNS int(11) unsigned
    NO SQL
BEGIN
	INSERT tb_Files (`File`) VALUES (in_File);
	RETURN LAST_INSERT_ID();
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `fn_File_GetId` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` FUNCTION `fn_File_GetId`(in_File VARCHAR(255)) RETURNS int(11) unsigned
    NO SQL
BEGIN
	RETURN (SELECT idFile FROM tb_Files WHERE File=in_File);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `fn_MyDoc_ParserComment` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` FUNCTION `fn_MyDoc_ParserComment`(in_ObjType CHAR(2), in_Obj VARCHAR(64), ObjCreate LONGTEXT) RETURNS tinyint(1) unsigned
    MODIFIES SQL DATA
    DETERMINISTIC
    COMMENT 'Parser Comments to MyDoc'
BEGIN
	DECLARE p INTEGER(11) UNSIGNED;
	DECLARE oRet VARCHAR(64) DEFAULT NULL;
	DECLARE oVar VARCHAR(64) DEFAULT NULL;
	DECLARE oType LONGTEXT DEFAULT NULL;
	DECLARE descr LONGTEXT DEFAULT NULL;
	
	SET ObjCreate=REPLACE(REPLACE(REPLACE(ObjCreate,'\r','\n'),'\n\n','\n'),'\t','    ');
	SET p=INSTR(ObjCreate,'/**\n');
	
	IF(p=0)THEN RETURN 0; END IF;
	SET ObjCreate=TRIM(SUBSTRING(ObjCreate,p+5));
	
	SET p=IFNULL(INSTR(ObjCreate,'**/\n'),0);
	IF(p=0)THEN SET p=IFNULL(INSTR(ObjCreate,'**/'),0); END IF;
	IF(p=0)THEN SET p=IFNULL(INSTR(ObjCreate,'*/'),0); END IF;
	IF(p=0)THEN RETURN 0; END IF;
	SET ObjCreate=LEFT(ObjCreate,p-1);
	
	REPEAT
		SET p=IFNULL(INSTR(ObjCreate,'\n'),0);
		IF(p=0)THEN
			SET descr=ObjCreate;
			SET ObjCreate=NULL;
		ELSE
			SET descr=IF(p=1,NULL,LEFT(ObjCreate,p-1));
			SET ObjCreate=TRIM(SUBSTRING(ObjCreate,p+1));
		END IF;
		
		IF(descr RLIKE '^ *\\* *@[a-z]+ ')THEN
			SET descr=SUBSTRING(descr,IFNULL(INSTR(descr,'@'),0)+1);
			SET p=IFNULL(INSTR(descr,' '),0);
			SET oRet=LEFT(descr,p-1);
			SET oVar=NULL;
			SET oType=NULL;
			SET descr=TRIM(SUBSTRING(descr,p+1));
			
			IF(oRet LIKE 'description%')THEN
				UPDATE tmp_MyDoc 
				SET Description=CONCAT_WS('\n',Description,descr)
				WHERE ObjType=in_ObjType AND Obj=in_Obj;
			ELSE
				SET p=IFNULL(INSTR(descr,' '),0);
				IF(p=0)THEN
					IF NOT(in_ObjType='fn' AND oRet='return')THEN
						SET oVar=descr;
						SET descr=NULL;
					END IF;
				ELSE
					SET oVar=LEFT(descr,p-1);
					SET descr=TRIM(SUBSTRING(descr,p+1));
				END IF;
				SET p=IFNULL(INSTR(descr,']'),0);
				IF(LEFT(descr,1)='[' AND p!=0)THEN
					SET oType=TRIM(SUBSTRING(descr,2,p-2));
					SET descr=TRIM(SUBSTRING(descr,p+1));
				END IF;
				IF(oRet='parameter')THEN
					IF(oVar IS NOT NULL)THEN
						UPDATE tmp_MyDoc 
						SET Description=descr
						WHERE ObjType='f' AND Dad=in_Obj AND Ret=oRet AND Var=oVar;
					END IF;
				ELSE
					INSERT IGNORE tmp_MyDoc (ObjType,Obj,Dad,Ret,Var,`Type`,Description)
					VALUES ('c',CONCAT_WS('.',in_Obj,CONCAT('`',oRet,'`'),CONCAT('`',oVar,'`')),in_Obj,oRet,oVar,oType,descr);
				END IF;
			END IF;
		END IF;
	UNTIL IFNULL(ObjCreate,'')='' END REPEAT;

	RETURN 1;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `fn_Permition_CRUDS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` FUNCTION `fn_Permition_CRUDS`(in_Level tinyint(1) UNSIGNED, in_fCRUDS tinyint(2) UNSIGNED, in_pCRUDS tinyint(2) UNSIGNED) RETURNS tinyint(2) unsigned
    NO SQL
BEGIN
	
	DECLARE o_CRUDS tinyint(2) UNSIGNED;

	IF (in_Level = 0) THEN
		RETURN 31 & in_fCRUDS;
	END IF;
	SET o_CRUDS = in_pCRUDS & in_fCRUDS;
	IF (in_Level = 1 OR (o_CRUDS = in_fCRUDS AND o_CRUDS > 0)) THEN
		RETURN o_CRUDS;
	END IF;
	RETURN 0;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `fn_Permition_File_by_File_idUser` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` FUNCTION `fn_Permition_File_by_File_idUser`(in_File VARCHAR(255),in_idUser INT(11) UNSIGNED) RETURNS tinyint(2) unsigned
    NO SQL
BEGIN
	RETURN (
		SELECT fn_Permition_CRUDS(f.Nivel,f.CRUDS,BIT_OR(p.CRUDS)) CRUDS
		FROM tb_Files f
		JOIN tb_Files_x_tb_GrpFile gf ON f.idFile=gf.idFile
		JOIN tb_Permitions p ON gf.idGrpFile=p.idGrpFile
		LEFT JOIN tb_Users_x_tb_GrpUsr gu ON p.idGrpUsr=gu.idGrpUsr 
		LEFT JOIN tb_Users u ON gu.idUser=u.idUser
		WHERE f.File=in_File
		AND (
			p.idGrpUsr=1 OR 
			(gu.idUser=in_idUser AND u.Ativo) OR 
			(p.idGrpUsr=3 AND in_idUser!=0 AND (SELECT Ativo FROM tb_Users WHERE idUser=in_idUser)) 
		) 
		GROUP BY f.idFile
	);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `fn_Permition_File_by_idFile_idUser` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` FUNCTION `fn_Permition_File_by_idFile_idUser`(in_idFile INT(11) UNSIGNED,in_idUser INT(11) UNSIGNED) RETURNS tinyint(2) unsigned
    NO SQL
BEGIN
	RETURN (
		SELECT fn_Permition_CRUDS(f.Nivel,f.CRUDS,BIT_OR(p.CRUDS)) CRUDS
		FROM tb_Files f
		JOIN tb_Files_x_tb_GrpFile gf ON f.idFile=gf.idFile
		JOIN tb_Permitions p ON gf.idGrpFile=p.idGrpFile
		LEFT JOIN tb_Users_x_tb_GrpUsr gu ON p.idGrpUsr=gu.idGrpUsr 
		LEFT JOIN tb_Users u ON gu.idUser=u.idUser
		WHERE f.idFile=in_idFile
		AND (
			p.idGrpUsr=1 OR 
			(gu.idUser=in_idUser AND u.Ativo) OR 
			(p.idGrpUsr=3 AND in_idUser!=0 AND (SELECT Ativo FROM tb_Users WHERE idUser=in_idUser)) 
		) 
		GROUP BY f.idFile
	);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `fn_User_BuildToken` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` FUNCTION `fn_User_BuildToken`(in_idUser INT(11) UNSIGNED) RETURNS char(32) CHARSET latin1
    DETERMINISTIC
BEGIN
	DECLARE o_Token CHAR(32) DEFAULT MD5(CONCAT(in_idUser,'@',CONNECTION_ID(),'/', USER(),'?',NOW(),'.',RAND()));
	REPLACE tb_Users_Token (idUser,Token) VALUES (in_idUser,o_Token);

	RETURN o_Token;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `fn_User_ChangePasswd` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` FUNCTION `fn_User_ChangePasswd`(in_user VARCHAR(64), in_oldPasswd VARCHAR(64), in_newPasswd VARCHAR(64)) RETURNS tinyint(1) unsigned
    NO SQL
    COMMENT 'Muda senha de um usuário'
BEGIN
	

	UPDATE tb_Users 
	SET passwd=fn_User_Key(in_newPasswd,1)
	WHERE `user`=in_user AND in_oldPasswd=fn_User_Key(passwd,0);

	RETURN fn_User_Check(in_user,in_newPasswd);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `fn_User_Check` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` FUNCTION `fn_User_Check`(in_idUser int(11) UNSIGNED, in_passwd VARCHAR(64)) RETURNS tinyint(1) unsigned
    NO SQL
    COMMENT 'Check if password is correct'
BEGIN
	
	
	IF(fn_User_CheckTryLogin(o_idUser)) THEN RETURN NULL; END IF;
	RETURN ifnull((SELECT MAX(idUser) u FROM tb_Users WHERE idUser=in_idUser AND fn_User_Key(passwd,0)=in_passwd),0);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `fn_User_CheckToken` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` FUNCTION `fn_User_CheckToken`(in_idUser int(11) UNSIGNED,in_Token CHAR(32)) RETURNS tinyint(1) unsigned
    NO SQL
BEGIN
	DECLARE o_ExpireSession INT(11) UNSIGNED DEFAULT IFNULL(@expiresSession,15); 

	IF(
		IFNULL(in_Token,'')!='' AND
		(SELECT DtUpdate FROM tb_Users_Token WHERE idUser=in_idUser AND Token=in_Token)>DATE_SUB(NOW(),INTERVAL o_ExpireSession MINUTE)
	)THEN
		UPDATE tb_Users_Token SET DtUpdate=NOW() WHERE idUser=in_idUser;
		RETURN TRUE;
	END IF;
	RETURN FALSE;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `fn_User_CheckTryLogin` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` FUNCTION `fn_User_CheckTryLogin`(in_idUser int(11) UNSIGNED) RETURNS tinyint(1) unsigned
    NO SQL
    COMMENT 'Retorna se pode tentar login novamente'
BEGIN
	

	DECLARE o_tryWait INT(11) UNSIGNED DEFAULT IFNULL(@tryWait,10); 
	DECLARE o_DtLastTry TINYINT(1) UNSIGNED DEFAULT IFNULL((SELECT DtUpdate FROM tb_Users_TryLogin WHERE idUser=in_idUser),'1970-01-01 00:00:00');
	DECLARE o_out TINYINT(1) UNSIGNED DEFAULT o_DtLastTry>DATE_SUB(NOW(),INTERVAL o_tryWait SECOND);

	REPLACE tb_Users_TryLogin (idUser) VALUES (in_idUser);
	RETURN o_out;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `fn_User_GetRandPasswd` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` FUNCTION `fn_User_GetRandPasswd`(in_tam TINYINT UNSIGNED) RETURNS varchar(64) CHARSET latin1
    NO SQL
BEGIN
	DECLARE universoChar VARCHAR(255) DEFAULT '0123456789ABCDEFGHIJKLMNOPQRSTUVXWYZabcdefghijklmnopqrstuvxwyz_-+=!@#$%&*()[]{}';
    DECLARE passwd VARCHAR(64) DEFAULT '';
    DECLARE i INT DEFAULT 0;
    DECLARE m INT;
	
	SET in_tam=GREATEST(IFNULL(in_tam,10),3);
    SET m=LENGTH(universoChar);
    WHILE i<in_tam DO
    	SET i=i+1;
        SET passwd=CONCAT(passwd,MID(universoChar,FLOOR(RAND()*m)+1,1));
    END WHILE;    
	RETURN passwd;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `fn_User_GetToken` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` FUNCTION `fn_User_GetToken`(in_idUser INT(11) UNSIGNED) RETURNS char(32) CHARSET latin1
    DETERMINISTIC
BEGIN
	RETURN (SELECT Token FROM tb_Users_Token WHERE idUser=in_idUser);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `fn_User_Key` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` FUNCTION `fn_User_Key`(in_passwd VARCHAR(255),in_type TINYINT(1) UNSIGNED) RETURNS varchar(255) CHARSET latin1
    NO SQL
BEGIN
	DECLARE o_key VARCHAR(255) DEFAULT '<put here your passphare or any caracteres>';
	IF(in_passwd IS NULL)THEN
		RETURN o_key;
	END IF;
	IF(in_type=1)THEN
		RETURN ENCODE(in_passwd,o_key);
	END IF;
	RETURN DECODE(in_passwd,o_key);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `fail` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` PROCEDURE `fail`(IN in_erro VARCHAR(255))
    COMMENT 'Executa uma exceção gerando erro'
BEGIN
	
	CREATE TEMPORARY TABLE IF NOT EXISTS fail(
		`ERRO` text NOT NULL,
		PRIMARY KEY USING HASH (`ERRO`(500))
	);
	INSERT fail VALUES (in_erro),(in_erro);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `pc_MyDoc` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` PROCEDURE `pc_MyDoc`(IN in_ObjType ENUM('db','tb','vw','fn','sp','ev','tr','f'), IN in_Obj VARCHAR(255))
    COMMENT 'Captura a documentação de um objeto qualquer no banco'
BEGIN
	
	DECLARE firstExec TINYINT(1) DEFAULT 0;
	DECLARE a VARCHAR(64) DEFAULT NULL;
	DECLARE b VARCHAR(64) DEFAULT NULL;
	DECLARE c VARCHAR(64) DEFAULT NULL;
	DECLARE p INTEGER(11) UNSIGNED;
	DECLARE oSchema VARCHAR(64) DEFAULT DATABASE();
	DECLARE oObj VARCHAR(64) DEFAULT NULL;
	DECLARE oField VARCHAR(64) DEFAULT NULL;
	DECLARE ObjCreate LONGTEXT DEFAULT NULL;
	DECLARE descr LONGTEXT DEFAULT NULL;
	DECLARE oId INTEGER(11) UNSIGNED DEFAULT NULL;

	CALL db_rIP.pc_SplitObjName(a,in_Obj);
	CALL db_rIP.pc_SplitObjName(b,in_Obj);
	CALL db_rIP.pc_SplitObjName(c,in_Obj);

	IF(a IS NULL)THEN 
		IF(in_ObjType!='db')THEN
			CALL fail('ERRO: Parametro in_Obj incorreto'); 
		END IF;
	ELSE
		IF(b IS NULL)THEN
			SET oObj=a;
		ELSEIF(c IS NULL)THEN
			IF(in_ObjType='f')THEN
				SET oObj=a;
				SET oField=b;
			ELSE
				SET oSchema=a;
				SET oObj=b;
			END IF;
		ELSE
			SET oSchema=a;
			SET oObj=b;
			SET oField=c;
		END IF;
	END IF;

	DROP TABLE IF EXISTS tmp_MyDoc;
	CREATE TEMPORARY TABLE tmp_MyDoc(
		id INTEGER(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		ObjType ENUM('db','tb','vw','fn','sp','ev','tr','f','c') DEFAULT NULL,
		Obj VARCHAR(200) NOT NULL,
		Dad VARCHAR(200) DEFAULT NULL,
		Ret VARCHAR(32) DEFAULT NULL,
		Var VARCHAR(32) DEFAULT NULL,
		`Type` LONGTEXT DEFAULT NULL,
		Description LONGTEXT DEFAULT NULL,
		INDEX ObjType (ObjType),
		INDEX Obj (Obj),
		INDEX Dad (Dad),
		INDEX Ret (Ret),
		INDEX Var (Var)
	);

	IF(in_ObjType='db')THEN 
		INSERT IGNORE tmp_MyDoc (ObjType,Obj,Var,`Type`)
		SELECT 
			'db' ObjType,
			CONCAT('`',SCHEMA_NAME,'`') Obj,
			SCHEMA_NAME Var,
			CONCAT_WS(' ',
				CONCAT('DEFAULT_CHARACTER_SET_NAME=',DEFAULT_CHARACTER_SET_NAME),
				CONCAT('DEFAULT_COLLATION_NAME=',DEFAULT_COLLATION_NAME),
				CONCAT('SQL_PATH=',SQL_PATH)
			)  `Type`
		FROM information_schema.SCHEMATA WHERE SCHEMA_NAME=oSchema;
	END IF;
	IF(in_ObjType IN ('db','tb','vw'))THEN
		INSERT IGNORE tmp_MyDoc (ObjType,Obj,Dad,Var,`Type`,Description)
		SELECT 
			IF(TABLE_TYPE='VIEW','vw','tb') ObjType,
			CONCAT('`',TABLE_SCHEMA,'`.`',TABLE_NAME,'`') Obj,
			CONCAT('`',TABLE_SCHEMA,'`') Dad,
			TABLE_NAME Var,
			CONCAT_WS(' ',
				CONCAT('ENGINE=',ENGINE),
				CONCAT('VERSION=',VERSION),
				CONCAT('ROW_FORMAT=',ROW_FORMAT),
				CONCAT('TABLE_ROWS=',TABLE_ROWS),
				CONCAT('AVG_ROW_LENGTH=',AVG_ROW_LENGTH),
				CONCAT('DATA_LENGTH=',DATA_LENGTH),
				CONCAT('MAX_DATA_LENGTH=',MAX_DATA_LENGTH),
				CONCAT('INDEX_LENGTH=',INDEX_LENGTH),
				CONCAT('AUTO_INCREMENT=',AUTO_INCREMENT),
				CONCAT('CREATE_TIME=',CREATE_TIME),
				CONCAT('UPDATE_TIME=',UPDATE_TIME),
				CONCAT('CHECK_TIME=',CHECK_TIME),
				CONCAT('TABLE_COLLATION=',TABLE_COLLATION),
				CONCAT('CHECKSUM=',`CHECKSUM`),
				CONCAT('CREATE_OPTIONS=',CREATE_OPTIONS)
			)  `Type`,
			TABLE_COMMENT Description
		FROM information_schema.TABLES WHERE TABLE_SCHEMA=oSchema AND (in_ObjType='db' OR (TABLE_NAME=oObj AND TABLE_TYPE=IF(in_ObjType='tb','BASE TABLE','VIEW')));
	END IF;
	IF(in_ObjType IN ('db','tb','vw','f'))THEN
		IF(oField IS NULL AND in_ObjType='f')THEN DROP TABLE tmp_MyDoc; CALL fail('ERRO: Parametro in_Obj incorreto. Field inexistente'); END IF;
		INSERT IGNORE tmp_MyDoc (ObjType,Obj,Dad,Var,`Type`,Description)
		SELECT
			'f',
			CONCAT('`',TABLE_SCHEMA,'`.`',TABLE_NAME,'`.`',COLUMN_NAME,'`') Obj,
			CONCAT('`',TABLE_SCHEMA,'`.`',TABLE_NAME,'`') Dad,
			COLUMN_NAME Var,
			CONCAT_WS(' ',
				COLUMN_TYPE,
				IF(IS_NULLABLE='NO','NOT NULL',NULL),
				CONCAT('DEFAULT "',COLUMN_DEFAULT,'"'),
				IF(EXTRA='',NULL,EXTRA)
			) `Type`,
			COLUMN_COMMENT Description
		FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=oSchema
		AND (in_ObjType='db' OR (TABLE_NAME=oObj AND (in_ObjType IN ('tb','vw') OR COLUMN_NAME=oField)));
	END IF;
	IF(in_ObjType IN ('db','fn','sp'))THEN
		INSERT IGNORE tmp_MyDoc (ObjType,Obj,Dad,Var,`Type`,Description)
		SELECT 
			IF(ROUTINE_TYPE='FUNCTION','fn','sp') ObjType,
			CONCAT('`',ROUTINE_SCHEMA,'`.`',ROUTINE_NAME,'`') Obj,
			CONCAT('`',ROUTINE_SCHEMA,'`') Dad,
			ROUTINE_NAME Var,
			CONCAT_WS(' ',
				CONCAT('DEFINER=',DEFINER),
				CONCAT('SECURITY_TYPE=',SECURITY_TYPE),
				IF(IS_DETERMINISTIC='NO','NOT',NULL),'DETERMINISTIC',
				IF(SQL_MODE='',NULL,SQL_MODE),
				CONCAT('EXTERNAL_NAME=',EXTERNAL_NAME),
				CONCAT('EXTERNAL_LANGUAGE=',EXTERNAL_LANGUAGE),
				CONCAT('SQL_DATA_ACCESS ',SQL_DATA_ACCESS),
				CONCAT('SQL_PATH ',SQL_PATH)
			)  `Type`,
			ROUTINE_COMMENT Description
		FROM information_schema.ROUTINES 
		WHERE ROUTINE_SCHEMA=oSchema 
		AND (in_ObjType='db' OR (ROUTINE_NAME=oObj AND ROUTINE_TYPE=IF(in_ObjType='fn','FUNCTION','PROCEDURE')));
		
		IF(ROW_COUNT()>0)THEN
			INSERT IGNORE tmp_MyDoc (ObjType,Obj,Dad,Ret,Var,`Type`)
			SELECT 
				'f' ObjType,
				CONCAT('`',SPECIFIC_SCHEMA,'`.`',SPECIFIC_NAME,'`.`parameter`.`',PARAMETER_NAME,'`') Obj,
				CONCAT('`',SPECIFIC_SCHEMA,'`.`',SPECIFIC_NAME,'`') Dad,
				'parameter' Ret,
				PARAMETER_NAME Var,
				CONCAT(DTD_IDENTIFIER,' ',PARAMETER_MODE) `Type`
			FROM information_schema.PARAMETERS 
			WHERE PARAMETER_NAME IS NOT NULL AND SPECIFIC_SCHEMA=oSchema 
			AND (in_ObjType='db' OR (SPECIFIC_NAME=oObj AND ROUTINE_TYPE=IF(in_ObjType='fn','FUNCTION','PROCEDURE')));

			SELECT COUNT(fn_MyDoc_ParserComment(IF(ROUTINE_TYPE='FUNCTION','fn','sp'),CONCAT('`',ROUTINE_SCHEMA,'`.`',ROUTINE_NAME,'`'),ROUTINE_DEFINITION)) INTO p
			FROM information_schema.ROUTINES 
			WHERE ROUTINE_SCHEMA=oSchema 
			AND (in_ObjType='db' OR (ROUTINE_NAME=oObj AND ROUTINE_TYPE=IF(in_ObjType='fn','FUNCTION','PROCEDURE')));
		END IF;
	END IF;
	IF(in_ObjType IN ('db','tb','tr'))THEN
		INSERT IGNORE tmp_MyDoc (ObjType,Obj,Dad,Ret,Var,`Type`)
		SELECT 
			'tr' ObjType,
			CONCAT('`',EVENT_OBJECT_SCHEMA,'`.`',EVENT_OBJECT_TABLE,'`.`',TRIGGER_NAME,'`') Obj,
			CONCAT('`',EVENT_OBJECT_SCHEMA,'`.`',EVENT_OBJECT_TABLE,'`') Dad,
			CONCAT_WS(' ',ACTION_TIMING,EVENT_MANIPULATION) Ret,
			TRIGGER_NAME Var,
			CONCAT_WS(' ',
				CONCAT('ACTION_ORDER=',ACTION_ORDER),
				CONCAT('ACTION_CONDITION=',ACTION_CONDITION),
				CONCAT('ACTION_ORIENTATION=',ACTION_ORIENTATION),
				CONCAT('ACTION_REFERENCE_OLD_TABLE=',ACTION_REFERENCE_OLD_TABLE),
				CONCAT('ACTION_REFERENCE_NEW_TABLE=',ACTION_REFERENCE_NEW_TABLE),
				CONCAT('ACTION_REFERENCE_OLD_ROW=',ACTION_REFERENCE_OLD_ROW),
				CONCAT('ACTION_REFERENCE_NEW_ROW=',ACTION_REFERENCE_NEW_ROW),
				CONCAT('CREATED=',CREATED),
				CONCAT('SQL_MODE=',SQL_MODE),
				CONCAT('DEFINER=',DEFINER),
				CONCAT('CHARACTER_SET_CLIENT=',CHARACTER_SET_CLIENT),
				CONCAT('COLLATION_CONNECTION=',COLLATION_CONNECTION),
				CONCAT('DATABASE_COLLATION=',DATABASE_COLLATION)
			)  `Type`
		FROM information_schema.TRIGGERS 
		WHERE EVENT_OBJECT_SCHEMA=oSchema 
		AND (in_ObjType='db' 
			OR (in_ObjType='tb' AND EVENT_OBJECT_TABLE=oObj) 
			OR (in_ObjType='tr' AND TRIGGER_NAME=oObj)
		);
		
		IF(ROW_COUNT()>0)THEN
			SELECT COUNT(fn_MyDoc_ParserComment('tr',CONCAT('`',EVENT_OBJECT_SCHEMA,'`.`',EVENT_OBJECT_TABLE,'`.`',TRIGGER_NAME,'`'),ACTION_STATEMENT)) INTO p
			FROM information_schema.TRIGGERS 
			WHERE EVENT_OBJECT_SCHEMA=oSchema 
			AND (in_ObjType='db' 
				OR (in_ObjType='tb' AND EVENT_OBJECT_TABLE=oObj) 
				OR (in_ObjType='tr' AND TRIGGER_NAME=oObj)
			);
		END IF;
	END IF;
	IF(in_ObjType IN ('db','ev'))THEN
		INSERT IGNORE tmp_MyDoc (ObjType,Obj,Dad,Var,`Type`,Description)
		SELECT 
			'ev' ObjType,
			CONCAT('`',EVENT_SCHEMA,'`.`',EVENT_NAME,'`') Obj,
			CONCAT('`',EVENT_SCHEMA,'`') Dad,
			EVENT_NAME Var,
			CONCAT_WS(' ',
				CONCAT('DEFINER=',DEFINER),
				CONCAT('TIME_ZONE=',TIME_ZONE),
				CONCAT('EVENT_BODY=',EVENT_BODY),
				CONCAT('EVENT_TYPE=',EVENT_TYPE),
				CONCAT('EXECUTE_AT=',EXECUTE_AT),
				CONCAT('INTERVAL_VALUE=',INTERVAL_VALUE),
				CONCAT('INTERVAL_FIELD=',INTERVAL_FIELD),
				CONCAT('STARTS=',STARTS),
				CONCAT('ENDS=',ENDS),
				CONCAT('STATUS=',`STATUS`),
				CONCAT('ON_COMPLETION=',ON_COMPLETION),
				CONCAT('SQL_MODE=',SQL_MODE),
				CONCAT('CREATED=',CREATED),
				CONCAT('LAST_ALTERED=',LAST_ALTERED),
				CONCAT('LAST_EXECUTED=',LAST_EXECUTED),
				CONCAT('ORIGINATOR=',ORIGINATOR),
				CONCAT('CHARACTER_SET_CLIENT=',CHARACTER_SET_CLIENT),
				CONCAT('COLLATION_CONNECTION=',COLLATION_CONNECTION),
				CONCAT('DATABASE_COLLATION=',DATABASE_COLLATION)
			)  `Type`,
			EVENT_COMMENT Description
		FROM information_schema.EVENTS WHERE EVENT_SCHEMA=oSchema AND (in_ObjType='db' OR EVENT_NAME=oObj);
		IF(ROW_COUNT()>0)THEN
			SELECT COUNT(fn_MyDoc_ParserComment('ev',CONCAT('`',EVENT_SCHEMA,'`.`',EVENT_NAME,'`'),EVENT_DEFINITION)) INTO p
			FROM information_schema.EVENTS WHERE EVENT_SCHEMA=oSchema AND (in_ObjType='db' OR EVENT_NAME=oObj);
		END IF;
	END IF;

	SELECT id,ObjType,Obj,Dad,Ret,Var,`Type`,Description 
	FROM tmp_MyDoc 
	ORDER BY Obj,id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `pc_Permition_Files_by_idUser` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` PROCEDURE `pc_Permition_Files_by_idUser`(IN in_idUser INT(11) UNSIGNED)
    COMMENT 'Mostra todos os arquivos com permissões para um usuário'
BEGIN
	
	SELECT f.idFile, f.File, f.L, f.CRUDS fCRUDS, fn_Permition_CRUDS(f.L,f.CRUDS,BIT_OR(p.CRUDS)) aCRUDS
	FROM tb_Files f
	JOIN tb_Files_x_tb_GrpFile gf ON f.idFile=gf.idFile
	JOIN tb_Permitions p ON gf.idGrpFile=p.idGrpFile
	LEFT JOIN tb_Users_x_tb_GrpUsr gu ON p.idGrpUsr=gu.idGrpUsr 
	LEFT JOIN tb_Users u ON gu.idUser=u.idUser
	WHERE p.idGrpUsr=1 OR 
		(gu.idUser=in_idUser AND u.Ativo) OR 
		(p.idGrpUsr=3 AND in_idUser!=0 AND (SELECT Ativo FROM tb_Users WHERE idUser=in_idUser)) 
	GROUP BY f.idFile;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `pc_Permition_GrpUsr_by_idFile_CRUDS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` PROCEDURE `pc_Permition_GrpUsr_by_idFile_CRUDS`(IN in_idFile int(11) UNSIGNED, IN in_CRUDS tinyint(2) UNSIGNED)
    COMMENT 'Mostra permissões de um arquivo para seus respectivos Grupos de Users'
BEGIN
	
	SET in_CRUDS = IFNULL(in_CRUDS, 31);
	SELECT
		f.idFile,
		f.File,
		f.Nivel,
		f.CRUDS fCRUDS,
		p.CRUDS pCRUDS,
		fn_Permition_CRUDS(f.Nivel, f.CRUDS, p.CRUDS) aCRUDS,
		gu.idGrpUsr,
		gu.GrpUsr
	FROM tb_Files f
		JOIN tb_Files_x_tb_GrpFile gf
			ON f.idFile = gf.idFile
		JOIN tb_Permitions p
			ON gf.idGrpFile = p.idGrpFile AND p.CRUDS & in_CRUDS
		JOIN tb_GrpUsr gu
			ON p.idGrpUsr = gu.idGrpUsr
	WHERE f.idFile = in_idFile;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `pc_SplitObjName` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` PROCEDURE `pc_SplitObjName`(OUT out_ObjName VARCHAR(255), INOUT oi_ObjFullName VARCHAR(255))
    COMMENT 'Divide um nome de objeto em duas partes primeira[.restante]'
BEGIN
	
	DECLARE p TINYINT(4) DEFAULT 0;

	IF(oi_ObjFullName IS NULL)THEN
		SET out_ObjName=NULL;
	ELSE
		IF(LEFT(oi_ObjFullName,1)='`')THEN 
			SET oi_ObjFullName=SUBSTRING(oi_ObjFullName,2);
			SET p=INSTR(oi_ObjFullName,'`');
			IF(p=0)THEN CALL fail('ERRO: Parametro in_Obj incorreto'); END IF;
			SET out_ObjName=LEFT(oi_ObjFullName,p-1);
			SET oi_ObjFullName=SUBSTRING(oi_ObjFullName,p+1);
		ELSE
			SET p=INSTR(oi_ObjFullName,'.');
			IF(p=0)THEN 
				SET out_ObjName=oi_ObjFullName;
				SET oi_ObjFullName=NULL;
			ELSE
				SET out_ObjName=LEFT(oi_ObjFullName,p-1);
				SET oi_ObjFullName=SUBSTRING(oi_ObjFullName,p);
			END IF;
		END IF;
		IF(LEFT(oi_ObjFullName,1)='.')THEN
			SET oi_ObjFullName=SUBSTRING(oi_ObjFullName,2);
		END IF;
	END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `pc_User_Create` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` PROCEDURE `pc_User_Create`(IN in_Domain VARCHAR(50), IN in_User VARCHAR(64), IN in_Passwd VARCHAR(64))
    COMMENT 'Cria um usuário'
BEGIN
	
	DECLARE o_idDomain tinyint(4) UNSIGNED DEFAULT IFNULL((SELECT idDomain FROM tb_Domain WHERE Domain=IFNULL(in_Domain,'')),1);
	DECLARE o_idUser INT(11) UNSIGNED DEFAULT 0;

	IF(IFNULL(in_Passwd,'')='')THEN CALL fail('Senha vazia'); END IF;
	INSERT tb_Users (idDomain,in_User) VALUES(o_idDomain,in_User);
	SET o_idUser=LAST_INSERT_ID();
	IF(o_idUser)THEN
		UPDATE tb_Users_Passwd SET Passwd=in_Passwd WHERE idUser=o_idUser;
	END IF;
	SELECT o_idUser idUser;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `pc_User_LogOn` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` PROCEDURE `pc_User_LogOn`(IN in_Domain VARCHAR(50), IN `in_User` varchar(64), IN `in_Password` varchar(64))
    COMMENT 'Verifica usuário e senha'
BEGIN
	
	DECLARE o_idUser INT(11) UNSIGNED DEFAULT NULL;
	DECLARE o_Token CHAR(32) DEFAULT NULL;
	DECLARE o_Message TEXT DEFAULT NULL;
	DECLARE o_Check INT(11) UNSIGNED DEFAULT NULL;

	IF(IFNULL(in_Password,'')='')THEN
		CALL fail('Senha vazia');
	ELSE
		SET in_Domain=IFNULL(in_Domain,'');
		SET o_idUser=(SELECT idUser FROM tb_Users u JOIN tb_Domain d ON u.idDomain=d.idDomain AND d.Domain=in_Domain WHERE u.User=in_User);
		IF(IFNULL(o_idUser,0)=0)THEN
			SET o_Message='Usuário não existe';
		ELSE
			SET o_Check=fn_User_Check(o_idUser,in_Password);
			IF(o_Check IS NULL) THEN
				SET o_Message=CONCAT('Tentativa rescente de login (limite de ',IFNULL(@tryWait,10),' segundos)');
			ELSEIF(o_Check=0) THEN
				SET o_Message='Senha inválida';
			ELSEIF(IFNULL((SELECT DtExpires FROM tb_Users_Passwd WHERE idUser=o_idUser),NOW())<NOW()) THEN
				SET o_Message='Senha expirada mude a senha';
			ELSE
				SET o_Token=fn_User_BuildToken(o_idUser);
			END IF;
		END IF;
	END IF;

	SELECT o_idUser idUser, o_Token Token, o_Message Message;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Current Database: `db_Secure_Logs`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `db_Secure_Logs` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `db_Secure_Logs`;

--
-- Table structure for table `tb_Logs`
--

DROP TABLE IF EXISTS `tb_Logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tb_Logs` (
  `idLog` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Data` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `idUser` int(11) unsigned NOT NULL,
  `idFile` int(11) unsigned NOT NULL,
  `nAuth` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT 'n bits: o menos significativo e se foi ou nao autenticado. os demais e um count da auth feita ',
  `remoteAddr` varchar(39) NOT NULL COMMENT '[Remote]Endereco do Computador Remoto',
  `remotePort` smallint(6) unsigned NOT NULL COMMENT '[Port]Porta do computador remoto',
  `serverAddr` varchar(39) NOT NULL COMMENT '[Server]Endereco do Server',
  `serverPort` smallint(6) unsigned NOT NULL COMMENT '[Port]Porta do Server',
  `request` blob COMMENT 'serialize (array(''post''=>'''',''get''=>'''',etc))',
  `referer` tinyblob,
  `requestMethod` enum('GET','POST') DEFAULT NULL,
  `HTTPs` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`idLog`),
  KEY `Data` (`Data`),
  KEY `idFiles` (`idFile`),
  KEY `idUsers` (`idUser`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_Logs`
--

LOCK TABLES `tb_Logs` WRITE;
/*!40000 ALTER TABLE `tb_Logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_Logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'db_Secure_Logs'
--

--
-- Dumping routines for database 'db_Secure_Logs'
--

--
-- Current Database: `db_System`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `db_System` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `db_System`;

--
-- Dumping events for database 'db_System'
--

--
-- Dumping routines for database 'db_System'
--
/*!50003 DROP FUNCTION IF EXISTS `fn_Cnpj_Auth` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` FUNCTION `fn_Cnpj_Auth`(in_cnpj VARCHAR(18)) RETURNS tinyint(1) unsigned
    DETERMINISTIC
    COMMENT 'Autentica CNPJ'
BEGIN
	RETURN IF(fn_Cpf_getDigControl(in_cnpj)<=>RIGHT(in_cnpj,2),1,0);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `fn_Cnpj_Cpf_Auth` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` FUNCTION `fn_Cnpj_Cpf_Auth`(in_num VARCHAR(18)) RETURNS tinyint(1) unsigned
    DETERMINISTIC
    COMMENT 'Autentica CNPJ ou CPF'
BEGIN
    RETURN IF(LENGTH(fn_Cnpj_Cpf_Strip(in_num))=14,fn_Cnpj_Auth(in_num),fn_Cpf_Auth(in_num)); 
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `fn_Cnpj_Cpf_Format` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` FUNCTION `fn_Cnpj_Cpf_Format`(in_num VARCHAR(18)) RETURNS varchar(18) CHARSET latin1
    DETERMINISTIC
    COMMENT 'Auto formata CNPJ ou CPF'
BEGIN
	DECLARE o_tam INT(11) UNSIGNED; 
	SET in_num=fn_Cnpj_Cpf_Strip(in_num);
    SET o_tam=LENGTH(in_num);
    IF (o_tam=14) THEN
    	RETURN CONCAT(LEFT(in_num,2),'.',MID(in_num,3,3),'.',MID(in_num,6,3),'/',MID(in_num,9,4),'-',RIGHT(in_num,2)); 
    ELSEIF (o_tam=11) THEN
    	RETURN CONCAT(LEFT(in_num,3),'.',MID(in_num,4,3),'.',MID(in_num,7,3),'-',RIGHT(in_num,2)); 
    END IF;
    RETURN NULL;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `fn_Cnpj_Cpf_Strip` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` FUNCTION `fn_Cnpj_Cpf_Strip`(in_Cnpj VARCHAR(18)) RETURNS char(14) CHARSET latin1
    DETERMINISTIC
    COMMENT 'Retira todos os caracteres não números com regra'
BEGIN
	IF (in_Cnpj REGEXP '^i[0-9][0-9]') THEN SET in_Cnpj=SUBSTR(in_Cnpj,5);
	ELSEIF(in_Cnpj IS NULL OR in_Cnpj REGEXP '^[ilIL]') THEN RETURN NULL;
	END IF;
	RETURN fn_StripNomNum(in_Cnpj);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `fn_Cnpj_getDigControl` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` FUNCTION `fn_Cnpj_getDigControl`(in_cnpj VARCHAR(18)) RETURNS char(2) CHARSET latin1
    DETERMINISTIC
    COMMENT 'Gera o dígito verificador'
BEGIN
	DECLARE oCNPJ  VARCHAR(18)      DEFAULT LPAD(fn_Cnpj_Cpf_Strip(in_cnpj),14,0);
	DECLARE oN1    INT(11) UNSIGNED DEFAULT 0;
	DECLARE oN2    INT(11) UNSIGNED DEFAULT 0;
	DECLARE oSeqN1 INT(11) UNSIGNED DEFAULT 5;
	DECLARE oSeqN2 INT(11) UNSIGNED DEFAULT 6;
	DECLARE oI     INT(11) UNSIGNED DEFAULT 0;
	DECLARE oS     INT(11) UNSIGNED DEFAULT 0;
	
	IF (oCNPJ IS NULL OR oCNPJ RLIKE "[^-0-9\\./]") THEN RETURN fn_Warning("Caracters Invalidos"); END IF;
	

	WHILE (oI<12) DO
		SET oS=MID(oCNPJ,oI+1,1)+0;
		SET oN1=oN1+(oS * oSeqN1);
		SET oN2=oN2+(oS * oSeqN2);
		SET oSeqN1=oSeqN1-1;
		SET oSeqN2=oSeqN2-1;
		IF (oI=3) THEN SET oSeqN1=9;
		ELSEIF (oI=4) THEN SET oSeqN2=9;
		END IF;
		SET oI=oI+1;
	END WHILE;

	SET oS=oN1 % 11;
	SET oN1=IF(oS=0 OR oS=1,0,11-oS);
	SET oN2=oN2 + (2 * oN1);
	SET oS=oN2 % 11;
	SET oN2=IF(oS=0 OR oS=1,0,11-oS);
	RETURN CONCAT(oN1,oN2);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `fn_Cpf_Auth` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` FUNCTION `fn_Cpf_Auth`(in_cpf VARCHAR(18)) RETURNS tinyint(1) unsigned
    DETERMINISTIC
    COMMENT 'Autentica CPF'
BEGIN
	RETURN IF(fn_Cpf_getDigControl(in_cpf)<=>RIGHT(in_cpf,2),1,0);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `fn_Cpf_getDigControl` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` FUNCTION `fn_Cpf_getDigControl`(in_cpf VARCHAR(18)) RETURNS char(2) CHARSET latin1
    DETERMINISTIC
    COMMENT 'Gera o dígito verificador'
BEGIN
	DECLARE oCPF VARCHAR(18) DEFAULT fn_Cnpj_Cpf_Strip(in_cpf);
	DECLARE oDig CHAR(2) DEFAULT '';
	DECLARE oVez TINYINT(1) UNSIGNED DEFAULT 0;
	DECLARE oInd TINYINT(1) UNSIGNED;
	DECLARE oSoma   INT(11);
	DECLARE oRes    INT(11);

	WHILE (oVez<2) DO
		SET oSoma=0, oInd=0;
		WHILE (oInd<9) DO
			SET oSoma=oSoma+(10+oVez-oInd)*MID(oCPF,oInd+1,1);
			SET oInd=oInd+1;
		END WHILE;
		IF(oVez)THEN SET oSoma=oSoma+(LEFT(oDig,1)*2); END IF;
		SET oRes=oSoma-(floor(oSoma/11)*11);
		SET oDig=CONCAT(oDig,IF(oRes<=1,0,11-oRes));

		SET oVez=oVez+1;
	END WHILE;

	RETURN oDig;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `fn_DigControl` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` FUNCTION `fn_DigControl`(in_Num VARCHAR(255)) RETURNS tinyint(1) unsigned
    DETERMINISTIC
BEGIN
	DECLARE oChar CHAR(1);
	DECLARE oOut INT(11) UNSIGNED DEFAULT 0;
	SET in_Num=CONCAT(in_Num,MD5(in_Num));

	WHILE (LENGTH(in_Num)>1) DO
		WHILE (in_Num!='') DO
			SET oChar=in_Num;
			SET in_Num=SUBSTR(in_Num,2);
			SET oOut=oOut+IF(oChar>0 AND oChar<9,oChar,IF(oChar IN ('a','b','c','d','e','f'),ASCII(UPPER(oChar)-55),ASCII(oChar)));
		END WHILE;
		SET in_Num=oOut;
		SET oOut=0;
	END WHILE;
	RETURN in_Num;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `fn_make_uid` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` FUNCTION `fn_make_uid`() RETURNS char(36) CHARSET latin1
    DETERMINISTIC
BEGIN
	RETURN UUID();
	
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `fn_StripNomNum` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` FUNCTION `fn_StripNomNum`(in_Texto VARCHAR(255)) RETURNS varchar(255) CHARSET latin1
    DETERMINISTIC
    COMMENT 'Retira todos os caracteres não números'
BEGIN
	RETURN preg_replace('/\D/','',in_Texto);
	
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `fn_Warning` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` FUNCTION `fn_Warning`(in_Error VARCHAR(255)) RETURNS char(1) CHARSET latin1
    DETERMINISTIC
    COMMENT 'Gera erro Warnnig'
BEGIN
	SET @WARNING=in_Error;
	RETURN NULL;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `fail` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`admin`@`%` PROCEDURE `fail`(IN in_erro text)
    COMMENT 'Executa uma exceção gerando erro'
BEGIN
  CREATE TEMPORARY TABLE IF NOT EXISTS fail(`ERRO` text NOT NULL, PRIMARY KEY USING HASH (`ERRO`(255)));
  INSERT fail VALUES (in_erro),(in_erro);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Current Database: `db_Secure`
--

USE `db_Secure`;

--
-- Final view structure for view `vw_digits`
--

/*!50001 DROP TABLE IF EXISTS `vw_digits`*/;
/*!50001 DROP VIEW IF EXISTS `vw_digits`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`admin`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_digits` AS select 0 AS `d` union select 1 AS `d` union select 2 AS `d` union select 3 AS `d` union select 4 AS `d` union select 5 AS `d` union select 6 AS `d` union select 7 AS `d` union select 8 AS `d` union select 9 AS `d` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Current Database: `db_Secure_Logs`
--

USE `db_Secure_Logs`;

--
-- Current Database: `db_System`
--

USE `db_System`;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-11-05 11:23:13
