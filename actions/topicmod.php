<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/topics.php');
require_once('lib/utils.php');
require_once('lib/errors.php');
require_once('lib/tmptexts.php');
require_once('lib/track.php');

function getTrack($topic)
{
$tr=trackById('topics',$topic->up);
$add=track($topic->id);
return $tr!='' ? "$tr $add" : $add;
}

function storeTrack($topic)
{
return mysql_query("update topics
                    set track='".$topic->track."'
		    where id=".$topic->id);
}

function modifyTopic($topic)
{
global $userAdminTopics;

if(!$userAdminTopics)
  return ET_NO_EDIT;
if($topic->name=='')
  return ET_NAME_ABSENT;
if($topic->stotext->body=='')
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
$topic->track=getTrack($topic);
if(!storeTrack($topic))
  return ET_TRACK_SQL;
return ET_OK;
}

postInteger('editid');
postInteger('up');
postString('name');
postString('description');
postString('large_description');

dbOpen();
session($sessionid);
$topic=getTopicById($editid,$up);
$topic->setup($HTTP_POST_VARS);
$err=uploadLargeText($topic->stotext);
if($err==EUL_OK)
  $err=modifyTopic($topic);
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
			      'large_description'),
			array('descriptionid'       => $descriptionId,
			      'large_descriptionid' => $largeDescriptionId,
			      'err'                 => $err)).'#error');
  }
dbClose();
?>
