create table version
(
db_version int not null
);
INSERT INTO `version` (`db_version`) VALUES ('1');
ALTER TABLE `mailing_types` ADD `force_send` TINYINT NOT NULL;
UPDATE mailing_types SET force_send=1 WHERE ident='register';
ALTER TABLE `messages` ADD `author` VARCHAR(250) NOT NULL AFTER `subject`,
ADD `source` VARCHAR(250) NOT NULL AFTER `author`; 
