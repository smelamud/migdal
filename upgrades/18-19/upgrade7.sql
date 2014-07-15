INSERT INTO entry_grps( entry_id, grp ) 
SELECT entries.id, 4096
FROM  `entries` 
LEFT JOIN entry_grps ON entry_grps.entry_id = entries.id
WHERE  `entry` =3
AND entry_grps.grp =4;
