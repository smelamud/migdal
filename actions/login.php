<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/errors.php');
require_once('lib/bug.php');
require_once('lib/utils.php');
require_once('lib/post.php');
require_once('lib/logs.php');
require_once('lib/sessions.php');
require_once('lib/session.php');
require_once('lib/users.php');
require_once('lib/chat-users.php');

function startSession()
{
global $login,$password,$sessionid,$userId,$realUserId;

$id=getUserIdByLoginPassword(addslashes($login),$password);
if($id==0)
  return EL_INVALID;
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
return EL_OK;
}

postString('login');
postString('password');

dbOpen();
session();
$err=startSession();
if($err==EL_OK)
  header('Location: /actions/checkcookies.php?'.
          makeQuery(array('svalue'  => $sessionid,
	                  'okdir'   => $okdir,
			  'faildir' => $faildir)));
else
  header('Location: '.remakeURI($faildir,
                                array(),
		  	        array('err' => $err)).'#error');
dbClose();
?>
