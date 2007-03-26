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
$result=sql("select user_id,real_user_id,duration
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

function createSession($userId,$realUserId)
{
global $shortSessionTimeout;

$sid=createSessionId();
sql("insert into sessions(user_id,real_user_id,duration,sid)
     values($userId,$realUserId,$shortSessionTimeout,'$sid')",
    __FUNCTION__);
return $sid;
}

function updateSession($sessionId,$userId,$realUserId,$duration)
{
$sessionIdS=addslashes($sessionId);
sql("update sessions
     set user_id=$userId,real_user_id=$realUserId,duration=$duration
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

function deleteClosedSessions()
{
sql("delete from sessions
     where last+interval duration hour<now()",
    __FUNCTION__,'delete');
sql('optimize table sessions',
    __FUNCTION__,'optimize');
}

function getSubdomainCookie($name)
{
global $siteDomain;

$sd=strtr($siteDomain,'.','_');
return $_COOKIE["$name-$sd"];
}

function setSubdomainCookie($name,$value,$expire,$path,$domain)
{
global $siteDomain;

$sd=strtr($siteDomain,'.','_');
setcookie("$name-$sd",$value,$expire,$path,$domain);
}

function getSessionCookie()
{
return getSubdomainCookie('sessionid');
}

function setSessionCookie($sessionId)
{
global $siteDomain,$longSessionTimeout;

if($sessionId===0)
  setSubdomainCookie('sessionid',0,0,'/',$siteDomain);
else
  setSubdomainCookie('sessionid',$sessionId,
                     time()+($longSessionTimeout+24)*3600,'/',$siteDomain);
}
?>
