<?php
# @(#) $Id$

function IPToInteger($addr)
{
$octets=explode('.',$addr);
$ip=0;
foreach($octets as $oct)
       $ip=$ip*256+$oct;
return $ip;
}

function logEvent($event,$body)
{
global $REMOTE_ADDR;

$event=addslashes($event);
$ip=IPToInteger($REMOTE_ADDR);
$body=addslashes($body);
mysql_query("insert into logs(event,ip,body)
             values('$event',$ip,'$body')")
     or die("Ошибка SQL при добавлении в лог");
}
?>
