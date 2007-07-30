<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/postings.php');
require_once('lib/sql.php');

function deletePostingAction($id)
{
if(!postingExists($id))
  return EPD_POSTING_ABSENT;
$perms=getPermsById($id);
if(!$perms->isWritable())
  return EPD_NO_DELETE;
deletePosting($id);
return EG_OK;
}

postString('okdir');
postString('faildir');

postInteger('id');

dbOpen();
session();
$err=deletePostingAction($id);
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
