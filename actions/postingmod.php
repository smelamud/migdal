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
require_once('lib/grps.php');
require_once('lib/topics.php');
require_once('lib/image-upload.php');
require_once('lib/postings.php');
require_once('lib/postings-info.php');
require_once('lib/text-upload.php');
require_once('lib/track.php');
require_once('lib/catalog.php');
require_once('lib/redirs.php');
require_once('lib/modbits.php');
require_once('lib/counters.php');
require_once('lib/logging.php');
require_once('lib/sql.php');
require_once('lib/captcha.php');

function modifyPosting(&$posting,$original,$imageEditor,$iuFlags)
{
global $captcha,$thumbnailType,$userId;

if($original->getId()!=0 && !$original->isWritable())
  return EP_NO_EDIT;
if(!isGrpValid($posting->grp))
  return EP_INVALID_GRP;
if($posting->isMandatory('body') && $posting->body=='')
  return EP_BODY_ABSENT;
if($posting->isMandatory('lang') && $posting->lang=='')
  return EP_LANG_ABSENT;
if($posting->isMandatory('subject') && $posting->subject=='')
  return EP_SUBJECT_ABSENT;
if($posting->isMandatory('author') && $posting->author=='')
  return EP_AUTHOR_ABSENT;
if($posting->isMandatory('source') && $posting->source=='')
  return EP_SOURCE_ABSENT;
if(($posting->isMandatory('large_body')
    || $posting->isMandatory('large_body_upload'))
   && $posting->large_body=='')
  return EP_LARGE_BODY_ABSENT;
if($posting->isMandatory('url') && $posting->url=='')
  return EP_URL_ABSENT;
if($posting->url!='' && strpos($posting->url,'://')===false
   && $posting->url{0}!='/')
  $posting->url="http://{$posting->url}";
if($posting->isMandatory('topic') && $posting->parent_id==0)
  return EP_TOPIC_ABSENT;
if(getTypeByEntryId($posting->up)==ENT_TOPIC
   || $posting->up==$original->parent_id)
  $posting->up=$posting->parent_id;
$correct=validateHierarchy($posting->parent_id,$posting->up,ENT_POSTING,
                           $posting->id);
if($correct!=EG_OK)
  return $correct;
if($original->getId()==0 || $original->parent_id!=$posting->parent_id)
  {
  if($posting->parent_id!=0)
    $perms=getPermsById($posting->parent_id);
  else
    $perms=getRootPerms('Topic');
  if(!$perms->isPostable())
    return EP_TOPIC_ACCESS;
  }
if($posting->up!=0 && $posting->up!=$posting->parent_id)
  {
  $perms=getPermsById($posting->up);
  if(!$perms->isAppendable())
    return EP_UP_APPEND;
  }
if($posting->isMandatory('ident') && $posting->ident=='')
  return EP_IDENT_ABSENT;
$cid=idByIdent($posting->ident);
if($posting->ident!='' && $cid!=0 && $posting->id!=$cid)
  return EP_IDENT_UNIQUE;
if($posting->isMandatory('index1') && $posting->index1==0)
  return EP_INDEX1_ABSENT;
if($posting->isMandatory('image') && !$posting->hasSmallImage())
  return EP_IMAGE_ABSENT;
if($posting->hasSmallImage()
   && !(imageFileExists($posting->orig_id,$thumbnailType,
                        $posting->small_image,'small')
	|| imageFileExists($posting->orig_id,$posting->large_image_format,
		           $posting->small_image,'small')))
  return EP_NO_IMAGE;
if($posting->hasLargeImage()
   && !imageFileExists($posting->orig_id,$posting->large_image_format,
                       $posting->large_image,'large'))
  return EP_NO_IMAGE;
if($imageEditor['imageExactX']>0 || $imageEditor['imageExactY']>0)
  {
  if(abs($imageEditor['imageExactX']-$posting->getImageX())>1)
    {
    if($imageEditor['imageExactX']>0 && $imageEditor['imageExactY']>0)
      return EP_LARGE_IMAGE_EXACT;
    if($imageEditor['imageExactX']>0)
      return EP_LARGE_IMAGE_EXACT_X;
    }
  if(abs($imageEditor['imageExactY']-$posting->getImageY())>1)
    {
    if($imageEditor['imageExactX']>0 && $imageEditor['imageExactY']>0)
      return EP_LARGE_IMAGE_EXACT;
    if($imageEditor['imageExactY']>0)
      return EP_LARGE_IMAGE_EXACT_Y;
    }
  }
if($imageEditor['imageMaxX']>0 || $imageEditor['imageMaxY']>0)
  {
  if($imageEditor['imageMaxX']<$posting->getImageX())
    {
    if($imageEditor['imageMaxX']>0 && $imageEditor['imageMaxY']>0)
      return EP_LARGE_IMAGE_MAX;
    if($imageEditor['imageMaxX']>0)
      return EP_LARGE_IMAGE_MAX_X;
    }
  if($imageEditor['imageMaxY']<$posting->getImageY())
    {
    if($imageEditor['imageMaxX']>0 && $imageEditor['imageMaxY']>0)
      return EP_LARGE_IMAGE_MAX;
    if($imageEditor['imageMaxY']>0)
      return EP_LARGE_IMAGE_MAX_Y;
    }
  }
if(($iuFlags & IU_THUMB)!=IU_THUMB_NONE)
  {
  if($imageEditor['thumbExactX']>0 || $imageEditor['thumbExactY']>0)
    {
    if(abs($imageEditor['thumbExactX']-$posting->getSmallImageX())>1)
      {
      if($imageEditor['thumbExactX']>0 && $imageEditor['thumbExactY']>0)
	return EP_SMALL_IMAGE_EXACT;
      if($imageEditor['thumbExactX']>0)
	return EP_SMALL_IMAGE_EXACT_X;
      }
    if(abs($imageEditor['thumbExactY']-$posting->getSmallImageY())>1)
      {
      if($imageEditor['thumbExactX']>0 && $imageEditor['thumbExactY']>0)
	return EP_SMALL_IMAGE_EXACT;
      if($imageEditor['thumbExactY']>0)
	return EP_SMALL_IMAGE_EXACT_Y;
      }
    }
  if($imageEditor['thumbMaxX']>0 || $imageEditor['thumbMaxY']>0)
    {
    if($imageEditor['thumbMaxX']<$posting->getSmallImageX())
      {
      if($imageEditor['thumbMaxX']>0 && $imageEditor['thumbMaxY']>0)
	return EP_SMALL_IMAGE_MAX;
      if($imageEditor['thumbMaxX']>0)
	return EP_SMALL_IMAGE_MAX_X;
      }
    if($imageEditor['thumbMaxY']<$posting->getSmallImageY())
      {
      if($imageEditor['thumbMaxX']>0 && $imageEditor['thumbMaxY']>0)
	return EP_SMALL_IMAGE_MAX;
      if($imageEditor['thumbMaxY']>0)
	return EP_SMALL_IMAGE_MAX_Y;
      }
    }
  }
if($posting->person_id!=0 && !personalExists($posting->person_id))
  return EP_NO_PERSON;
if($posting->id<=0 && $userId<=0)
  {
  if($captcha=='')
    return EP_CAPTCHA_ABSENT;
  if(!validateCaptcha($captcha))
    return EP_CAPTCHA;
  }
$posting->track='';
$posting->catalog='';
storePosting($posting);
commitImages($posting,$original);
setPremoderates($posting,$original);
if($original->getId()==0)
  createCounters($posting->id,$posting->grp);
return EG_OK;
}

