<?php
# @(#) $Id$

require_once('lib/errors.php');
require_once('lib/logs.php');
require_once('lib/sessions.php');
require_once('lib/users.php');
require_once('lib/chat-users.php');

define('RELOGIN_GUEST',1);
define('RELOGIN_SAME',2);
define('RELOGIN_LOGIN',3);

function login($login,$password,$remember=true)
{
global $sessionid,$userId,$realUserId;

$id=getUserIdByLoginPassword($login,$password);
if($id==0)
  return EL_INVALID;
if($remember)
  {
  logEvent('login',"user($id)");
  $prevId=$userId!=0 ? $userId : $realUserId;
  if(isChatLogged($prevId))
    {
    clearLastChat($prevId);
    chatLogout($prevId);
    postChatSwitchMessage($id,$prevId);
    chatLogin($id);
    }
  updateSession($sessionid,$id,$id);
  }
session($id);
return EG_OK;
}

function logout($remember=true)
{
global $sessionid;

if($remember)
  {
  $row=getUserIdsBySessionId($sessionid);
  $guestId=getGuestId();
  if($row)
    {
    list($userId,$realUserId)=$row;
    logEvent('logout',"user($userId)");
    if($userId!=0 && $userId!=$realUserId)
      {
      updateSession($sessionid,$realUserId,$realUserId);
      session();
      return EG_OK;
      }
    if(isChatLogged($userId))
      {
      clearLastChat($userId);
      chatLogout($userId);
      postChatSwitchMessage($guestId,$userId);
      chatLogin($guestId);
      }
    }
  updateSession($sessionid,0,$guestId);
  session();
  }
else
  session(0);
return EG_OK;
}

function relogin($relogin,$login,$password,$remember)
{
if(!$relogin)
  return EG_OK;
switch($relogin)
      {
      case RELOGIN_LOGIN:
           return login($login,$password,$remember);
      case RELOGIN_GUEST:
           return logout($remember);
      }
}
?>
