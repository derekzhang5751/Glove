-- MySQL dump 10.13  Distrib 5.7.17, for macos10.12 (x86_64)
--
-- Host: localhost    Database: glove
-- ------------------------------------------------------
-- Server version	5.6.38

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
-- Table structure for table `gv_achat_msg`
--

DROP TABLE IF EXISTS `gv_achat_msg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gv_achat_msg` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `achat_name` varchar(128) NOT NULL,
  `group_name` varchar(128) NOT NULL,
  `achat_id` int(10) unsigned NOT NULL DEFAULT '0',
  `from_userid` int(10) unsigned NOT NULL DEFAULT '0',
  `from_nick` varchar(128) NOT NULL,
  `to_userid` int(10) unsigned NOT NULL DEFAULT '0',
  `to_nick` varchar(128) NOT NULL,
  `msg` varchar(256) NOT NULL DEFAULT '',
  `recv_time` datetime DEFAULT NULL,
  `reply` varchar(256) NOT NULL DEFAULT '',
  `send_time` datetime DEFAULT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gv_achat_msg`
--

LOCK TABLES `gv_achat_msg` WRITE;
/*!40000 ALTER TABLE `gv_achat_msg` DISABLE KEYS */;
INSERT INTO `gv_achat_msg` VALUES (1,'','',1,0,'张三',0,'','message1','2018-03-28 14:58:32','',NULL,3),(2,'','',1,0,'张三',0,'','message1','2018-03-28 14:58:32','',NULL,3),(3,'','',1,0,'张三',0,'','message1','2018-03-28 14:58:32','',NULL,3),(4,'','',2,0,'张三',0,'','message2','2018-03-28 14:58:32','',NULL,3),(5,'','',3,0,'张三',0,'','message3','2018-03-28 14:58:32','',NULL,3),(6,'','',1,0,'加州',0,'','@ท็อป 测试消息','2018-03-30 03:56:04','没看懂！','2018-03-30 05:56:05',3),(7,'','',2,0,'加州',0,'','@ท็อป 测试消息二','2018-03-30 04:28:38','你说啥！','2018-03-30 06:28:38',3),(8,'','',3,0,'加州',0,'','@ท็อป 没说啥','2018-03-30 04:29:12','什么意思！','2018-03-30 06:29:12',3),(9,'Simulator01','飞艇一号群',101,0,'常胜将军',0,'我','学费 2000','2018-04-04 12:13:14','学员注册有点问题，一会再试一下吧!!!','2018-04-05 06:27:31',2),(10,'Simulator01','飞艇一号群',101,0,'常胜将军',0,'我','学费 2000','2018-04-04 12:13:14','稍等，有点忙，一会再发给我吧','2018-04-05 06:33:05',2),(11,'Simulator01','飞艇一号群',101,0,'常胜将军',0,'我','学费 2000','2018-04-04 12:13:14','请使用这个地址交学费: http://glove.loc/Achat/Xufei/do.php?id=180405063933sgq9','2018-04-05 06:39:33',3),(12,'Simulator01','飞艇一号群',101,0,'常胜将军',0,'我','退费 3000','2018-04-04 12:19:18','余额不足 !!!','2018-04-05 06:45:29',2),(13,'Simulator01','飞艇一号群',101,0,'常胜将军12',0,'我','退费 3000','2018-04-04 12:19:18','余额不足 !!!','2018-04-05 06:46:06',2),(14,'Simulator01','飞艇一号群',101,0,'常胜将军12',0,'我','退费 3000','2018-04-04 12:19:18','余额不足 !!!','2018-04-05 06:48:16',2),(15,'Simulator01','飞艇一号群',101,0,'常胜将军1',0,'我','退费 3000','2018-04-04 12:19:18','您还不是会员 !!!','2018-04-05 06:48:40',2),(16,'Simulator01','飞艇一号群',101,0,'常胜将军',0,'我','退费 3000','2018-04-04 12:19:18','余额不足 !!!','2018-04-05 06:49:04',2),(17,'Simulator01','飞艇一号群',101,0,'常胜将军',0,'我','退费 3000','2018-04-04 12:19:18','余额不足 !!!','2018-04-05 17:18:10',2),(18,'Simulator01','飞艇一号群',101,0,'常胜将军',0,'我','退费 3000','2018-04-04 12:19:18','余额不足 !!!','2018-04-05 17:18:26',2);
/*!40000 ALTER TABLE `gv_achat_msg` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gv_admin`
--

DROP TABLE IF EXISTS `gv_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gv_admin` (
  `admin_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(45) NOT NULL,
  `password` varchar(45) NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY (`admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gv_admin`
--

LOCK TABLES `gv_admin` WRITE;
/*!40000 ALTER TABLE `gv_admin` DISABLE KEYS */;
INSERT INTO `gv_admin` VALUES (1,'admin','123qwe',99);
/*!40000 ALTER TABLE `gv_admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gv_money`
--

DROP TABLE IF EXISTS `gv_money`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gv_money` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(64) NOT NULL,
  `amount` decimal(13,2) NOT NULL,
  `balance` decimal(13,2) NOT NULL,
  `req_source` int(11) NOT NULL COMMENT '0 - user request\n1 - order process automatically\n',
  `req_time` datetime NOT NULL,
  `status` int(11) NOT NULL,
  `charge_id` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gv_money`
--

LOCK TABLES `gv_money` WRITE;
/*!40000 ALTER TABLE `gv_money` DISABLE KEYS */;
INSERT INTO `gv_money` VALUES (1,1,'常胜将军',2000.00,0.00,0,'2018-04-05 06:39:33',0,'180405063933sgq9');
/*!40000 ALTER TABLE `gv_money` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gv_sessions`
--

DROP TABLE IF EXISTS `gv_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gv_sessions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `session_key` varchar(45) NOT NULL,
  `expiry` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(32) NOT NULL DEFAULT '',
  `data` varchar(1024) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gv_sessions`
--

LOCK TABLES `gv_sessions` WRITE;
/*!40000 ALTER TABLE `gv_sessions` DISABLE KEYS */;
INSERT INTO `gv_sessions` VALUES (5,'a46a7b41d2f11ed765a6021321489ed0',1523387128,0,1,'127.0.0.1','a:1:{s:8:\"GLOVE_ID\";s:32:\"a46a7b41d2f11ed765a6021321489ed0\";}'),(6,'96dd1dc0cfff1a7062255bc6f4a4af64',1523422441,0,1,'127.0.0.1','a:3:{s:8:\"GLOVE_ID\";s:32:\"96dd1dc0cfff1a7062255bc6f4a4af64\";s:7:\"captcha\";s:4:\"58me\";s:8:\"admin_id\";s:1:\"1\";}');
/*!40000 ALTER TABLE `gv_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gv_user`
--

DROP TABLE IF EXISTS `gv_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gv_user` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(64) NOT NULL,
  `password` varchar(45) NOT NULL DEFAULT '',
  `reg_time` datetime NOT NULL,
  `last_time` datetime NOT NULL,
  `achat_name` varchar(128) NOT NULL DEFAULT '',
  `group_name` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gv_user`
--

LOCK TABLES `gv_user` WRITE;
/*!40000 ALTER TABLE `gv_user` DISABLE KEYS */;
INSERT INTO `gv_user` VALUES (1,'常胜将军','','2018-04-05 06:33:05','2018-04-05 06:33:05','Simulator01','飞艇一号群'),(2,'常胜将军12','','2018-04-05 06:46:06','2018-04-05 06:46:06','Simulator01','飞艇一号群');
/*!40000 ALTER TABLE `gv_user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-04-12 11:42:29