postString('okdir');
postString('faildir');

postInteger('full');
postInteger('relogin');
postString('login');
postString('password');
postInteger('remember');
postInteger('noguests');

postIdent('editid');
postInteger('edittag');
postString('captcha');
postInteger('grp');
postInteger('index1');
postInteger('index2');
postInteger('up');
postString('body');
postInteger('body_format');
postString('large_body');
postInteger('del_large_body');
postInteger('large_body_format');
postString('subject');
postString('author');
postString('source');
postString('comment0');
postString('comment1');
postString('title');
postString('url');
postIdent('parent_id');
postInteger('priority');
postString('ident');
postString('lang');
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
postInteger('person_id');
postInteger('hidden');
postInteger('disabled');
postInteger('sent');
postInteger('sent_year');
postInteger('sent_month');
postInteger('sent_day');
postInteger('sent_hour');
postInteger('sent_minute');
postInteger('sent_second');

dbOpen();
session();
$err=EG_OK;
loginHints($login,$userMyComputerHint);
if($editid<=0)
  $err=relogin($relogin,$login,$password,$remember);
$posting=getPostingById($editid,$grp,$parent_id,
                        SELECT_GENERAL|SELECT_LARGE_BODY,$up);
$original=$posting;
$posting->setup($Args);

$imageEditor=$posting->getGrpImageEditor();
$iuFlags=imageUploadFlags($imageEditor['style']);
$erru=imageUpload('image_file',$posting,$iuFlags,
                  $imageEditor['thumbExactX'],$imageEditor['thumbExactY'],
                  $imageEditor['thumbMaxX'],$imageEditor['thumbMaxY'],
                  $imageEditor['imageExactX'],$imageEditor['imageExactY'],
                  $imageEditor['imageMaxX'],$imageEditor['imageMaxY'],
		  $del_image);
