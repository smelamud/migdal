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
