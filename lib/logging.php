<?php
# @(#) $Id$

require_once('lib/errors.php');
require_once('lib/logs.php');
require_once('lib/sessions.php');
require_once('lib/users.php');
require_once('lib/chat-users.php');

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

function logout()
{
global $sessionid;

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
return EG_OK;
}
?>
