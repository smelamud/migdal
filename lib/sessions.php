<?php
# @(#) $Id$

require_once('lib/random.php');
require_once('lib/bug.php');
require_once('lib/sql.php');

function getUserIdBySessionId($sessionId)
{
$result=sql("select user_id from sessions where sid=$sessionId",
            'getUserIdBySessionId');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : -1;
}

function getUserIdsBySessionId($sessionId)
{
$result=sql("select user_id,real_user_id
	     from sessions
	     where sid=$sessionId",
	    'getUserIdsBySessionId');
return mysql_num_rows($result)>0 ? mysql_fetch_array($result) : 0;
}

function updateSessionTimestamp($sessionId)
{
sql("update sessions set last=null where sid=$sessionId",
    'updateSessionTimestamp');
}

function createSession($userId,$realUserId=0)
{
$sid=rnd();
sql("insert into sessions(user_id,real_user_id,sid)
     values($userId,$realUserId,$sid)",
    'createSession');
return $sid;
}

function updateSession($sessionId,$userId,$realUserId)
{
sql("update sessions
     set user_id=$userId,real_user_id=$realUserId
     where sid=$sessionId",
    'updateSession');
}

function updateSessionUserId($sessionId,$userId)
{
sql("update sessions
     set user_id=$userId
     where sid=$sessionId",
    'updateSessionUserId');
}

function deleteSession($sessionId)
{
sql("delete from sessions
     where sid=$sessionId",
    'deleteSession');
}

function setSessionCookie($sessionId)
{
global $sessionTimeout,$siteDomain;

if($sessionId==0)
  SetCookie('sessionid',0,0,'/',$siteDomain);
else
  SetCookie('sessionid',$sessionId,time()+($sessionTimeout+24)*3600,'/',
	    $siteDomain);
}
?>
