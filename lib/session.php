<?php
# @(#) $Id$

function session($sessionId)
{
global $userId;

if(!$sessionId)
  {
  $userId=-1;
  return;
  }
$result=mysql_query("select user_id from sessions where id=$sessionId");
if(!$result)
  die('Ошибка SQL при выборке сессии');
if(mysql_num_rows($result)<=0)
  {
  SetCookie('sessionid');
  $userId=-1;
  }
else
  {
  $userId=mysql_result($result,0,0);
  mysql_query("update sessions set last=null where id=$sessionId")
                   or die('Ошибка SQL при обновлении TIMESTAMP сессии');
  SetCookie('sessionid',$sessionId,time()+7200);
  }
}
?>
