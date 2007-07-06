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
require_once('lib/postings.php');
require_once('lib/forums.php');
require_once('lib/permissions.php');
require_once('lib/image-upload.php');
require_once('lib/postings-info.php');
require_once('lib/logging.php');
require_once('lib/captcha.php');

function modifyForum(&$forum,$original)
{
global $captcha,$userId,$realUserId,$thumbnailType,$forumMandatoryBody,
       $forumMandatoryImage;

if($userId<=0 && $realUserId<=0)
  return EF_NO_SEND;
if($original->getId()!=0 && !$original->isWritable())
  return EF_NO_EDIT;
if(!entryExists(ENT_NULL,$forum->getParentId()))
  return EF_NO_PARENT;
$correct=validateHierarchy($forum->parent_id,$forum->up,ENT_FORUM,$forum->id);
if($correct!=EG_OK)
  return $correct;
$parent=getPermsById($forum->getParentId());
if(!$parent->isPostable())
  return EF_NO_SEND;
if($forum->up!=$forum->parent_id)
  {
  $perms=getPermsById($forum->up);
  if(!$perms->isAppendable())
    return EF_UP_APPEND;
  }
if($forumMandatorySubject && $forum->subject=='')
  return EF_SUBJECT_ABSENT;
if($forumMandatoryAuthor && $forum->author=='')
  return EF_AUTHOR_ABSENT;
if($forumMandatoryBody && $forum->body=='')
  return EF_BODY_ABSENT;
if($forumMandatoryImage && !$forum->hasSmallImage())
  return EF_IMAGE_ABSENT;
if($forum->hasSmallImage()
   && !(imageFileExists($forum->id,$thumbnailType,$forum->small_image,'small')
	|| imageFileExists($forum->id,$forum->large_image_format,
		           $forum->small_image,'small')))
  return EF_NO_IMAGE;
if($forum->hasLargeImage()
   && !imageFileExists($forum->id,$forum->large_image_format,
                       $forum->large_image,'large'))
  return EF_NO_IMAGE;
if($forum->id<=0 && $userId<=0)
  {
  if($captcha=='')
    return EF_CAPTCHA_ABSENT;
  if(!validateCaptcha($captcha))
    return EF_CAPTCHA;
  }
storeForum($forum);
commitImages($forum,$original);
return EG_OK;
}

postString('okdir');
postString('faildir');

postInteger('relogin');
postString('login');
postString('password');
postInteger('remember');

postInteger('editid');
postInteger('edittag');
postString('captcha');
postInteger('parent_id');
postInteger('up');
postString('subject');
postString('author');
postString('body');
postInteger('small_image');
postInteger('small_image_x');
postInteger('small_image_y');
postInteger('large_image');
postInteger('large_image_x');
postInteger('large_image_y');
postInteger('large_image_size');
postString('large_image_format');
postString('large_image_filename');
postInteger('del_image');
postInteger('hidden');
postInteger('disabled');

dbOpen();
session();
$err=EG_OK;
loginHints($login,$userMyComputerHint);
if($editid<=0)
  $err=relogin($relogin,$login,$password,$remember);
$forum=getForumById($editid,$parent_id);
$original=$forum;
$forum->setup($Args);

$erru=imageUpload('image_file',$forum,IU_THUMB_AUTO|IU_MANUAL,
                  0,0,$thumbnailWidth,$thumbnailHeight,
		  0,0,0,0,$del_image);
if($err==EG_OK)
  $err=$erru;
if($err==EG_OK)
  $err=modifyForum($forum,$original);
if($err==EG_OK)
  {
  dropPostingsInfoCache(DPIC_FORUMS);
  header('Location: '.remakeURI($okdir,
                                array('offset'),
				array('tid' => $forum->getId()),
				't'.$forum->getId()));
  }
else
  {
  $bodyId=tmpTextSave($body);
  $subjectId=tmpTextSave($subject);
  $authorId=tmpTextSave($author);
  $largeImageFormatId=tmpTextSave($forum->large_image_format);
  $largeImageFilenameId=tmpTextSave($forum->large_image_filename);
  header('Location: '.
          remakeMakeURI($faildir,
			$Args,
			array('password',
			      'body',
			      'subject',
			      'author',
			      'okdir',
			      'faildir'),
			array('body_i'        => $bodyId,
			      'subject_i'     => $subjectId,
			      'author_i'      => $authorId,
			      'small_image'   => $forum->small_image,
			      'small_image_x' => $forum->small_image_x,
			      'small_image_y' => $forum->small_image_y,
			      'large_image'   => $forum->large_image,
			      'large_image_x' => $forum->large_image_x,
			      'large_image_y' => $forum->large_image_y,
			      'large_image_size' => $forum->large_image_size,
			      'large_image_format_i' => $largeImageFormatId,
			      'large_image_filename_i' => $largeImageFilenameId,
			      'err'           => $err)).'#error');
  }
dbClose();
?>
