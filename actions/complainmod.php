<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/uri.php');
require_once('lib/errors.php');
require_once('lib/tmptexts.php');
require_once('lib/complains.php');

function modifyComplain($complain,$original)
{
global $userId,$rootComplainUserName,$rootComplainGroupName,$rootComplainPerms;

if($original->getId()==0)
  {
  $root=new Complain(
	 array('sender_id' => getUserIdByLogin($rootComplainUserName),
	       'group_id'  => getUserIdByLogin($rootComplainGroupName),
	       'perms'     => $rootComplainPerms)
	);
  if(!$root->isAppendable())
    return EC_NO_SEND;
  }
else
  if(!$original->isEditable())
    return EC_NO_EDIT;
if($complain->stotext->body=='')
  return EC_BODY_ABSENT;
if($complain->subject=='')
  return EC_SUBJECT_ABSENT;
if($complain->type_id<=COMPL_NONE || $complain->type_id>COMPL_MAX)
  return EC_NO_TYPE;
$complain->store();
return EC_OK;
}

postInteger('editid');
postInteger('type_id');
postString('body');
postString('subject');

dbOpen();
session();
$complain=getComplainById($editid,$type_id);
$original=$complain;
$complain->setup($HTTP_POST_VARS);
$err=modifyComplain($complain,$original);
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
