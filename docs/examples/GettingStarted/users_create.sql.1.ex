CREATE TABLE `users` (
    `id` bigint(20) unsigned NOT NULL auto_increment,
    `first_name` varchar(32) DEFAULT NULL,
    `last_name` varchar(32) DEFAULT NULL,
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;