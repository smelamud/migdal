<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/uri.php');
require_once('lib/errors.php');
require_once('lib/tmptexts.php');
require_once('lib/complains.php');

function modifyComplain($complain)
{
global $userId;

if($userId<=0)
  return EC_NO_SEND;
if(!$complain->isEditable())
  return EC_NO_EDIT;
if($complain->stotext->body=='')
  return EC_BODY_ABSENT;
if($complain->subject=='')
  return EC_SUBJECT_ABSENT;
if(!complainTypeExists($complain->type_id))
  return EC_NO_TYPE;
if(!$complain->store())
  return EC_STORE_SQL;
return EC_OK;
}

postInteger('editid');
postInteger('type_id');
postString('body');
postString('subject');

dbOpen();
session($sessionid);
$complain=getComplainById($editid);
$complain->setup($HTTP_POST_VARS);
$err=modifyComplain($complain);
if($err==EC_OK)
  header("Location: $okdir");
else
  {
  $bodyId=tmpTextSave($body);
  $subjectId=tmpTextSave($subject);
  header('Location: '.
          remakeMakeURI($faildir,
			$HTTP_POST_VARS,
			array('body','subject','okdir','faildir'),
			array('bodyid'       => $bodyId,
			      'subjectid'    => $subjectId,
			      'err'          => $err)).'#error');
  }
dbClose();
?>
