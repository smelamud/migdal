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

function uploadLargeText(&$message)
{
global $large_file,$large_file_size,$large_file_type,$large_file_name,$large_loaded,
       $maxLargeText,$tmpDir;

if(isset($large_loaded) && $large_loaded==1)
  return EP_OK;
if(!isset($large_file) || $large_file=='' || !is_uploaded_file($large_file)
   || filesize($large_file)!=$large_file_size)
  return EP_OK;
if($large_file_size>$maxLargeText)
  return EP_LARGE_BODY_LARGE;

$large_file_tmpname=tempnam($tmpDir,'mig-');
if(!move_uploaded_file($large_file,$large_file_tmpname))
  return EP_OK;
$fd=fopen($large_file_tmpname,'r');
$message->large_filename=$large_file_name;
$message->large_body=htmlspecialchars(fread($fd,$maxLargeText),ENT_QUOTES);
fclose($fd);
unlink($large_file_tmpname);

return EP_OK;
}

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
if($message->mandatoryLargeBody() && $message->large_body=='')
  return EP_LARGE_BODY_ABSENT;
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
  $err=uploadLargeText($message);
if($err==EP_OK)
  $err=modifyPosting($message);
if($err==EP_OK)
  header("Location: $redir");
else
  {
  $bodyId=tmpTextSave($body);
  $largeBodyId=tmpTextSave($large_body);
  $subjectId=tmpTextSave($subject);
  header('Location: /postingedit.php?'.
          makeQuery($HTTP_POST_VARS,
	            array('body',
		          'large_body',
		          'subject'),
		    array('bodyid'       => $bodyId,
		          'large_bodyid' => $largeBodyId,
		          'subjectid'    => $subjectId,
		          'image_set'    => $message->getImageSet(),
		          'err'          => $err)).'#error');
  }
dbClose();
?>
