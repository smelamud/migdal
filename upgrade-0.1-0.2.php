<?php
require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/charsets.php');

dbOpen();
$fd=fopen('/tmp/conv','w');
$result=mysql_query('select id,name
                     from topics');
while($row=mysql_fetch_assoc($result))
     {
     fputs($fd,$row['name'].' '.convertOutput($row['name'])
                           .' '.convertSort($row['name'])."\n");
     mysql_query("update topics
                  set name_sort='".convertSort($row['name'])."'
		  where id=".$row['id']);
     }
fclose($fd);
$result=mysql_query('select id,login
                     from users');
while($row=mysql_fetch_assoc($result))
     mysql_query("update users
                  set login_sort='".convertSort($row['login'])."'
		  where id=".$row['id']);
dbClose();
?>
