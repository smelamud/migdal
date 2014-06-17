RENAME TABLE `image_files` TO `image_files_c` ;
UPDATE `version` SET `db_version` = '19' WHERE `version`.`db_version` =18 LIMIT 1 ;
