<?php
# @(#) $Id$

require_once('lib/random.php');
require_once('lib/bug.php');

function getUserIdBySessionId($sessionId)
{
$result=mysql_query("select user_id from sessions where sid=$sessionId")
	  or sqlbug('Ошибка SQL при выборке сессии');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : -1;
}

function getUserIdsBySessionId($sessionId)
{
$result=mysql_query("select user_id,real_user_id
                     from sessions
		     where sid=$sessionId")
	  or sqlbug('Ошибка SQL при выборке сессии');
return mysql_num_rows($result)>0 ? mysql_fetch_array($result) : 0;
}

function updateSessionTimestamp($sessionId)
{
mysql_query("update sessions set last=null where sid=$sessionId")
  or sqlbug('Ошибка SQL при обновлении TIMESTAMP сессии');
}

function createSession($userId,$realUserId=0)
{
$sid=rnd();
mysql_query("insert into sessions(user_id,real_user_id,sid)
             values($userId,$realUserId,$sid)")
  or sqlbug('Ошибка SQL при создании сессии');
return $sid;
}

function updateSession($sessionId,$userId,$realUserId)
{
mysql_query("update sessions
	     set user_id=$userId,real_user_id=$realUserId
	     where sid=$sessionId")
  or sqlbug('Ошибка SQL при обновлении сессии');
}

function updateSessionUserId($sessionId,$userId)
{
mysql_query("update sessions
	     set user_id=$userId
	     where sid=$sessionId")
  or sqlbug('Ошибка SQL при обновлении сессии');
}

function deleteSession($sessionId)
{
mysql_query("delete from sessions
	     where sid=$sessionId")
  or sqlbug('Ошибка SQL при удалении сессии');
}
?>
