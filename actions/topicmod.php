<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/topics.php');
require_once('lib/entries.php');
require_once('lib/utils.php');
require_once('lib/errors.php');
require_once('lib/tmptexts.php');
require_once('lib/track.php');
require_once('lib/catalog.php');
require_once('lib/users.php');

function modifyTopic($topic,$original)
{
global $userLogin,$topicMandatoryDescription,$rootTopicUserName,
       $rootTopicGroupName,$rootTopicPerms;

if($original->getId()!=0 && !$original->isWritable())
  return ET_NO_EDIT;
if($topic->subject=='')
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
if($topicMandatoryDescription && $topic->body=='')
  return ET_DESCRIPTION_ABSENT;
if($topic->up<0)
  $topic->up=0;
$correct=validateHierarchy(0,$topic->up,ENT_TOPIC,$topic->id);
if($correct!=EG_OK)
  return $correct;
if($topic->up!=0)
  $upPerms=getPermsById($topic->up);
else
  $upPerms=new Entry(array('user_id'  => getUserIdByLogin($rootTopicUserName),
                           'group_id' => getUserIdByLogin($rootTopicGroupName),
                           'perms'    => $rootTopicPerms));
if(!$upPerms->isAppendable())
  return ET_NO_APPEND;
$cid=idByIdent($topic->ident);
if(!is_null($topic->ident) && $cid!=0 && $topic->id!=$cid)
  return ET_IDENT_UNIQUE;
if($topic->id==0 || $original->up!=$topic->up)
  $topic->track='';
if($topic->id==0 || $original->up!=$topic->up
   || $original->ident!=$topic->ident || $original->modbits!=$topic->modbits)
  $topic->catalog='';
storeTopic($topic);
setGrpsByEntryId($topic->id,$topic->grps);
return EG_OK;
}

postString('okdir');
postString('faildir');
postInteger('editid');
postInteger('edittag');
postString('body');
postInteger('up');
postString('subject');
postString('comment0');
postString('comment1');
postString('ident');
postString('login');
postString('user_name');
postString('group_login');
postString('group_name');
postString('perm_string',false);
postIntegerArray('modbits');
postInteger('index2');
postIntegerArray('grps');

dbOpen();
session();
$topic=getTopicById($editid,$up);
$original=$topic;
$topic->setup($Args);
$err=modifyTopic($topic,$original);
if($err==EG_OK)
  header("Location: $okdir");
else
  {
  $bodyId=tmpTextSave($body);
  header('Location: '.
          remakeMakeURI($faildir,
			$Args,
			array('body',
			      'okdir',
			      'faildir'),
			array('body_i' => $bodyId,
			      'err'    => $err)).'#error');
  }
dbClose();
?>
