<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/topics.php');
require_once('lib/utils.php');
require_once('lib/errors.php');
require_once('lib/tmptexts.php');

function setTrack($topic)
{
$tr=getTopicTrackById($topic->up);
$add=sprintf('%010u',$topic->id);
$track=$tr!='' ? "$tr $add" : $add;
return mysql_query("update topics
                    set track='".$track."'
	            where id=".$topic->id);
}

function modifyTopic($topic)
{
global $userAdminTopics;

if(!$userAdminTopics)
  return ET_NO_EDIT;
if($topic->name=='')
  return ET_NAME_ABSENT;
if($topic->description=='')
  return ET_DESCRIPTION_ABSENT;
if($topic->up!=0 && !topicExists($topic->up))
  return ET_NO_UP;
if($topic->up!=0 && $topic->up==$topic->id)
  return ET_LOOP_UP;
$cid=idByIdent('topics',$topic->ident);
if($topic->ident!='' && $cid!=0 && $topic->id!=$cid)
  return ET_IDENT_UNIQUE;
if(!$topic->store())
  return ET_STORE_SQL;
if(!setTrack($topic))
  return ET_TRACK_SQL;
return ET_OK;
}

settype($editid,'integer');

dbOpen();
session($sessionid);
$topic=getTopicById($editid);
$topic->setup($HTTP_POST_VARS);
$err=modifyTopic($topic);
if($err==ET_OK)
  header("Location: $redir");
else
  {
  $descriptionId=tmpTextSave($description);
  header('Location: /topicedit.php?'.
          makeQuery($HTTP_POST_VARS,
	            array('description'),
		    array('descriptionid' => $descriptionId,
		          'err'           => $err)).'#error');
  }
dbClose();
?>
