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
//require_once('lib/complains.php'); # FIXME
//require_once('lib/stotext.php');
require_once('lib/track.php');
//require_once('lib/forums.php');
require_once('lib/redirs.php');
require_once('lib/modbits.php');
require_once('lib/counters.php');
require_once('lib/logging.php');
require_once('lib/sql.php');

function isDisabledSet($posting)
{
global $userId,$userModerator;

return (getPremoderateByTopicId($posting->getTopicId()) && $userId>0
	|| $posting->getLargeFormat()==TF_HTML)
       && !$userModerator;
}

function isModerateSet($posting)
{
global $userId,$userModerator;

return (getModerateByTopicId($posting->getTopicId())
	|| $posting->getLargeFormat()==TF_HTML)
       && !$userModerator;
}

function isEditSet($posting)
{
global $userModerator;

return getEditByTopicId($posting->getTopicId()) && !$userModerator;
}

function setDisabled($posting,$original)
{
if($original->getId()==0)
  if(isDisabledSet($posting))
    setDisabledByMessageId($posting->getMessageId(),1);
$modbits=($original->getId()==0 ? $posting->getCreateModmask()
                                : $posting->getModifyModmask()) & MOD_USER;
				// FIXME MOD_USER is deprecated
if(isModerateSet($posting))
  $modbits|=MOD_MODERATE;
if($posting->getLargeFormat()==TF_HTML)
  $modbits|=MOD_HTML;
if(isEditSet($posting))
  $modbits|=MOD_EDIT;
setModbitsByMessageId($posting->getMessageId(),$modbits);
}

function modifyPosting($posting,$original)
{
global $thumbnailType;

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
if($posting->isMandatory('topic') && $posting->parent_id==0)
  return EP_TOPIC_ABSENT;
if($original->getId()==0 || $original->parent_id!=$posting->parent_id)
  if($posting->parent_id!=0)
    {
    $perms=getPermsById($posting->parent_id));
    if(!$perms)
      return EP_NO_TOPIC;
    if(!$perms->isPostable())
      return EP_TOPIC_ACCESS;
    }
  else
    {
    $perms=getRootPerms('Topic');
    if(!$perms->isPostable())
      return EP_TOPIC_ACCESS;
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
if($posting->hasSmallImage() && !imageExists($posting->id,$thumbnailType,
                                             $posting->small_image,'small'))
  return EP_NO_IMAGE;
if($posting->person_id!=0 && !personalExists($posting->person_id))
  return EP_NO_PERSON;
if($posting->up<0)
  $posting->up=0;
# converted up to here
if($posting->up!=0)
  {
  if(!messageExists($posting->up))
    return EP_NO_UP;
  if($posting->up==$posting->message_id)
    return EP_LOOP_UP;
  $perms=getPermsById('messages',$posting->up);
  if(!$perms->isAppendable())
    return EP_UP_APPEND;
  }
$posting->track='';
storePosting($posting);
updateTracks('entries',$posting->id);
setDisabled($posting,$original);
if($original->getId()==0)
  createCounters($posting->id,$posting->grp);
return EG_OK;
}

postString('okdir');
postString('faildir');

postInteger('relogin');
postString('login');
postString('password');
postInteger('remember');
postInteger('noguests');

postIdent('editid');
postInteger('edittag');
postInteger('grp');
postInteger('index1');
postInteger('index2');
postInteger('up');
postString('body');
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
postInteger('full');
postInteger('priority');
postString('ident');
postString('lang');
postInteger('image');
postInteger('del_image');
postInteger('person_id');
postInteger('hidden');
postInteger('disabled');

dbOpen();
session();
$posting=getPostingById($editid,$grp,$index1,$parent_id,
                        SELECT_GENERAL|SELECT_LARGE_BODY,$up);
$original=$posting;
$posting->setup($Args);
/*
$image=uploadImage('image',$posting->createThumbnail(),
                   $thumbnailWidth,$thumbnailHeight,$err);
if($image)
  $posting->setImageSet($image->getImageSet());
if($err==EG_OK)
  $err=uploadLargeText($posting->stotext);
if($err==EG_OK)
  if($original->getId()==0 && $relogin)
    $err=login($login,$password,$remember);
  else*/
    $err=EG_OK;
if($err==EG_OK)
  $err=modifyPosting($posting,$original);
if($err==EG_OK)
  {
  header("Location: $okdir");
  dropPostingsInfoCache(DPIC_POSTINGS);
  }
else
  {
  $bodyId=tmpTextSave($body);
  $largeBodyId=tmpTextSave($large_body);
  $subjectId=tmpTextSave($subject);
  $authorId=tmpTextSave($author);
  $sourceId=tmpTextSave($source);
  $comment0Id=tmpTextSave($comment0);
  $comment1Id=tmpTextSave($comment1);
  $titleId=tmpTextSave($title);
  $urlId=tmpTextSave($url);
  header('Location: '.
          remakeMakeURI($faildir,
			$Args,
			array('body',
			      'large_body',
			      'subject',
			      'author',
			      'source',
			      'comment0',
			      'comment1',
			      'title',
			      'url',
			      'okdir',
			      'faildir'),
			array('body_i'       => $bodyId,
			      'large_body_i' => $largeBodyId,
			      'subject_i'    => $subjectId,
			      'author_i'     => $authorId,
			      'source_i'     => $sourceId,
			      'comment0_i'   => $comment0Id,
			      'comment1_i'   => $comment1Id,
			      'title_i'      => $titleId,
			      'url_i'        => $urlId,
			      'err'          => $err)).'#error');
  }
dbClose();
?>
