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

function logout($sessionid)
{
global $siteDomain;

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
deleteSession($sessionid);
SetCookie('sessionid',0,0,'/',$siteDomain);
return ELO_OK;
}

settype($sessionid,'integer');

dbOpen();
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