if($err==EG_OK)
  $err=$erru;
$erru=uploadLargeBody($posting,$del_large_body);
if($err==EG_OK)
  $err=$erru;
if($err==EG_OK)
  $err=modifyPosting($posting,$original,$imageEditor,$iuFlags);
if($err==EG_OK)
  {
  if($posting->isDisabled() && ($userId<=0 || $userWillBeModeratedNote))
    header('Location: '.
           remakeURI('/will-be-moderated/',
	             array(),
		     array('okdir_i' => tmpTextSave($okdir))));
  else
    header("Location: $okdir");
  dropPostingsInfoCache(DPIC_POSTINGS);
  }
else
  {
  $bodyId=tmpTextSave($body);
  $largeBodyId=tmpTextSave($posting->large_body);
  $largeBodyFilenameId=tmpTextSave($posting->large_body_filename);
  $subjectId=tmpTextSave($subject);
  $authorId=tmpTextSave($author);
  $sourceId=tmpTextSave($source);
  $comment0Id=tmpTextSave($comment0);
  $comment1Id=tmpTextSave($comment1);
  $titleId=tmpTextSave($title);
  $urlId=tmpTextSave($url);
  $largeImageFormatId=tmpTextSave($posting->large_image_format);
  $largeImageFilenameId=tmpTextSave($posting->large_image_filename);
  header('Location: '.
          remakeMakeURI($faildir,
			$Args,
			array('password',
			      'body',
			      'large_body',
			      'subject',
			      'author',
			      'source',
			      'comment0',
			      'comment1',
			      'title',
			      'url',
			      'sent_year',
			      'sent_month',
			      'sent_day',
			      'sent_hour',
			      'sent_minute',
			      'sent_second',
			      'okdir',
			      'faildir'),
			array('body_i'        => $bodyId,
			      'large_body_i'  => $largeBodyId,
			      'large_body_filename_i' => $largeBodyFilenameId,
			      'subject_i'     => $subjectId,
			      'author_i'      => $authorId,
			      'source_i'      => $sourceId,
			      'comment0_i'    => $comment0Id,
			      'comment1_i'    => $comment1Id,
			      'title_i'       => $titleId,
			      'url_i'         => $urlId,
			      'small_image'   => $posting->small_image,
			      'small_image_x' => $posting->small_image_x,
			      'small_image_y' => $posting->small_image_y,
			      'large_image'   => $posting->large_image,
			      'large_image_x' => $posting->large_image_x,
			      'large_image_y' => $posting->large_image_y,
			      'large_image_size' => $posting->large_image_size,
			      'large_image_format_i' => $largeImageFormatId,
			      'large_image_filename_i' => $largeImageFilenameId,
			      'sent'          => $posting->getSent(),
			      'err'           => $err)).'#error');
  }
dbClose();
?>
