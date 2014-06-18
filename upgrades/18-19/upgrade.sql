RENAME TABLE `image_files` TO `image_files_c` ;
CREATE TABLE `image_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mime_type` varchar(30) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `size_x` smallint(6) NOT NULL,
  `size_y` smallint(6) NOT NULL,
  `file_size` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `accessed` datetime NOT NULL,
  PRIMARY KEY (`id`)
);
CREATE TABLE `image_file_transforms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dest_id` int(11) NOT NULL,
  `orig_id` int(11) NOT NULL,
  `transform` tinyint(4) NOT NULL,
  `size_x` int(11) NOT NULL,
  `size_y` int(11) NOT NULL,
  PRIMARY KEY (`id`)
);
DROP TABLE `media` ;
UPDATE `version` SET `db_version` = '19' WHERE `version`.`db_version` =18 LIMIT 1 ;
