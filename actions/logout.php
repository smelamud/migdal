<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/uri.php');
require_once('lib/post.php');
require_once('lib/random.php');
require_once('lib/errors.php');
require_once('lib/logs.php');
require_once('lib/sessions.php');
require_once('lib/session.php');
require_once('lib/users.php');

function logout($sessionid)
{
$row=getUserIdsBySessionId($sessionid);
if($row)
  {
  list($userId,$realUserId)=$row;
  logEvent('logout',"user($userId)");
  if($userId!=0 && $userId!=$realUserId)
    {
    updateSession($sessionid,$realUserId,$realUserId);
    return ELO_OK;
    }
  clearLastChat($userId);
  }
updateSession($sessionid,0,getGuestId());
return ELO_OK;
}

dbOpen();
session();
$err=logout($sessionid);
if($err==ELO_OK)
  header('Location: '.remakeURI($okdir,
				array(),
				array('reload' => random(0,999))));
else
  header('Location: '.remakeURI($faildir,
				array(),
				array('err' => $err)));
dbClose();
?>
