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
require_once('lib/complains.php');
require_once('lib/stotext.php');
require_once('lib/track.php');
require_once('lib/forums.php');
require_once('lib/redirs.php');
require_once('lib/modbits.php');
require_once('lib/counters.php');
require_once('lib/logging.php');
require_once('lib/sql.php');

function setImageTitle($image_set,$title)
{
if(!$image_set)
  return EG_OK;
sql("update images
     set title='$title'
     where image_set=$image_set",
    'setImageTitle');
journal("update images
         set title='".jencode($title)."'
	 where image_set=".journalVar('images',$image_set));
return EG_OK;
}

function isDisabledSet($message)
{
global $userId,$userModerator;

return (getPremoderateByTopicId($message->getTopicId()) && $userId>0
	|| $message->getLargeFormat()==TF_HTML)
       && !$userModerator;
}

function isModerateSet($message)
{
global $userId,$userModerator;

return (getModerateByTopicId($message->getTopicId())
	|| $message->getLargeFormat()==TF_HTML)
       && !$userModerator;
}

function isEditSet($message)
{
global $userModerator;

return getEditByTopicId($message->getTopicId()) && !$userModerator;
}

function setDisabled($message,$original)
{
if($original->getId()==0)
  if(isDisabledSet($message))
    setDisabledByMessageId($message->getMessageId(),1);
$modbits=($original->getId()==0 ? $message->getCreateModmask()
                                : $message->getModifyModmask()) & MOD_USER;
				// FIXME MOD_USER is deprecated
if(isModerateSet($message))
  $modbits|=MOD_MODERATE;
if($message->getLargeFormat()==TF_HTML)
  $modbits|=MOD_HTML;
if(isEditSet($message))
  $modbits|=MOD_EDIT;
setModbitsByMessageId($message->getMessageId(),$modbits);
}

function modifyPosting($message,$original)
{
if($original->getId()!=0 && !$original->isWritable())
  return EP_NO_EDIT;
if(!getGrpValid($message->grp))
  return EP_INVALID_GRP;
if($message->mandatoryBody() && $message->stotext->body=='')
  return EP_BODY_ABSENT;
if($message->mandatoryLang() && $message->lang=='')
  return EP_LANG_ABSENT;
if($message->mandatorySubject() && $message->subject=='')
  return EP_SUBJECT_ABSENT;
if($message->mandatoryAuthor() && $message->author=='')
  return EP_AUTHOR_ABSENT;
if($message->mandatorySource() && $message->source=='')
  return EP_SOURCE_ABSENT;
if($message->mandatoryLargeBody() && $message->stotext->large_body=='')
  return EP_LARGE_BODY_ABSENT;
if($message->mandatoryURL() && $message->url=='')
  return EP_URL_ABSENT;
if($message->mandatoryTopic() && $message->topic_id==0)
  return EP_TOPIC_ABSENT;
if($original->getId()==0 || $original->topic_id!=$message->topic_id)
  if($message->topic_id!=0)
    {
    $perms=getPermsById('topics',addslashes($message->topic_id));
    if(!$perms)
      return EP_NO_TOPIC;
    if(!$perms->isPostable())
      return EP_TOPIC_ACCESS;
    }
  else
    {
    $perms=getRootPerms('topics');
    if(!$perms->isPostable())
      return EP_TOPIC_ACCESS;
    }
if($message->mandatoryIdent() && $message->ident=='')
  return EP_IDENT_ABSENT;
$cid=idByIdent('postings',$message->ident);
if($message->ident!='' && $cid!=0 && $message->id!=$cid)
  return EP_IDENT_UNIQUE;
if($message->mandatoryIndex1() && $message->index1==0)
  return EP_INDEX1_ABSENT;
if($message->mandatoryImage() && $message->stotext->image_set==0)
  return EP_IMAGE_ABSENT;
if($message->stotext->image_set!=0
   && !imageSetExists($message->stotext->image_set))
  return EP_NO_IMAGE;
if($message->personal_id!=0 && !personalExists($message->personal_id))
  return EP_NO_PERSONAL;
if($message->up<0)
  $message->up=0;
if($message->up!=0)
  {
  if(!messageExists($message->up))
    return EP_NO_UP;
  if($message->up==$message->message_id)
    return EP_LOOP_UP;
  $perms=getPermsById('messages',$message->up);
  if(!$perms->isAppendable())
    return EP_UP_APPEND;
  }
$message->track='';
$message->store();
updateTracks('messages',$message->message_id);
setDisabled($message,$original);
if($original->getId()==0)
  createCounters($message->message_id,$message->grp);
return EG_OK;
}

postInteger('relogin');
postString('login');
postString('password');
postInteger('remember');

postInteger('editid');
postInteger('grp');
postInteger('index1');
postInteger('index2');
postInteger('up');
postString('body');
postString('large_body');
postString('subject');
postString('author');
postString('source');
postString('comment0');
postString('comment1');
postString('title');
postString('url');
postIdent('topic_id','topics');

dbOpen();
session();
$message=getPostingById($editid,$grp,$index1,$topic_id,SELECT_TOPICS,$up);
$original=$message;
$message->setup($HTTP_POST_VARS);
$image=uploadImage('image',$message->createThumbnail(),
                   $thumbnailWidth,$thumbnailHeight,$err);
if($image)
  $message->setImageSet($image->getImageSet());
if($err==EG_OK && $message->getImageSet()!=0)
  $err=setImageTitle($message->getImageSet(),
                     addslashes(htmlspecialchars($title,ENT_QUOTES)));
if($err==EG_OK)
  $err=uploadLargeText($message->stotext);
if($err==EG_OK)
  if($original->getId()==0 && $relogin)
    $err=login($login,$password,$remember);
  else
    $err=EG_OK;
if($err==EG_OK)
  $err=modifyPosting($message,$original);
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
			$HTTP_POST_VARS,
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
			array('bodyid'       => $bodyId,
			      'large_bodyid' => $largeBodyId,
			      'subjectid'    => $subjectId,
			      'authorid'     => $authorId,
			      'sourceid'     => $sourceId,
			      'comment0id'   => $comment0Id,
			      'comment1id'   => $comment1Id,
			      'titleid'      => $titleId,
			      'urlid'        => $urlId,
			      'image_set'    => $message->getImageSet(),
			      'err'          => $err)).'#error');
  }
dbClose();
?>
