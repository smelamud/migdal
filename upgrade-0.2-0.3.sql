ALTER TABLE `forums` CHANGE `up` `parent_id` INT(11) DEFAULT '0' NOT NULL;
ALTER TABLE `messages` ADD `up` INT NOT NULL AFTER `id`, ADD `track` VARCHAR(255) NOT NULL AFTER `up`;
ALTER TABLE `messages` ADD INDEX (`up`, `track`);
update version set db_version=2;
DROP TABLE `choices`;
DROP TABLE `complain_scripts`;
DROP TABLE `complain_types`;
ALTER TABLE `complains` ADD `no_auto` TINYINT NOT NULL;
ALTER TABLE `users` ADD `name_sort` VARCHAR(60) NOT NULL AFTER `name`;
ALTER TABLE `users` ADD INDEX (`name_sort`);
ALTER TABLE `users` ADD `jewish_name_sort` VARCHAR(60) NOT NULL AFTER `jewish_name`;
ALTER TABLE `users` ADD INDEX (`jewish_name_sort`);
ALTER TABLE `users` ADD `surname_sort` VARCHAR(60) NOT NULL AFTER `surname`;
ALTER TABLE `users` ADD INDEX (`surname_sort`);
ALTER TABLE `messages` ADD `url_domain` VARCHAR(70) NOT NULL AFTER `sent`;
ALTER TABLE `messages` ADD INDEX (`url_domain`);
ALTER TABLE `messages` ADD `topic_link` INT NOT NULL AFTER `sent`;
ALTER TABLE `messages` ADD `url_check` DATETIME NOT NULL, ADD `url_check_success` DATETIME NOT NULL;
ALTER TABLE `messages` ADD INDEX (`url_check`, `url_check_success`); 
ALTER TABLE `messages` ADD `lang` CHAR(5) NOT NULL AFTER `track`;
ALTER TABLE `topics` ADD `virtual` TINYINT NOT NULL AFTER `hidden`;
ALTER TABLE `topics` ADD `index0` INT NOT NULL, ADD `index1` INT NOT NULL, ADD `index2` INT NOT NULL, ADD `index3` INT NOT NULL, ADD `index4` INT NOT NULL;
ALTER TABLE `topics` ADD INDEX (`index0`, `index1`, `index2`, `index3`, `index4`);
CREATE TABLE `multisites` (
 `domain` VARCHAR(70) NOT NULL, 
 `replacer` VARCHAR(70) NOT NULL,
 INDEX (`domain`)
 );
CREATE TABLE `links` (
 `id` INT NOT NULL, 
 `message_id` INT NOT NULL, 
 `title` VARCHAR(250) NOT NULL, 
 `url` VARCHAR(250) NOT NULL, 
 `url_check` DATETIME NOT NULL, 
 `url_check_success` DATETIME NOT NULL,
 PRIMARY KEY (`id`),
 INDEX (`message_id`, `url_check`, `url_check_success`)
 );
