create table version
(
db_version int not null
);
INSERT INTO `version` (`db_version`) VALUES ('1');
ALTER TABLE `mailing_types` ADD `force_send` TINYINT NOT NULL;
UPDATE mailing_types SET force_send=1 WHERE ident='register';
ALTER TABLE `messages` ADD `author` VARCHAR(250) NOT NULL AFTER `subject`,
ADD `source` VARCHAR(250) NOT NULL AFTER `author`; 
ALTER TABLE `users` ADD `last_chat` DATETIME NOT NULL;
ALTER TABLE `users` ADD INDEX (`last_chat`);
ALTER TABLE `postings` ADD `read_count` INT NOT NULL, ADD `index0` INT NOT NULL, ADD `index1` INT NOT NULL, ADD `index2` INT NOT NULL, ADD `index3` INT NOT NULL, ADD `index4` INT NOT NULL;
ALTER TABLE `postings` ADD INDEX (`read_count`, `index0`, `index1`, `index2`, `index3`, `index4`);
ALTER TABLE postings ADD last_read DATETIME NOT NULL AFTER read_count;
CREATE TABLE `redirs` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
`up` INT NOT NULL,
`track` VARCHAR(255) NOT NULL,
`name` VARCHAR(255) NOT NULL,
`uri` TEXT NOT NULL,
`last_access` TIMESTAMP NOT NULL,
INDEX (`id`, `up`, `track`, `last_access`)
);
ALTER TABLE `sessions` DROP INDEX `last_2`;
ALTER TABLE `sessions` ADD `real_user_id` INT NOT NULL AFTER `user_id`;
DROP TABLE `menu`;
ALTER TABLE `users` DROP `admin_menu`;
ALTER TABLE `topics` ADD `user_id` INT NOT NULL AFTER `name`;
ALTER TABLE `topics` ADD INDEX (`user_id`);
ALTER TABLE `topics` ADD `name_sort` VARCHAR(140) NOT NULL AFTER `name`;
ALTER TABLE `topics` ADD INDEX (`name_sort`); 
ALTER TABLE `users` ADD `login_sort` VARCHAR(60) NOT NULL AFTER `login`;
ALTER TABLE `users` ADD INDEX (`login_sort`);
ALTER TABLE `messages` ADD `url` VARCHAR(250) NOT NULL;
ALTER TABLE `postings` CHANGE `grp` `grp` INT DEFAULT '0' NOT NULL;
CREATE TABLE `logs` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
`event` VARCHAR(30) NOT NULL,
`sent` TIMESTAMP NOT NULL,
`body` VARCHAR(250) NOT NULL,
INDEX (`id`, `event`, `sent`)
);
ALTER TABLE `logs` ADD `ip` INT UNSIGNED NOT NULL AFTER `sent`;
DROP TABLE statistics;
ALTER TABLE `postings` ADD `vote` INT NOT NULL AFTER `last_read`, ADD `vote_count` INT UNSIGNED NOT NULL AFTER `vote`;
CREATE TABLE votes (
  posting_id int(11) NOT NULL default '0',
  ip int(10) unsigned NOT NULL default '0',
  user_id int(11) NOT NULL default '0',
  sent timestamp(14) NOT NULL,
  vote int(11) NOT NULL default '0',
  KEY posting_id (posting_id,ip,user_id,sent)
);
ALTER TABLE `postings` ADD `subdomain` TINYINT NOT NULL;
ALTER TABLE `postings` ADD INDEX (`subdomain`);
ALTER TABLE `users` ADD `admin_domain` TINYINT NOT NULL AFTER `judge`;
update mailing_types set text='register.php' where text='register.mail';
update mailing_types set text='registering.php' where text='registering.mail';
drop table mailing_types;
