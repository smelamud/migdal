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
require_once('lib/images.php');
require_once('lib/postings.php');

function modifyPosting($message)
{
global $userId;

if($userId<=0)
  return EP_NO_SEND;
if(!$message->isEditable())
  return EP_NO_EDIT;
if($message->body=='')
  return EP_BODY_ABSENT;
if($message->mandatorySubject() && $message->subject=='')
  return EP_SUBJECT_ABSENT;
if($message->mandatoryTopic() && $message->topic_id==0)
  return EP_TOPIC_ABSENT;
if($message->topic_id!=0 && !topicExists($message->topic_id))
  return EP_NO_TOPIC;
if($message->mandatoryImage() && $message->image_set==0)
  return EP_IMAGE_ABSENT;
if($message->image_set!=0 && !imageSetExists($message->image_set))
  return EP_NO_IMAGE;
if($message->personal_id!=0 && !personalExists($message->personal_id))
  return EP_NO_PERSONAL;
if(!$message->store())
  return EP_STORE_SQL;
return EP_OK;
}

settype($editid,'integer');
settype($grp,'integer');
settype($HTTP_POST_VARS['grp'],'integer');

dbOpen();
session($sessionid);
$message=getPostingById($editid,$grp);
$message->setup($HTTP_POST_VARS);
$img=uploadImage('image',true,$err);
if($img)
  $message->setImageSet($img->getImageSet());
if($err==EIU_OK)
  $err=modifyPosting($message);
if($err==EP_OK)
  header("Location: $redir");
else
  {
  $bodyId=tmpTextSave($body);
  $subjectId=tmpTextSave($subject);
  header('Location: /postingedit.php?'.
          makeQuery($HTTP_POST_VARS,
	            array('body',
		          'subject'),
		    array('bodyid'    => $bodyId,
		          'subjectid' => $subjectId,
		          'image_set' => $message->getImageSet(),
		          'err'       => $err)).'#error');
  }
dbClose();
?>
