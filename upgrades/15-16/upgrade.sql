UPDATE entries SET rating = vote -3 * cast( vote_count AS signed ) ;
UPDATE `version` SET `db_version` = '16' WHERE `version`.`db_version` =15 LIMIT 1 ;
