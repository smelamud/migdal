<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/utils.php');
require_once('lib/errors.php');
require_once('lib/tmptexts.php');
require_once('lib/forums.php');
require_once('lib/messages.php');
require_once('lib/image-upload.php');

function modifyForumAnswer($answer)
{
global $userId,$forumanswerMandatoryBody,$forumanswerMandatoryImage;

if($userId<=0)
  return EFA_NO_SEND;
if(!$answer->isEditable())
  return EFA_NO_EDIT;
if($forumanswerMandatoryBody && $answer->stotext->body=='')
  return EFA_BODY_ABSENT;
if($forumanswerMandatoryImage && $answer->stotext->image_set==0)
  return EFA_IMAGE_ABSENT;
if($answer->stotext->image_set!=0
   && !imageSetExists($answer->stotext->image_set))
  return EFA_NO_IMAGE;
if(!messageExists($answer->parent_id))
  return EFA_NO_UP;
if(!$answer->store())
  return EFA_STORE_SQL;
return EFA_OK;
}

postInteger('editid');
postInteger('parent_id');
postString('body');
postString('subject');

dbOpen();
session();
$answer=getForumAnswerById($editid);
$answer->setup($HTTP_POST_VARS);
$img=uploadImage('image',true,$thumbnailWidth,$thumbnailHeight,$err);
if($img)
  $answer->setImageSet($img->getImageSet());
if($err==EIU_OK)
  $err=modifyForumAnswer($answer);
if($err==EFA_OK)
  header("Location: $okdir");
else
  {
  $bodyId=tmpTextSave($body);
  $subjectId=tmpTextSave($subject);
  header('Location: '.
          remakeMakeURI($faildir,
			$HTTP_POST_VARS,
			array('body','subject','okdir','faildir'),
			array('bodyid'       => $bodyId,
			      'subjectid'    => $subjectId,
			      'image_set'    => $answer->getImageSet(),
			      'err'          => $err)).'#error');
  }
dbClose();
?>
