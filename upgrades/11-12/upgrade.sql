CREATE TABLE `html_cache` (
`ident` VARCHAR( 255 ) NOT NULL ,
`content` MEDIUMTEXT NOT NULL ,
`deadline` DATETIME NULL ,
PRIMARY KEY ( `ident` )
) ENGINE = MYISAM ;
ALTER TABLE `html_cache` ADD INDEX ( `deadline` ) ;
UPDATE `version` SET `db_version` = '12' WHERE `version`.`db_version` =11 LIMIT 1 ;
