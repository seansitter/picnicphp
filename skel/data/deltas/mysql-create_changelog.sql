CREATE TABLE `changelog` (
  `rev` integer(10) unsigned NOT NULL auto_increment,
  `migration` varchar(256) NOT NULL,
  `dt_started` DATETIME DEFAULT NULL,
  `dt_completed` DATETIME DEFAULT NULL,
  `ts_updated` TIMESTAMP,
  PRIMARY KEY  (`rev`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;