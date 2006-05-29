DROP TABLE `cross_topics`;
DROP TABLE `complains` ;
ALTER TABLE `counters` DROP `message_id` ;
DROP TABLE `forums` ;
DROP TABLE `images` ;
ALTER TABLE `inner_images` DROP `stotext_id` ;
ALTER TABLE `inner_images` DROP `image_id` ;
ALTER TABLE `inner_images` CHANGE `image_entry_id` `image_id` INT( 11 ) NOT NULL DEFAULT '0';
DROP TABLE `instants` ;
DROP TABLE `messages` ;
DROP TABLE `multisites` ;
ALTER TABLE `packages` DROP `message_id` ;
DROP TABLE `postings` ;
DROP TABLE `postings_info` ;
DROP TABLE `stotexts` ;
DROP TABLE `topics` ;
ALTER TABLE `users` DROP `migdal_student` ,
DROP `accepts_complains` ,
DROP `rebe` ,
DROP `admin_users` ,
DROP `admin_topics` ,
DROP `admin_complain_answers` ,
DROP `moderator` ,
DROP `judge` ,
DROP `admin_domain` ;
ALTER TABLE `votes` DROP `posting_id` ;
ALTER TABLE `users` CHANGE `guest` `guest` TINYINT( 4 ) NOT NULL DEFAULT '0';
ALTER TABLE `inner_images` DROP INDEX `entry_id`;
ALTER TABLE `inner_images` DROP INDEX `par`;
DELETE FROM `inner_images` WHERE entry_id =0;
ALTER TABLE `inner_images` ADD PRIMARY KEY ( `entry_id` , `par` , `x` , `y` );
