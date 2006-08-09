<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/utils.php');
require_once('lib/random.php');
/* Required to prevent inclusion of Posting class before Message */
require_once('lib/postings.php');

function modifyMessage($editid,$hide)
{
$perms=getPermsById('messages',$editid);
if(!$perms->isWritable())
  return EMS_NO_HIDE;
if(!messageExists($editid))
  return EMS_NO_MESSAGE;
setHiddenByMessageId($editid,$hide);
return EG_OK;
}

postInteger('editid');

dbOpen();
session();
$err=modifyMessage($editid,$hide ? 1 : 0);
if($err==EG_OK)
  header('Location: '.remakeURI($okdir,array(),array('reload' => random(0,999))));
else
  header('Location: '.remakeURI($faildir,array(),array('err' => $err)).'#error');
dbClose();
?>
