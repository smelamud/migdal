<?php
# @(#) $Id$

require_once('lib/random.php');
require_once('lib/bug.php');
require_once('lib/sql.php');

function getUserIdBySessionId($sessionId)
{
$sessionIdS=addslashes($sessionId);
$result=sql("select user_id
             from sessions
	     where sid='$sessionIdS'",
            __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : -1;
}

function sessionExists($sessionId)
{
return getUserIdBySessionId($sessionId)>=0;
}

function getUserIdsBySessionId($sessionId)
{
$sessionIdS=addslashes($sessionId);
$result=sql("select user_id,real_user_id
	     from sessions
	     where sid='$sessionIdS'",
	    __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_fetch_array($result) : 0;
}

function updateSessionTimestamp($sessionId)
{
$sessionIdS=addslashes($sessionId);
sql("update sessions
     set last=null
     where sid='$sessionIdS'",
    __FUNCTION__);
}

function createSessionId()
{
do
  {
  $sid=md5(uniqid(rand(),true));
  }
while(sessionExists($sid));
return $sid;
}

function createSession($userId,$realUserId=0)
{
$sid=createSessionId();
sql("insert into sessions(user_id,real_user_id,sid)
     values($userId,$realUserId,'$sid')",
    __FUNCTION__);
return $sid;
}

function updateSession($sessionId,$userId,$realUserId)
{
$sessionIdS=addslashes($sessionId);
sql("update sessions
     set user_id=$userId,real_user_id=$realUserId
     where sid='$sessionIdS'",
    __FUNCTION__);
}

function updateSessionUserId($sessionId,$userId)
{
$sessionIdS=addslashes($sessionId);
sql("update sessions
     set user_id=$userId
     where sid='$sessionIdS'",
    __FUNCTION__);
}

function deleteSession($sessionId)
{
$sessionIdS=addslashes($sessionId);
sql("delete from sessions
     where sid='$sessionIdS'",
    __FUNCTION__);
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
