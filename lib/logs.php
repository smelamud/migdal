<?php
# @(#) $Id$

function log($event,$body)
{
$event=addslashes($event);
$body=addslashes($body);
mysql_query("insert into logs(event,body)
             values('$event','$body')")
     or die("Ошибка SQL при добавлении в лог");
}
?>
