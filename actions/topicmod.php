<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/topics.php');
require_once('lib/utils.php');
require_once('lib/errors.php');
require_once('lib/tmptexts.php');
require_once('lib/track.php');
require_once('lib/users.php');

function modifyTopic($topic,$original)
{
global $userLogin,$topicMandatoryDescription;

if($original->getId()!=0 && !$original->isWritable())
  return ET_NO_EDIT;
if($topic->name=='')
  return ET_NAME_ABSENT;
if($topic->login=='')
  $topic->login=$userLogin;
$uid=getUserIdByLogin($topic->login);
if($uid==0)
  return ET_NO_USER;
$topic->user_id=$uid;
if($topic->group_login=='')
  $topic->group_login=$userLogin;
$gid=getUserIdByLogin($topic->group_login);
if($gid==0)
  return ET_NO_GROUP;
$topic->group_id=$gid;
if($topic->perms<0)
  return ET_BAD_PERMS;
if($topicMandatoryDescription && $topic->stotext->body=='')
  return ET_DESCRIPTION_ABSENT;
if($topic->up<0)
  $topic->up=0;
if($topic->up!=0)
  {
  if(!topicExists($topic->up))
    return ET_NO_UP;
  if($topic->up==$topic->id)
    return ET_LOOP_UP;
  $upPerms=getPermsById('topics',$topic->up);
  if(!$upPerms->isAppendable())
    return ET_NO_APPEND;
  }
$cid=idByIdent('topics',$topic->ident);
if($topic->ident!='' && $cid!=0 && $topic->id!=$cid)
  return ET_IDENT_UNIQUE;
if($topic->allow==0)
  return ET_MUST_ALLOW;
$topic->track='';
if(!$topic->store())
  return ET_STORE_SQL;
if(!updateTracks('topics',$topic->id))
  return ET_TRACK_SQL;
return ET_OK;
}

postInteger('editid');
postInteger('up');
postString('name');
postString('login');
postString('group_login');
postString('perms');
postString('description');
postString('large_description');

dbOpen();
session();
$topic=getTopicById($editid,$up);
$original=$topic;
$topic->setup($HTTP_POST_VARS);
$err=uploadLargeText($topic->stotext);
if($err==EUL_OK)
  $err=modifyTopic($topic,$original);
if($err==ET_OK)
  header("Location: $okdir");
else
  {
  $descriptionId=tmpTextSave($description);
  $largeDescriptionId=tmpTextSave($large_description);
  header('Location: '.
          remakeMakeURI($faildir,
			$HTTP_POST_VARS,
			array('description',
			      'large_description',
			      'okdir',
			      'faildir'),
			array('descriptionid'       => $descriptionId,
			      'large_descriptionid' => $largeDescriptionId,
			      'err'                 => $err)).'#error');
  }
dbClose();
?>
