CREATE TABLE `old_ids` (
`table_name` CHAR( 32 ) NOT NULL ,
`old_id` INT NOT NULL ,
`old_ident` VARCHAR( 75 ) ,
`entry_id` INT NOT NULL ,
PRIMARY KEY ( `table_name` , `old_id` )
) DEFAULT CHARSET=koi8u COLLATE=koi8u_bin;
ALTER TABLE `old_ids` ADD UNIQUE (
`table_name` ,
`entry_id`
);
ALTER TABLE `old_ids` ADD UNIQUE (
`table_name` ,
`old_ident`
);
CREATE TABLE `entries` (
`id` INT NOT NULL AUTO_INCREMENT ,
`ident` VARCHAR( 75 ) ,
`entry` TINYINT NOT NULL ,
`up` INT NOT NULL ,
`track` VARCHAR( 255 ) NOT NULL ,
`catalog` VARCHAR( 255 ) NOT NULL ,
`parent_id` INT NOT NULL ,
`orig_id` INT NOT NULL ,
`current_id` INT NOT NULL ,
`grp` INT NOT NULL ,
`link` INT NOT NULL ,
`person_id` INT NOT NULL ,
`user_id` INT NOT NULL ,
`group_id` INT NOT NULL ,
`perms` INT NOT NULL ,
`disabled` TINYINT NOT NULL ,
`subject` VARCHAR( 255 ) NOT NULL ,
`subject_sort` VARCHAR( 255 ) NOT NULL ,
`lang` VARCHAR( 7 ) NOT NULL ,
`author` VARCHAR( 255 ) NOT NULL ,
`author_xml` VARCHAR( 255 ) NOT NULL ,
`source` VARCHAR( 255 ) NOT NULL ,
`source_xml` VARCHAR( 255 ) NOT NULL ,
`title` VARCHAR( 255 ) NOT NULL ,
`title_xml` VARCHAR( 255 ) NOT NULL ,
`comment0` VARCHAR( 255 ) NOT NULL ,
`comment0_xml` VARCHAR( 255 ) NOT NULL ,
`comment1` VARCHAR( 255 ) NOT NULL ,
`comment1_xml` VARCHAR( 255 ) NOT NULL ,
`url` VARCHAR( 255 ) NOT NULL ,
`url_domain` VARCHAR( 70 ) NOT NULL ,
`url_check` DATETIME NOT NULL ,
`url_check_success` DATETIME NOT NULL ,
`body` TEXT NOT NULL ,
`body_xml` TEXT NOT NULL ,
`body_format` INT NOT NULL ,
`has_large_body` TINYINT NOT NULL ,
`large_body` MEDIUMTEXT NOT NULL ,
`large_body_xml` MEDIUMTEXT NOT NULL ,
`large_body_format` INT NOT NULL ,
`large_body_filename` VARCHAR( 70 ) NOT NULL ,
`priority` TINYINT NOT NULL ,
`index0` INT NOT NULL ,
`index1` INT NOT NULL ,
`index2` INT NOT NULL ,
`set0` INT NOT NULL ,
`set0_index` INT NOT NULL ,
`set1` INT NOT NULL ,
`set1_index` INT NOT NULL ,
`vote` INT NOT NULL ,
`vote_count` INT UNSIGNED NOT NULL ,
`rating` DOUBLE NOT NULL ,
`tmpid` INT,
`created` DATETIME NOT NULL ,
`modified` DATETIME NOT NULL ,
`accessed` DATETIME NOT NULL ,
`modbits` INT NOT NULL ,
`answers` INT NOT NULL ,
`last_answer` DATETIME NOT NULL ,
`last_answer_id` INT NOT NULL ,
`last_answer_user_id` INT NOT NULL ,
`small_image` INT NOT NULL ,
`small_image_x` SMALLINT NOT NULL ,
`small_image_y` SMALLINT NOT NULL ,
`large_image` INT NOT NULL ,
`large_image_x` SMALLINT NOT NULL ,
`large_image_y` SMALLINT NOT NULL ,
`large_image_size` INT NOT NULL ,
`large_image_format` VARCHAR( 30 ) NOT NULL ,
`large_image_filename` VARCHAR( 70 ) NOT NULL ,
`used` TINYINT NOT NULL ,
PRIMARY KEY ( `id` ) ,
UNIQUE (
`ident`
) ,
UNIQUE (
`tmpid`
)
) DEFAULT CHARSET=koi8u COLLATE=koi8u_bin;
ALTER TABLE `entries` ADD INDEX ( `entry` );
ALTER TABLE `entries` ADD INDEX ( `up` );
ALTER TABLE `entries` ADD INDEX ( `track` );
ALTER TABLE `entries` ADD INDEX ( `parent_id` );
ALTER TABLE `entries` ADD INDEX ( `orig_id` );
ALTER TABLE `entries` ADD INDEX ( `current_id` );
ALTER TABLE `entries` ADD INDEX ( `grp` );
ALTER TABLE `entries` ADD INDEX ( `person_id` );
ALTER TABLE `entries` ADD INDEX ( `user_id` );
ALTER TABLE `entries` ADD INDEX ( `group_id` );
ALTER TABLE `entries` ADD INDEX ( `perms` );
ALTER TABLE `entries` ADD INDEX ( `disabled` );
ALTER TABLE `entries` ADD INDEX ( `subject_sort` );
ALTER TABLE `entries` ADD INDEX ( `url_domain` );
ALTER TABLE `entries` ADD INDEX ( `url_check` );
ALTER TABLE `entries` ADD INDEX ( `url_check_success` );
ALTER TABLE `entries` ADD INDEX ( `priority` );
ALTER TABLE `entries` ADD INDEX ( `index0` );
ALTER TABLE `entries` ADD INDEX ( `index1` );
ALTER TABLE `entries` ADD INDEX ( `rating` );
ALTER TABLE `entries` ADD INDEX ( `modbits` );
ALTER TABLE `entries` ADD INDEX ( `answers` );
ALTER TABLE `entries` ADD INDEX ( `last_answer` );
ALTER TABLE `entries` ADD INDEX ( `used` );
DROP TABLE `rating_positions` ,
`ratings` ;
ALTER TABLE `chat_messages` CHANGE `text` `text` VARCHAR( 255 ) NOT NULL;
ALTER TABLE `chat_messages` ADD `text_xml` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `counters` ADD `entry_id` INT NOT NULL AFTER `message_id` ;
ALTER TABLE `counters` ADD INDEX ( `entry_id` ) ;
CREATE TABLE `cross_entries` (
`id` INT NOT NULL AUTO_INCREMENT ,
`source_name` VARCHAR( 255 ) NULL ,
`source_id` INT NULL ,
`link_type` INT NOT NULL ,
`peer_name` VARCHAR( 255 ) NULL ,
`peer_id` INT NULL ,
`peer_path` VARCHAR( 255 ) NOT NULL ,
`peer_subject` VARCHAR( 255 ) NOT NULL ,
`peer_subject_sort` VARCHAR( 255 ) NOT NULL ,
`peer_icon` VARCHAR( 64 ) NOT NULL ,
PRIMARY KEY ( `id` )
) DEFAULT CHARSET=koi8u COLLATE=koi8u_bin;
ALTER TABLE `packages` ADD `entry_id` INT NOT NULL AFTER `message_id` ;
ALTER TABLE `packages` ADD INDEX ( `entry_id` ) ;
TRUNCATE TABLE `sessions`;
ALTER TABLE `sessions` CHANGE `sid` `sid` CHAR( 32 );
ALTER TABLE `sessions` DROP INDEX `sid` ,
ADD UNIQUE `sid` ( `sid` );
ALTER TABLE `sessions` ADD `duration` INT NOT NULL AFTER `last` ;
ALTER TABLE `stotext_images` RENAME `inner_images`;
ALTER TABLE `inner_images` ADD `entry_id` INT NOT NULL AFTER `stotext_id` ;
ALTER TABLE `inner_images` ADD INDEX ( `entry_id` ) ;
ALTER TABLE `inner_images` ADD `x` TINYINT NOT NULL AFTER `par` ,
ADD `y` TINYINT NOT NULL AFTER `x` ;
ALTER TABLE `users` ADD `rights` INT NOT NULL AFTER `admin_domain` ;
ALTER TABLE `users` ADD INDEX ( `shames` );
ALTER TABLE `users` ADD INDEX ( `guest` );
ALTER TABLE `users` DROP INDEX `confirm_deadline_2`;
ALTER TABLE `votes` ADD `entry_id` INT NOT NULL AFTER `posting_id` ;
ALTER TABLE `votes` ADD INDEX ( `entry_id` ) ;
ALTER TABLE `entries` ADD `sent` DATETIME NOT NULL AFTER `tmpid` ;
ALTER TABLE `entries` ADD INDEX ( `sent` ) ;
CREATE TABLE `image_files` (
`max_id` INT NOT NULL
) DEFAULT CHARSET=koi8u COLLATE=koi8u_bin;
INSERT INTO `image_files` ( `max_id` )
VALUES (
'0'
);
UPDATE version SET db_version =10;
ALTER TABLE `inner_images` ADD `image_entry_id` INT NOT NULL AFTER `image_id` ;
ALTER TABLE `inner_images` ADD INDEX ( `image_entry_id` ) ;
ALTER TABLE `users` ADD `info_xml` TEXT NOT NULL AFTER `info` ;
ALTER TABLE `redirs` DROP `name` ;
CREATE TABLE `entry_grps` (
`entry_id` INT NOT NULL ,
`grp` INT NOT NULL ,
INDEX ( `entry_id` )
)  DEFAULT CHARSET=koi8u COLLATE=koi8u_bin;
ALTER TABLE `entry_grps` ADD INDEX ( `grp` ) ;
DELETE FROM `complain_actions` WHERE script_id <>1;
ALTER TABLE `complain_actions` DROP `type_id` ,
DROP `automatic` ;
DROP TABLE `mailings` ;
CREATE TABLE `mail_queue` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`created` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
`destination` VARCHAR( 255 ) NOT NULL ,
`subject` VARCHAR( 255 ) NOT NULL ,
`headers` TEXT NOT NULL ,
`body` MEDIUMTEXT NOT NULL ,
INDEX ( `created` )
) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_bin;
CREATE TABLE `mail_log` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`sent` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
INDEX ( `sent` )
) ENGINE = MYISAM ;
ALTER TABLE `users` DROP `last_chat` ,
DROP `in_chat` ;