ALTER TABLE `messages` ADD INDEX(`sent`);
ALTER TABLE `messages` DROP `topic_link`;
CREATE TABLE cross_topics (
  topic_id int(11) NOT NULL default '0',
  peer_id int(11) NOT NULL default '0',
  KEY topic_id (topic_id,peer_id)
);
ALTER TABLE `topics` DROP `virtual`;
ALTER TABLE `postings` ADD `shadow` TINYINT NOT NULL AFTER `message_id`;
ALTER TABLE `postings` ADD INDEX (`shadow`);
ALTER TABLE `forums` ADD `used` TINYINT NOT NULL;
ALTER TABLE `forums` ADD INDEX (`used`);
ALTER TABLE `messages` ADD `used` TINYINT NOT NULL;
ALTER TABLE `messages` ADD INDEX (`used`); 
ALTER TABLE `postings` ADD `used` TINYINT NOT NULL;
ALTER TABLE `postings` ADD INDEX (`used`); 
ALTER TABLE `stotexts` ADD `used` TINYINT NOT NULL;
ALTER TABLE `stotexts` ADD INDEX (`used`);
ALTER TABLE `stotext_images` ADD `used` TINYINT NOT NULL;
ALTER TABLE `stotext_images` ADD INDEX (`used`);
ALTER TABLE `complains` ADD `used` TINYINT NOT NULL;
ALTER TABLE `complains` ADD INDEX (`used`); 
ALTER TABLE `topics` ADD `used` TINYINT NOT NULL;
ALTER TABLE `topics` ADD INDEX (`used`);
ALTER TABLE `topics` ADD `separate` TINYINT NOT NULL AFTER `index4`;
ALTER TABLE `topics` ADD INDEX (`separate`);
alter table users add guest tinyint after shames;
ALTER TABLE `users` ADD `in_chat` TINYINT NOT NULL;
ALTER TABLE `users` ADD INDEX ( `in_chat` );
CREATE TABLE `journal` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`seq` INT NOT NULL ,
`result_table` VARCHAR( 30 ) NOT NULL ,
`result_id` INT NOT NULL ,
`result_var` INT NOT NULL ,
`query` MEDIUMBLOB NOT NULL ,
`sent` TIMESTAMP NOT NULL ,
INDEX ( `seq` , `result_table` , `result_id` , `result_var` , `sent` )
);
CREATE TABLE `journal_vars` (
`var` INT NOT NULL ,
`host` VARCHAR( 30 ) NOT NULL ,
`value` INT NOT NULL ,
INDEX ( `var` , `host` )
);
CREATE TABLE `horisonts` (
`host` VARCHAR( 30 ) NOT NULL ,
`horisont` INT NOT NULL ,
PRIMARY KEY ( `host` )
);
ALTER TABLE `horisonts` DROP `horisont`;
ALTER TABLE `horisonts` ADD `we_know` INT NOT NULL ,
ADD `they_know` INT NOT NULL ;
ALTER TABLE `horisonts` ADD `calling` TINYINT NOT NULL ;
ALTER TABLE `journal_vars` ADD `last_read` TIMESTAMP NOT NULL ;
ALTER TABLE `horisonts` DROP `calling` ;
ALTER TABLE `horisonts` ADD `lock` DATETIME;
CREATE TABLE `groups` (
`user_id` INT NOT NULL ,
`group_id` INT NOT NULL ,
INDEX ( `user_id` , `group_id` )
);
ALTER TABLE `topics` ADD `group_id` INT NOT NULL AFTER `user_id` ,
ADD `perms` INT NOT NULL AFTER `group_id` ;
ALTER TABLE `topics` ADD INDEX ( `group_id` ) ;
ALTER TABLE `messages` ADD `group_id` INT NOT NULL AFTER `sender_id` ,
ADD `perms` INT NOT NULL AFTER `group_id` ;
ALTER TABLE `messages` ADD INDEX ( `group_id` ) ;
UPDATE topics SET perms = IF(user_id=0,0x19FF,0x11FF);
UPDATE topics SET user_id = 3 WHERE user_id = 0;
UPDATE topics SET group_id = user_id;
ALTER TABLE `topics` DROP `hidden`;
ALTER TABLE `messages` ADD INDEX (`disabled`);
ALTER TABLE `cross_topics` ADD `topic_grp` INT NOT NULL AFTER `topic_id`;
ALTER TABLE `cross_topics` ADD INDEX (`topic_grp`);
ALTER TABLE `cross_topics` ADD `peer_grp` INT NOT NULL;
ALTER TABLE `cross_topics` ADD INDEX (`peer_grp`);
ALTER TABLE `messages` ADD INDEX (`url`);
ALTER TABLE `cross_topics` DROP INDEX `topic_id`;
ALTER TABLE `cross_topics` ADD INDEX ( `topic_id` );
ALTER TABLE `cross_topics` ADD INDEX ( `peer_id` );
ALTER TABLE `groups` DROP INDEX `user_id`;
ALTER TABLE `groups` ADD INDEX ( `user_id` );
ALTER TABLE `groups` ADD INDEX ( `group_id` );
ALTER TABLE `journal` DROP INDEX `seq`;
ALTER TABLE `journal` ADD INDEX ( `seq` );
ALTER TABLE `journal` ADD INDEX ( `result_table` );
ALTER TABLE `journal` ADD INDEX ( `result_id` );
ALTER TABLE `journal` ADD INDEX ( `result_var` );
ALTER TABLE `journal` ADD INDEX ( `sent` );
ALTER TABLE `journal_vars` DROP INDEX `var`;
ALTER TABLE `journal_vars` ADD INDEX ( `var` );
ALTER TABLE `journal_vars` ADD INDEX ( `host` );
ALTER TABLE `journal_vars` ADD INDEX ( `last_read` );
ALTER TABLE `links` DROP INDEX `message_id`;
ALTER TABLE `links` ADD INDEX ( `url_check` );
ALTER TABLE `links` ADD INDEX ( `url_check_success` );
ALTER TABLE `logs` DROP INDEX `id`;
ALTER TABLE `logs` ADD INDEX ( `event` );
ALTER TABLE `logs` ADD INDEX ( `sent` );
ALTER TABLE `messages` DROP INDEX `up`;
ALTER TABLE `messages` ADD INDEX ( `up` );
ALTER TABLE `messages` ADD INDEX ( `track` );
ALTER TABLE `messages` DROP INDEX `url_check`;
ALTER TABLE `messages` ADD INDEX ( `url_check` );
ALTER TABLE `messages` ADD INDEX ( `url_check_success` );
ALTER TABLE `postings` DROP INDEX `read_count`;
ALTER TABLE `postings` ADD INDEX ( `read_count` );
ALTER TABLE `postings` ADD INDEX ( `index0` );
ALTER TABLE `postings` ADD INDEX ( `index1` );
ALTER TABLE `postings` ADD INDEX ( `index2` );
ALTER TABLE `postings` ADD INDEX ( `index3` );
ALTER TABLE `postings` ADD INDEX ( `index4` );
ALTER TABLE `redirs` DROP INDEX `id`;
ALTER TABLE `redirs` ADD INDEX ( `up` );
ALTER TABLE `redirs` ADD INDEX ( `track` );
ALTER TABLE `redirs` ADD INDEX ( `last_access` );
ALTER TABLE `stotext_images` ADD INDEX ( `par` );
ALTER TABLE `tmp_texts` ADD INDEX ( `last_access` );
ALTER TABLE `topics` DROP INDEX `index0`;
ALTER TABLE `topics` ADD INDEX ( `index0` );
ALTER TABLE `topics` ADD INDEX ( `index1` );
ALTER TABLE `topics` ADD INDEX ( `index2` );
ALTER TABLE `topics` ADD INDEX ( `index3` );
ALTER TABLE `topics` ADD INDEX ( `index4` );
ALTER TABLE `votes` DROP INDEX `posting_id`;
ALTER TABLE `votes` ADD INDEX ( `posting_id` );
ALTER TABLE `votes` ADD INDEX ( `ip` );
ALTER TABLE `votes` ADD INDEX ( `user_id` );
ALTER TABLE `votes` ADD INDEX ( `sent` );
ALTER TABLE `messages` ADD INDEX ( `hidden` );
DROP TABLE `links`;
ALTER TABLE `messages` ADD `last_updated` TIMESTAMP NOT NULL;
ALTER TABLE `messages` ADD INDEX (`last_updated`);
CREATE TABLE `packages` (
`id` INT NOT NULL AUTO_INCREMENT,
`posting_id` INT NOT NULL ,
`type` INT NOT NULL ,
`mime_type` VARCHAR( 50 ) NOT NULL ,
`title` VARCHAR( 250 ) NOT NULL ,
`body` LONGBLOB NOT NULL ,
`size` INT NOT NULL ,
`url` VARCHAR( 250 ) NOT NULL ,
`created` DATETIME NOT NULL ,
PRIMARY KEY ( `id` )
);
ALTER TABLE `packages` ADD INDEX ( `posting_id` );
ALTER TABLE `packages` ADD INDEX ( `type` );
ALTER TABLE `packages` ADD INDEX ( `created` );
ALTER TABLE `packages` ADD `used` TINYINT NOT NULL;
ALTER TABLE `packages` ADD INDEX ( `used` );
ALTER TABLE `packages` CHANGE `posting_id` `message_id` INT( 11 ) DEFAULT '0' NOT NULL;
TRUNCATE TABLE `packages`;
CREATE TABLE `postings_info` (
`grp` INT NOT NULL ,
`topic_id` INT NOT NULL ,
`answers` INT NOT NULL ,
`user_id` INT NOT NULL ,
`recursive` SMALLINT NOT NULL ,
`total` INT NOT NULL ,
`max_sent` INT NOT NULL,
PRIMARY KEY  (grp,topic_id,answers,user_id,recursive)
);
ALTER TABLE `postings_info` ADD INDEX ( `grp` );
ALTER TABLE `postings_info` ADD INDEX ( `answers` );
ALTER TABLE `postings_info` ADD INDEX ( `topic_id` );
