UPDATE `version` SET `db_version` = '11' WHERE `version`.`db_version` =10 LIMIT 1 ;
ALTER TABLE `entries` DROP INDEX `track` ,
ADD UNIQUE `track` ( `track` ) ;
ALTER TABLE `redirs` DROP INDEX `track` ,
ADD UNIQUE `track` ( `track` ) ;
ANALYZE TABLE `entries` ;
