<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/users.php');
require_once('lib/logs.php');
require_once('lib/sessions.php');

function switchUser($sessionid,$login)
{
global $userAdminUsers;

if(!$userAdminUsers)
  return EUS_NO_SWITCH;
$id=getUserIdByLogin($login);
if($id<=0)
  return EUS_NO_USER;
logEvent('su',"user($id)");
updateSessionUserId($sessionid,$id);
return EUS_OK;
}

postString('okdir');
postString('faildir');
postString('login');

dbOpen();
session();
$err=switchUser($sessionid,$login);
if($err==EUS_OK)
  header("Location: $okdir");
else
  header('Location: '.remakeURI($faildir,
                                array(),
		  	        array('err' => $err)).'#error');
dbClose();
?>
