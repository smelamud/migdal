<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/utils.php');
require_once('lib/errors.php');
require_once('lib/tmptexts.php');
require_once('lib/forums.php');
require_once('lib/messages.php');
require_once('lib/images.php');

function modifyForumAnswer($answer)
{
global $userId;

if($userId<=0)
  return EFA_NO_SEND;
if(!$answer->isEditable())
  return EFA_NO_EDIT;
if($answer->body=='')
  return EFA_BODY_ABSENT;
if($answer->mandatoryImage() && $answer->image_set==0)
  return EFA_IMAGE_ABSENT;
if($answer->image_set!=0 && !imageSetExists($answer->image_set))
  return EFA_NO_IMAGE;
if(!messageExists($answer->up))
  return EFA_NO_UP;
if(!$answer->store())
  return EFA_STORE_SQL;
return EFA_OK;
}

settype($editid,'integer');

dbOpen();
session($sessionid);
$answer=getForumAnswerById($editid);
$answer->setup($HTTP_POST_VARS);
$img=uploadImage('image',true,$err);
if($img)
  $answer->setImageSet($img->getImageSet());
if($err==EIU_OK)
  $err=modifyForumAnswer($answer);
if($err==EFA_OK)
  header("Location: $redir");
else
  {
  $bodyId=tmpTextSave($body);
  $subjectId=tmpTextSave($subject);
  header('Location: /forumansweredit.php?'.
          makeQuery($HTTP_POST_VARS,
	            array('body',
		          'subject'),
		    array('bodyid'       => $bodyId,
		          'subjectid'    => $subjectId,
		          'image_set'    => $answer->getImageSet(),
		          'err'          => $err)).'#error');
  }
dbClose();
?>
