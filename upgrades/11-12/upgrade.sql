CREATE TABLE `content_versions` (
`postings_version` INT NOT NULL ,
`forums_version` INT NOT NULL ,
`topics_version` INT NOT NULL
) ENGINE = MYISAM ;
INSERT INTO `content_versions` ( `postings_version` , `forums_version` , `topics_version` )
VALUES (
'', '', ''
);
CREATE TABLE `html_cache` (
`ident` VARCHAR( 255 ) NOT NULL ,
`content` MEDIUMTEXT NOT NULL ,
`deadline` DATETIME NULL ,
`postings_version` INT NULL ,
`forums_version` INT NULL ,
`topics_version` INT NULL ,
PRIMARY KEY ( `ident` )
) ENGINE = MYISAM ;
UPDATE `version` SET `db_version` = '12' WHERE `version`.`db_version` =11 LIMIT 1 ;
