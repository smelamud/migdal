DROP TABLE `horisonts` ,
`journal` ,
`journal_vars` ;
ALTER TABLE `redirs` CHANGE `track` `track` VARCHAR( 255 ) CHARACTER SET koi8u COLLATE koi8u_bin NULL DEFAULT '';
DROP TABLE `complain_actions` ;
CREATE TABLE `complains` (
  `id` int(11) NOT NULL
  ) ;
INSERT INTO `complains`(`id`) SELECT `id` FROM `entries` WHERE `entry` = 5 ;
DELETE FROM `entries` WHERE `entry` =2 AND parent_id IN (
SELECT id
FROM complains
);
DROP TABLE `complains` ;
DELETE FROM `entries` WHERE `entry` =5;
ALTER TABLE `sessions` CHANGE  `last`  `last` DATETIME NOT NULL ;
UPDATE `entries` SET `grp` =2 WHERE `entry` =1 AND `grp` =4096;
UPDATE `version` SET `db_version` = '18' WHERE `version`.`db_version` =17 LIMIT 1 ;
