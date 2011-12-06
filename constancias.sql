
CREATE DATABASE `constancias`;

/*Table structure for table `constancias_generar` */

CREATE TABLE `constancias_generar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) NOT NULL,
  `nombre_participante` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `generada` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


/*Table structure for table `constancias_template` */

CREATE TABLE `constancias_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_evento` varchar(200) DEFAULT NULL,
  `template_file` varchar(100) DEFAULT NULL,
  `coords_x` smallint(6) DEFAULT '0',
  `coords_y` smallint(6) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


