CREATE TABLE `sessions` (
  `session_id` varchar(64) NOT NULL,
  `session_data` text,
  `permified` tinyint(1) unsigned default 0,
  `dt_created` datetime default null,
  `ts_update` timestamp,
  PRIMARY KEY  (`session_id`)
) ENGINE=MyISAM;