ALTER TABLE `cross_entries` CHANGE `peer_subject` `peer_subject` VARCHAR( 255 ) CHARACTER SET koi8u COLLATE koi8u_general_ci NOT NULL ;
ALTER TABLE `cross_entries` DROP `peer_subject_sort` ;
ALTER TABLE `cross_entries` ADD INDEX ( `peer_icon` , `peer_subject` ) ;
ALTER TABLE `entries` CHANGE `subject` `subject` VARCHAR( 255 ) CHARACTER SET koi8u COLLATE koi8u_general_ci NOT NULL ;
ALTER TABLE `entries` DROP `subject_sort` ;
ALTER TABLE `entries` ADD INDEX ( `subject` ) ;
ALTER TABLE `users` CHANGE `login` `login` VARCHAR( 30 ) CHARACTER SET koi8u COLLATE koi8u_general_ci NOT NULL ,
CHANGE `name` `name` VARCHAR( 30 ) CHARACTER SET koi8u COLLATE koi8u_general_ci NOT NULL ,
CHANGE `jewish_name` `jewish_name` VARCHAR( 30 ) CHARACTER SET koi8u COLLATE koi8u_general_ci NOT NULL ,
CHANGE `surname` `surname` VARCHAR( 30 ) CHARACTER SET koi8u COLLATE koi8u_general_ci NOT NULL ;
ALTER TABLE `users` DROP `login_sort` ,
DROP `name_sort` ,
DROP `jewish_name_sort` ,
DROP `surname_sort` ;
ALTER TABLE `users` ADD INDEX ( `login` ) ;
ALTER TABLE `users` ADD INDEX ( `name` ) ;
ALTER TABLE `users` ADD INDEX ( `jewish_name` ) ;
ALTER TABLE `users` ADD INDEX ( `surname` ) ;
UPDATE `version` SET `db_version` = '13' WHERE `version`.`db_version` =12 LIMIT 1 ;
