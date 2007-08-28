ALTER TABLE `chat_messages` ADD `guest_login` VARCHAR( 30 ) CHARACTER SET koi8u COLLATE koi8u_general_ci NOT NULL AFTER `id` ;
ALTER TABLE `entries` ADD `guest_login` VARCHAR( 30 ) CHARACTER SET koi8u COLLATE koi8u_general_ci NOT NULL AFTER `person_id` ;
UPDATE chat_messages SET sent=sent,guest_login = ( SELECT login
FROM users
WHERE id = chat_messages.sender_id ) where sender_id in (select id from users
where guest<>0);
UPDATE entries SET guest_login = ( SELECT login
FROM users
WHERE id = entries.user_id )
WHERE user_id
IN (
SELECT id
FROM users
WHERE guest<>0
);
UPDATE `users` SET `login` = 'Гость' WHERE `users`.`id` =165 LIMIT 1 ;
UPDATE chat_messages SET sent = sent,
sender_id =165 WHERE sender_id IN (
SELECT id
FROM users
WHERE guest<>0
);
UPDATE entries SET user_id =165 WHERE user_id IN (
SELECT id
FROM users
WHERE guest<>0
);
UPDATE sessions SET last=last,real_user_id =165 WHERE user_id =0;
DELETE FROM users WHERE guest<>0 AND id<>165;
UPDATE `version` SET `db_version` = '14' WHERE `version`.`db_version` =13 LIMIT 1 ;
