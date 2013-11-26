DROP TABLE `horisonts` ,
`journal` ,
`journal_vars` ;
UPDATE `migdal`.`version` SET `db_version` = '18' WHERE `version`.`db_version` =17 LIMIT 1 ;
