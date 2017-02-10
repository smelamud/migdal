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
if($topic->getSubject()=='')
  return ET_NAME_ABSENT;
if($topic->getLogin()=='')
  $topic->setLogin($userLogin);
$uid=getUserIdByLogin($topic->getLogin());
if($uid==0)
  return ET_NO_USER;
$topic->setUserId($uid);
if($topic->getGroupLogin()=='')
  $topic->setGroupLogin($userLogin);
$gid=getUserIdByLogin($topic->getGroupLogin());
if($gid==0)
  return ET_NO_GROUP;
$topic->setGroupId($gid);
if($topic->getPerms()<0)
  return ET_BAD_PERMS;
if($topicMandatoryDescription && $topic->getBody()=='')
  return ET_DESCRIPTION_ABSENT;
if($topic->getUpValue()<0)
  $topic->setUpValue(0);
$correct=validateHierarchy(0,$topic->getUpValue(),ENT_TOPIC,$topic->getId());
if($correct!=EG_OK)
  return $correct;
if($topic->getUpValue()!=0)
  $upPerms=getPermsById($topic->getUpValue());
else
  $upPerms=new Entry(array('user_id'  => getUserIdByLogin($rootTopicUserName),
                           'group_id' => getUserIdByLogin($rootTopicGroupName),
                           'perms'    => $rootTopicPerms));
if(!$upPerms->isAppendable())
  return ET_NO_APPEND;
$cid=idByIdent($topic->getIdent());
if(!is_null($topic->getIdent()) && $cid!=0 && $topic->getId()!=$cid)
  return ET_IDENT_UNIQUE;
if($topic->getId()==0 || $original->getUpValue()!=$topic->getUpValue())
  $topic->setTrack('');
if($topic->getId()==0 || $original->getUpValue()!=$topic->getUpValue()
   || $original->getIdent()!=$topic->getIdent()
   || $original->getModbits()!=$topic->getModbits())
  $topic->setCatalog('');
storeTopic($topic);
setGrpsByEntryId($topic->getId(),$topic->getGrps());
return EG_OK;
}

httpRequestString('okdir');
httpRequestString('faildir');
httpRequestInteger('editid');
httpRequestInteger('edittag');
httpRequestString('body');
httpRequestInteger('up');
httpRequestString('subject');
httpRequestString('comment0');
httpRequestString('comment1');
httpRequestString('ident');
httpRequestString('login');
httpRequestString('user_name');
httpRequestString('group_login');
httpRequestString('group_name');
httpRequestString('perm_string',false);
httpRequestIntegerArray('modbits');
httpRequestInteger('index2');
httpRequestIntegerArray('grps');

dbOpen();
session();
$topic=getTopicById($editid,$up);
$original=clone $topic;
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
