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
require_once('lib/complains.php');
require_once('lib/stotext.php');
require_once('lib/track.php');
require_once('lib/forums.php');
require_once('lib/redirs.php');

function setImageTitle($image_set,$title)
{
if(!$image_set)
  return EP_OK;
$result=mysql_query("update images
                     set title='$title'
		     where image_set=$image_set");
journal("update images
         set title='".jencode($title)."'
	 where image_set=".journalVar('images',$image_set));
return $result ? EP_OK : EP_TITLE_SQL;
}

function isPremoderated($message)
{
global $userId,$userModerator;

return (((int)getPremoderateByTopicId($message->getTopicId())
         & (int)$message->getGrp())!=0 || $message->getLargeFormat()==TF_HTML
	|| $userId<=0) && !$userModerator;
}

function setDisabled($message)
{
if(!isPremoderated($message))
  return true;
$complain=getComplainInfoByLink(COMPL_AUTO_POSTING,$message->getId());
if($complain->getId()==0)
  {
  $result=mysql_query('update messages
		       set disabled=1
		       where id='.$message->getMessageId());
  if(!$result)
    return false;
  journal('update messages
	   set disabled=1
	   where id='.journalVar('messages',$message->getMessageId()));
  sendAutomaticComplain(COMPL_AUTO_POSTING,
			'Автоматическая проверка сообщения "'.
			 $message->getSubjectDesc().'"',
			$message->getLargeFormat()!=TF_HTML
			 ? ''
			 : '~Внимание!~ Этот текст в формате HTML.',
			$message->getId(),
			$message->getLargeFormat()==TF_HTML);
  }
else
  {
  if($complain->isClosed())
    reopenComplain($complain->getId(),true);
  if(!postForumAnswer($complain->getMessageId(),
		      'Сообщение было изменено.',
		      getShamesId()))
    return false;
  }
return true;
}

function modifyPosting($message)
{
global $userModerator;

if(!$message->isEditable())
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
if($message->up!=0 && !messageExists($message->up))
  return EP_NO_UP;
if($message->up!=0 && $message->up==$message->message_id)
  return EP_LOOP_UP;
$message->track='';
if(!$message->store())
  return EP_STORE_SQL;
if(!updateTracks('messages',$message->message_id))
  return EP_TRACK_SQL;
if(!setDisabled($message))
  return EP_DISABLE_SQL;
return EP_OK;
}

postInteger('editid');
postInteger('grp');
postInteger('index1');
postInteger('up');
postString('body');
postString('large_body');
postString('subject');
postString('author');
postString('source');
postString('title');
postString('url');
postIdent('topic_id','topics');

dbOpen();
session();
redirect();
$message=getPostingById($editid,$grp);
$message->setup($HTTP_POST_VARS);
$image=uploadImage('image',true,$thumbnailWidth,$thumbnailHeight,$err);
if($image)
  $message->setImageSet($image->getImageSet());
if($err==EIU_OK && $message->getImageSet()!=0)
  $err=setImageTitle($message->getImageSet(),
                     addslashes(htmlspecialchars($title,ENT_QUOTES)));
if($err==EIU_OK || $err==EP_OK)
  $err=uploadLargeText($message->stotext);
if($err==EUL_OK)
  $err=modifyPosting($message);
if($err==EP_OK)
  header("Location: $okdir");
else
  {
  $bodyId=tmpTextSave($body);
  $largeBodyId=tmpTextSave($large_body);
  $subjectId=tmpTextSave($subject);
  $authorId=tmpTextSave($author);
  $sourceId=tmpTextSave($source);
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
			      'title',
			      'url',
			      'okdir',
			      'faildir'),
			array('bodyid'       => $bodyId,
			      'large_bodyid' => $largeBodyId,
			      'subjectid'    => $subjectId,
			      'authorid'     => $authorId,
			      'sourceid'     => $sourceId,
			      'titleid'      => $titleId,
			      'urlid'        => $urlId,
			      'image_set'    => $message->getImageSet(),
			      'err'          => $err)).'#error');
  }
dbClose();
?>
