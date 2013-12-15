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
CREATE TABLE `media` (
  `id` varchar(40) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `mime_type` varchar(30) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `width` smallint(6) NOT NULL,
  `height` smallint(6) NOT NULL,
  `size` int(11) NOT NULL,
  `accessed` datetime NOT NULL,
  `orig_id` int(11) DEFAULT NULL,
  `trans_op` tinyint(4) DEFAULT NULL,
  `trans_width` smallint(6) DEFAULT NULL,
  `trans_height` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
UPDATE `version` SET `db_version` = '18' WHERE `version`.`db_version` =17 LIMIT 1 ;
