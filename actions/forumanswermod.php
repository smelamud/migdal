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
/* Required to prevent inclusion of Posting class before Message */
require_once('lib/postings.php');
require_once('lib/forums.php');
require_once('lib/messages.php');
require_once('lib/permissions.php');
require_once('lib/image-upload.php');
require_once('lib/postings-info.php');
require_once('lib/answers.php');

function modifyForumAnswer($answer,$original)
{
global $userId,$realUserId,$forumanswerMandatoryBody,$forumanswerMandatoryImage;

if($userId<=0 && $realUserId<=0)
  return EFA_NO_SEND;
if($original->getId()!=0 && !$original->isWritable())
  return EFA_NO_EDIT;
if(!messageExists($answer->getParentId()))
  return EFA_NO_UP;
$upper=getPermsById('messages',$answer->getParentId());
if(!$upper->isPostable())
  return EFA_NO_SEND;
if($forumanswerMandatoryBody && $answer->stotext->body=='')
  return EFA_BODY_ABSENT;
if($forumanswerMandatoryImage && $answer->stotext->image_set==0)
  return EFA_IMAGE_ABSENT;
if($answer->stotext->image_set!=0
   && !imageSetExists($answer->stotext->image_set))
  return EFA_NO_IMAGE;
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
$answer=getForumAnswerById($editid,$parent_id);
$original=$answer;
$answer->setup($HTTP_POST_VARS);
$img=uploadImage('image',true,$thumbnailWidth,$thumbnailHeight,$err);
if($img)
  $answer->setImageSet($img->getImageSet());
if($err==EIU_OK)
  $err=modifyForumAnswer($answer,$original);
if($err==EFA_OK)
  {
  dropPostingsInfoCache(DPIC_FORUMS);
  answerUpdate($answer->getParentId());
  header("Location: $okdir");
  }
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
