<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/forums.php');
require_once('lib/sql.php');

function deleteForumAction($id)
{
if(!forumExists($id))
  return EFD_FORUM_ABSENT;
$perms=getPermsById($id);
if(!$perms->isWritable())
  return EFD_NO_DELETE;
deleteForum($id);
return EG_OK;
}

httpRequestString('okdir');
httpRequestString('faildir');

httpRequestInteger('id');

dbOpen();
session();
$err=deleteForumAction($id);
if($err==EG_OK)
  header('Location: '.remakeURI($okdir,
                                array('err'),
				array('reload' => random(0,999))));
else
  header('Location: '.remakeURI($faildir,
                                array(),
				array('err' => $err)).'#error');
dbClose();
?>
