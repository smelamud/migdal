<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/random.php');

function renewMessage($id)
{
global $userModerator;

if(!$userModerator)
  return EMR_NO_RENEW;
$result=mysql_query("update messages
                     set sent=now()
		     where id=$id");
if(!$result)
  return EMR_SQL;
return EMR_OK;
}

postInteger('id');

dbOpen();
session($sessionid);
$err=renewMessage($id);
if($err==EMR_OK)
  header('Location: '.remakeURI($okdir,
                                array(),
				array('reload' => random(0,999))));
else
  header('Location: '.remakeURI($faildir,
                                array(),
				array('err' => $err)).'#error');
dbClose();
?>
