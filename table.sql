CREATE TABLE `movies` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `year` varchar(11) DEFAULT NULL,
  `director` varchar(60) DEFAULT NULL,
  `lat` varchar(60) DEFAULT NULL,
  `long` varchar(60) DEFAULT NULL,
  `location` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;