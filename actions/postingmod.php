<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/utils.php');
require_once('lib/errors.php');
require_once('lib/tmptexts.php');
require_once('lib/grps.php');
require_once('lib/topics.php');
require_once('lib/image-upload.php');
require_once('lib/postings.php');
require_once('lib/complains.php');
require_once('lib/stotext.php');

function setImageTitle($image_set,$title)
{
if(!$image_set)
  return EP_OK;
$result=mysql_query("update images
                     set title='$title'
		     where image_set=$image_set");
return $result ? EP_OK : EP_TITLE_SQL;
}

function isPremoderated($message)
{
global $userModerator;

return (getPremoderateByTopicId($message->getTopicId())
        & $message->getGrp())!=0 && !$userModerator;
}

function setDisabled($message)
{
if(isPremoderated($message))
  {
  $result=mysql_query('update messages
                       set disabled=1
	               where id='.$message->getMessageId());
  if($result)
    sendAutomaticComplain('posting',
                          'Автоматическая проверка сообщения "'.
			   $message->getSubject().'"',
			  'Прошу Модератора проверить соответствие данного
			   сообщения политике сайта и открыть к нему публичный
			   доступ.',
			  $message->getId());
  return $result;
  }
else
  return true;
}

function modifyPosting($message)
{
global $userId;

if($userId<=0)
  return EP_NO_SEND;
if(!$message->isEditable())
  return EP_NO_EDIT;
if($message->stotext->body=='')
  return EP_BODY_ABSENT;
if($message->mandatorySubject() && $message->subject=='')
  return EP_SUBJECT_ABSENT;
if($message->mandatoryLargeBody() && $message->stotext->large_body=='')
  return EP_LARGE_BODY_ABSENT;
if($message->mandatoryTopic() && $message->topic_id==0)
  return EP_TOPIC_ABSENT;
if($message->topic_id!=0 && !topicExists($message->topic_id))
  return EP_NO_TOPIC;
if($message->mandatoryImage() && $message->stotext->image_set==0)
  return EP_IMAGE_ABSENT;
if($message->stotext->image_set!=0
   && !imageSetExists($message->stotext->image_set))
  return EP_NO_IMAGE;
if($message->personal_id!=0 && !personalExists($message->personal_id))
  return EP_NO_PERSONAL;
if(!$message->store())
  return EP_STORE_SQL;
if(!setDisabled($message))
  return EP_DISABLE_SQL;
return EP_OK;
}

settype($editid,'integer');
settype($grp,'integer');
settype($HTTP_POST_VARS['grp'],'integer');
$title=addslashes($title);

dbOpen();
session($sessionid);
$message=getPostingById($editid,$grp);
$message->setup($HTTP_POST_VARS);
$img=uploadImage('image',true,$thumbnailWidth,$thumbnailHeight,$err);
if($img)
  $message->setImageSet($img->getImageSet());
if($err==EIU_OK && $message->getImageSet()!=0)
  $err=setImageTitle($message->getImageSet(),$title);
if($err==EIU_OK || $err==EP_OK)
  $err=uploadLargeText($message->getStotext());
if($err==EP_OK)
  $err=modifyPosting($message);
if($err==EP_OK)
  header("Location: $redir");
else
  {
  $bodyId=tmpTextSave($body);
  $largeBodyId=tmpTextSave($large_body);
  $subjectId=tmpTextSave($subject);
  $titleId=tmpTextSave($title);
  header('Location: /postingedit.php?'.
          makeQuery($HTTP_POST_VARS,
	            array('body',
		          'large_body',
		          'subject',
			  'title'),
		    array('bodyid'       => $bodyId,
		          'large_bodyid' => $largeBodyId,
		          'subjectid'    => $subjectId,
		          'titleid'      => $titleId,
		          'image_set'    => $message->getImageSet(),
		          'err'          => $err)).'#error');
  }
dbClose();
?>
