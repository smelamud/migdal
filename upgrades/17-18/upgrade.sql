DROP TABLE `horisonts` ,
`journal` ,
`journal_vars` ;
ALTER TABLE `redirs` CHANGE `track` `track` VARCHAR( 255 ) CHARACTER SET koi8u COLLATE koi8u_bin NULL DEFAULT '';
UPDATE `migdal`.`version` SET `db_version` = '18' WHERE `version`.`db_version` =17 LIMIT 1 ;
