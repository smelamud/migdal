ALTER TABLE `entries` ADD `last_answer_guest_login` VARCHAR( 30 ) CHARACTER SET koi8u COLLATE koi8u_general_ci NOT NULL AFTER `last_answer_user_id` ;
UPDATE `version` SET `db_version` = '15' WHERE `version`.`db_version` =14 LIMIT 1 ;
