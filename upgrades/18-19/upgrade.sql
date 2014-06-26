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
ALTER TABLE `entries` ADD `small_image_format` VARCHAR( 30 ) CHARACTER SET ascii COLLATE ascii_bin NOT NULL AFTER `small_image_y` ;
UPDATE entries SET small_image_format =  'image/jpeg' WHERE small_image <>0 AND large_image <>0;
UPDATE entries SET small_image_format = large_image_format,
large_image_x = small_image_x,
large_image_y = small_image_y,
large_image = small_image WHERE small_image <>0 AND large_image =0;
ALTER TABLE `image_file_transforms` ADD UNIQUE (
`orig_id` ,
`transform` ,
`size_x` ,
`size_y`
);
ALTER TABLE  `image_file_transforms` ADD INDEX (  `dest_id` );
INSERT INTO image_files(id,mime_type,size_x,size_y,created,accessed)
SELECT small_image,small_image_format,small_image_x,small_image_y,now(),now()
FROM entries
WHERE small_image<>0;
INSERT INTO image_files(id,mime_type,size_x,size_y,file_size,created,accessed)
SELECT large_image,large_image_format,large_image_x,large_image_y,large_image_size,now(),now()
FROM entries
WHERE small_image<>0 and large_image<>small_image;
INSERT INTO image_file_transforms( dest_id, orig_id, transform, size_x, size_y )
SELECT small_image, large_image, 1, 100, 100
FROM entries
WHERE entry =1
AND small_image <>0
AND large_image <> small_image;
INSERT INTO image_file_transforms( dest_id, orig_id, transform, size_x, size_y )
SELECT small_image, large_image, 1, small_image_x, small_image_y
FROM entries
WHERE entry =1
AND small_image <>0
AND large_image <> small_image;
ALTER TABLE `image_files` AUTO_INCREMENT =25825;
DROP TABLE `image_files_c`;
UPDATE `version` SET `db_version` = '19' WHERE `version`.`db_version` =18 LIMIT 1 ;
