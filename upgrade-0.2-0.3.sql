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
