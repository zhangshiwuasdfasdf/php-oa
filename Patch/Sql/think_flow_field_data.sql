# Host: localhost  (Version: 5.5.16)
# Date: 2014-05-26 10:25:18
# Generator: MySQL-Front 5.3  (Build 1.27)

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE */;
/*!40101 SET SQL_MODE='' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES */;
/*!40103 SET SQL_NOTES='ON' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS */;
/*!40014 SET FOREIGN_KEY_CHECKS=0 */;

#
# Source for table "think_flow_field_data"
#

DROP TABLE IF EXISTS `think_flow_field_data`;
CREATE TABLE `think_flow_field_data` (
  `flow_id` int(11) NOT NULL,
  `field_id` varchar(50) NOT NULL,
  `val` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

#
# Data for table "think_flow_field_data"
#

/*!40000 ALTER TABLE `think_flow_field_data` DISABLE KEYS */;
INSERT INTO `think_flow_field_data` VALUES (172,'13','c'),(172,'14','2014-05-01'),(171,'12','a'),(171,'11','dddddddddddd'),(170,'23','c'),(170,'11','反反复复反反复复反反复复反反复复反反复复反反复复'),(170,'24','aaaa'),(170,'15','反反复复反反复复反反复复反反复复反反复复吩咐'),(170,'13','c'),(170,'14','2014-05-27'),(171,'23','c'),(171,'24','aaaa'),(171,'15','ddddddddd'),(171,'13','b'),(171,'14','2014-04-29'),(170,'12','a'),(169,'14','2014-05-25'),(169,'13','b'),(169,'15','d\"] => string(2) \"15\"\r\n  [\"name\"] => string(17) \"5'),(169,'24','cccc'),(169,'23','a,b'),(169,'11','333333333333333333333'),(169,'12','c'),(172,'15','dddddddd'),(172,'24','bbbb'),(172,'23','b'),(172,'11','ddddddddd'),(172,'12','a'),(173,'14','2014-05-06'),(173,'13','b'),(173,'15','dddddd'),(173,'24','bbbb'),(173,'23','b'),(173,'11','4444444444444'),(173,'12','a'),(0,'14','2014-05-06'),(0,'13','c'),(0,'15','dddddd'),(0,'24','bbbb'),(0,'23',NULL),(0,'11','4444444444444'),(0,'12','a'),(174,'23','a,b,c'),(174,'24','aaaa'),(174,'15','qqqqqqqqqqqqqqqqq'),(174,'13','b'),(174,'14','2014-05-27'),(174,'11','qqqqqqqqqqqqq'),(174,'12','a'),(175,'12','事假'),(175,'14','2014-04-27'),(175,'13','2014-05-13'),(175,'11','d');
/*!40000 ALTER TABLE `think_flow_field_data` ENABLE KEYS */;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
