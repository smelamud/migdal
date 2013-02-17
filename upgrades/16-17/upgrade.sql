CREATE TABLE `migdal`.`prisoners` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` VARCHAR( 255 ) NOT NULL ,
`name_russian` VARCHAR( 255 ) NOT NULL ,
`location` VARCHAR( 255 ) NOT NULL ,
`ghetto_name` VARCHAR( 255 ) NOT NULL ,
`sender_name` VARCHAR( 255 ) NOT NULL ,
`sum` INT NOT NULL ,
`search_data` VARCHAR( 255 ) NOT NULL
) ENGINE = MYISAM CHARACTER SET koi8u COLLATE koi8u_general_ci;
ALTER TABLE `prisoners` ADD INDEX ( `name` );
ALTER TABLE `prisoners` ADD INDEX ( `name_russian` );
ALTER TABLE `prisoners` ADD INDEX ( `location` );
ALTER TABLE `prisoners` ADD INDEX ( `ghetto_name` );
ALTER TABLE `prisoners` ADD INDEX ( `sender_name` );
UPDATE `migdal`.`version` SET `db_version` = '17' WHERE `version`.`db_version` =16 LIMIT 1 ;
