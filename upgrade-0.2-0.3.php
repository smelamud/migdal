<?php
require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/charsets.php');

dbOpen();
$result=mysql_query('select id,name,jewish_name,surname
                     from users');
while($row=mysql_fetch_assoc($result))
     mysql_query("update users
                  set name_sort='".convertSort($row['name'])."',
                      jewish_name_sort='".convertSort($row['jewish_name'])."',
                      surname_sort='".convertSort($row['surname'])."'
		  where id=".$row['id']);
dbClose();
?>
