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
require_once('lib/redirs.php');
require_once('lib/modbits.php');
require_once('lib/counters.php');
require_once('lib/logging.php');
require_once('lib/sql.php');

function isModbitRequired($modbits,$bit,$posting,$original)
{
global $userId,$userModerator;

$required=($modbits & $bit)!=0;
switch($bit)
      {
      case MODT_PREMODERATE:
	   $required=$original->getId()==0 && $required && $userId>0
	             && !$userModerator;
           break;
      case MODT_MODERATE:
	   $required=$required && !$userModerator;
           break;
      case MODT_EDIT:
	   $required=$required && !$userModerator;
           break;
      }
return $required;
}

function setPremoderates($posting,$original)
{
$tmod=getModbitsByTopicId($posting->getParentId());
if(isModbitRequired($tmod,MODT_PREMODERATE,$posting,$original))
  setDisabledByEntryId($posting->getId(),1);
$modbits=MOD_NONE;
if(isModbitRequired($tmod,MODT_MODERATE,$posting,$original))
  $modbits|=MOD_MODERATE;
if(isModbitRequired($tmod,MODT_EDIT,$posting,$original))
  $modbits|=MOD_EDIT;
setModbitsByEntryId($posting->getId(),$modbits);
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
if($posting->hasSmallImage() && !imageExists($posting->id,$thumbnailType,
                                             $posting->small_image,'small'))
  return EP_NO_IMAGE;
if($posting->hasLargeImage() && !imageExists($posting->id,
                                             $posting->large_image_format,
                                             $posting->large_image,'large'))
  return EP_NO_IMAGE;
if($posting->person_id!=0 && !personalExists($posting->person_id))
  return EP_NO_PERSON;
$posting->track='';
storePosting($posting);
updateTracks('entries',$posting->id);
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
$err=EG_OK;
if($editid<=0)
  $err=relogin($relogin,$login,$password,$remember);
$posting=getPostingById($editid,$grp,$index1,$parent_id,
                        SELECT_GENERAL|SELECT_LARGE_BODY,$up);
$original=$posting;
$posting->setup($Args);

if($original->getId()==0 || $original->isWritable())
  {
  $erru=uploadImage('image_file',$posting,$thumbnailWidth,$thumbnailHeight,
                    $del_image);
  if($err==EG_OK)
    $err=$erru;
  $erru=uploadLargeBody($posting,$del_large_body);
  if($err==EG_OK)
    $err=$erru;
  }
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
  $largeBodyId=tmpTextSave($posting->large_body);
  $largeBodyFilenameId=tmpTextSave($posting->large_body_filename);
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
			      'okdir',
			      'faildir'),
			array('body_i'       => $bodyId,
			      'large_body_i' => $largeBodyId,
			      'large_body_filename_i' => $largeBodyFilenameId,
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
