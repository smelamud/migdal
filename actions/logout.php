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

function logout($sessionid)
{
$row=getUserIdsBySessionId($sessionid);
if($row)
  {
  logEvent('logout','user('.$row['user_id'].')');
  if($row['user_id']!=$row['real_user_id'])
    {
    updateSession($sessionid,$row['real_user_id'],$row['real_user_id']);
    return ELO_OK;
    }
  }
sessionGuest();
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
