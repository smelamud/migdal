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
require_once('lib/messages.php');

function uploadImage(&$message)
{
global $maxImage,$thumbnailType,$thumbnailWidth,$thumbnailHeight;
global $userId,$image,$image_name,$image_size,$image_type;

if(!isset($image) || $image=='' || !is_uploaded_file($image)
   || filesize($image)!=$image_size)
  return EM_OK;
if($image_size>$maxImage)
  return EM_IMAGE_LARGE;
$largeExt=getImageExtension($image_type);
$smallExt=getImageExtension($thumbnailType);
if($largeExt=='')
  return EM_UNKNOWN_IMAGE;

srand(time());
$hash=rand();
$largeFile="/tmp/mig-$hash.$largeExt";
$smallFile="/tmp/mig-$hash.$smallExt";

if(!move_uploaded_file($image,$largeFile))
  return EM_OK;

$large_size=getImageSize($largeFile);
$fd=fopen($largeFile,'r');
$large=fread($fd,$maxImage);
fclose($fd);

$geometry=$thumbnailWidth.'x'.$thumbnailHeight;
exec("mogrify -format $smallExt -geometry '$geometry>' $largeFile");

$small_size=getImageSize($smallFile);
$fd=fopen($smallFile,'r');
$small=fread($fd,$maxImage);
fclose($fd);

$img=new Image(array('filename' => $image_name,
		     'small'    => $small,
		     'small_x'  => $small_size[0],
		     'small_y'  => $small_size[1],
		     'large'    => $large,
		     'large_x'  => $large_size[0],
		     'large_y'  => $large_size[1],
		     'format'   => $image_type));
if(!$img->store())
  return EM_IMAGE_SQL;
$message->setImageSet($img->getImageSet());
return EM_OK;
}

function modifyMessage($message)
{
global $userId;

if($userId<=0)
  return EM_NO_SEND;
if(!$message->isEditable())
  return EM_NO_EDIT;
if($message->body=='')
  return EM_BODY_ABSENT;
if($message->mandatorySubject() && $message->subject=='')
  return EM_SUBJECT_ABSENT;
if($message->mandatoryTopic() && $message->topic_id==0)
  return EM_TOPIC_ABSENT;
if($message->topic_id!=0 && !topicExists($message->topic_id))
  return EM_NO_TOPIC;
if($message->mandatoryImage() && $message->image_set==0)
  return EM_IMAGE_ABSENT;
if($message->image_set!=0 && !imageSetExists($message->image_set))
  return EM_NO_IMAGE;
if($message->personal_id!=0 && !personalExists($message->personal_id))
  return EM_NO_PERSONAL;
if($message->up!=0 && $message->grp!=GRP_FORUMS)
  return EM_FORUM_ANSWER;
if($message->up!=0 && !messageExists($message->up))
  return EM_NO_UP;
if(!$message->store())
  return EM_STORE_SQL;
return EM_OK;
}

dbOpen();
session($sessionid);
$message=getMessageById($editid,$grp);
$message->setup($HTTP_POST_VARS);
$err=uploadImage($message);
if($err==EM_OK)
  $err=modifyMessage($message);
if($err==EM_OK)
  header("Location: $redir");
else
  {
  $bodyId=tmpTextSave($body);
  $subjectId=tmpTextSave($subject);
  header('Location: /messageedit.php?'.
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
