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
