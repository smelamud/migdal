ALTER TABLE `forums` CHANGE `up` `parent_id` INT(11) DEFAULT '0' NOT NULL;
ALTER TABLE `messages` ADD `up` INT NOT NULL AFTER `id`, ADD `track` VARCHAR(255) NOT NULL AFTER `up`;
ALTER TABLE `messages` ADD INDEX (`up`, `track`);
