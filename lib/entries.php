<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/usertag.php');
require_once('lib/mtext-shorten.php');
require_once('lib/image-types.php');
require_once('lib/track.php');
require_once('lib/answers.php');
require_once('lib/langs.php');
require_once('lib/text-large.php');

define('ENT_NULL',0);
define('ENT_POSTING',1);
define('ENT_FORUM',2);
define('ENT_TOPIC',3);
define('ENT_IMAGE',4);
define('ENT_COMPLAIN',5);
define('ENT_VERSION',6);

class Entry
      extends UserTag
{
var $id=0;
var $ident=null;
var $entry=ENT_NULL;
var $up=0;
var $track='';
var $catalog='';
var $parent_id=0;
var $orig_id=0;
var $grp=0;
var $grps=array();
var $person_id=0;
var $user_id=0;
var $group_id=0;
var $group_login='';
var $perms=PERM_NONE;
var $perm_string='';
var $disabled=0;
var $subject='';
var $subject_sort='';
var $lang='';
var $author='';
var $author_xml='';
var $source='';
var $source_xml='';
var $title='';
var $title_xml='';
var $comment0='';
var $comment0_xml='';
var $comment1='';
var $comment1_xml='';
var $url='';
var $url_domain='';
var $url_check=0;
var $url_check_success=0;
var $body='';
var $body_xml='';
var $body_format=TF_MAIL;
var $has_large_body=0;
var $large_body='';
var $large_body_xml='';
var $large_body_format=TF_PLAIN;
var $large_body_filename='';
var $priority=0;
var $index0=0;
var $index1=0;
var $index2=0;
var $vote=0;
var $vote_count=0;
var $rating=0;
var $sent=0;
var $created=0;
var $modified=0;
var $accessed=0;
var $modbits=MOD_NONE;
var $answers=0;
var $last_answer=0;
var $last_answer_id=0;
var $last_answer_user_id=0;
var $small_image=0;
var $small_image_x=0;
var $small_image_y=0;
var $large_image=0;
var $large_image_x=0;
var $large_image_y=0;
var $large_image_size=0;
var $large_image_format='';
var $large_image_filename='';

function Entry($row)
{
parent::UserTag($row);
}

function getId()
{
return $this->id;
}

function getIdent()
{
return $this->ident;
}

function getEntry()
{
return $this->entry;
}

function getUpValue()
{
return $this->up;
}

function getTrack()
{
return $this->track;
}

function getCatalog($start=0,$length=0)
{
if($start==0 && $length==0 || $this->catalog=='')
  return $this->catalog;
$elements=explode('/',substr($this->catalog,0,-1));
$catalog='';
$begin=$start>=0 ? $start : count($elements)+$start;
$end=$length>0 ? $begin+$length : count($elements)+$length;
for($i=$begin;$i<$end;$i++)
   $catalog.=$elements[$i].'/';
return $catalog;
}

function getParentId()
{
return $this->parent_id;
}

function getOrigId()
{
return $this->orig_id;
}

function getGrp()
{
return $this->grp;
}

function setGrp($grp)
{
$this->grp=$grp;
}

function getGrps()
{
return $this->grps;
}

function setGrps($grps)
{
$this->grps=$grps;
}

function getPersonId()
{
return $this->person_id;
}

function getUserId()
{
return $this->user_id;
}

function setUserId($id)
{
$this->user_id=$id;
}

function getUserFolder()
{
return c_ascii($this->getLogin()) ? $this->getLogin() : $this->getUserId();
}

function getGroupId()
{
return $this->group_id;
}

function setGroupId($id)
{
$this->group_id=$id;
}

function getGroupLogin()
{
return $this->group_login;
}

function getGroupName()
{
return $this->getGroupLogin();
}

function getPerms()
{
return $this->perms;
}

function getPermString()
{
return $this->perm_string!='' ? $this->perm_string
                              : strPerms($this->getPerms());
}

function getPermHTML()
{
return $this->perm_string!='' ? str_replace('-','-&nil;',$this->perm_string)
                              : strPerms($this->getPerms(),true);
}

function isPermitted($right)
{
return true;
}

function isReadable()
{
return $this->isPermitted(PERM_READ);
}

function isWritable()
{
return $this->isPermitted(PERM_WRITE);
}

function isAppendable()
{
return $this->isPermitted(PERM_APPEND);
}

function isPostable()
{
return $this->isPermitted(PERM_POST);
}

function isHidden()
{
return ($this->perms & 0x1100)==0;
}

function isDisabled()
{
return $this->disabled;
}

function getSubject()
{
return $this->subject;
}

function getSubjectDesc()
{
return $this->getSubject()!=''
       ? $this->getSubject()
       : ($this->getTitle()!=''
          ? $this->getTitle()
	  : $this->getBodyTiny());
}

function getLang()
{
return $this->lang;
}

function getLangName()
{
global $langCodes;

return $langCodes[$this->lang];
}

function getAuthor()
{
return $this->author;
}

function getAuthorXML()
{
return $this->author_xml;
}

function getAuthorHTML()
{
return mtextToHTML($this->getAuthorXML(),MTEXT_LINE,$this->getId());
}

function getSource()
{
return $this->source;
}

function getSourceXML()
{
return $this->source_xml;
}

function getSourceHTML()
{
return mtextToHTML($this->getSourceXML(),MTEXT_LINE,$this->getId());
}

function getTitle()
{
return $this->title;
}

function getTitleXML()
{
return $this->title_xml;
}

function getTitleHTML()
{
return mtextToHTML($this->getTitleXML(),MTEXT_SHORT,$this->getId());
}

function isTitlePea()
{
global $peaSize,$peaSizeMinus,$peaSizePlus;

return cleanLength($this->getTitleXML())<=$peaSize+$peaSizePlus;
}

function getTitlePea()
{
global $peaSize,$peaSizeMinus,$peaSizePlus;

return shortenNote($this->getTitleXML(),$peaSize,$peaSizeMinus,$peaSizePlus);
}

function getTitlePeaXML()
{
global $peaSize,$peaSizeMinus,$peaSizePlus;

return shorten($this->getTitleXML(),$peaSize,$peaSizeMinus,$peaSizePlus);
}

function getTitlePeaHTML()
{
return mtextToHTML($this->getTitlePeaXML(),MTEXT_SHORT,$this->getId());
}

function getComment0()
{
return $this->comment0;
}

function getComment0XML()
{
return $this->comment0_xml;
}

function getComment0HTML()
{
return mtextToHTML($this->getComment0XML(),MTEXT_LINE,$this->getId());
}

function getComment1()
{
return $this->comment1;
}

function getComment1XML()
{
return $this->comment1_xml;
}

function getComment1HTML()
{
return mtextToHTML($this->getComment1XML(),MTEXT_LINE,$this->getId());
}

function getURL()
{
return $this->url;
}

function getURLEllip()
{
global $urlEllipSize;

return ellipsize($this->url,$urlEllipSize);
}

function getURLDomain()
{
return $this->url_domain;
}

function getURLCheck()
{
return $this->url_check;
}

function getURLCheckSuccess()
{
return $this->url_check_success;
}

function getURLCheckFail()
{
return $this->url_check_success==0 ? 0 : time()-$this->url_check_success;
}

function getURLCheckFailDays()
{
return floor($this->getURLCheckFail()/(24*60*60));
}

function getBody()
{
return $this->body;
}

function getBodyXML()
{
return $this->body_xml;
}

function getBodyHTML()
{
return mtextToHTML($this->getBodyXML(),MTEXT_SHORT,$this->getId());
}

function getBodyNormal()
{
return shortenNote($this->getBodyXML(),65535,0,0);
}

function isBodyTiny()
{
global $tinySize,$tinySizeMinus,$tinySizePlus;

return cleanLength($this->getBodyXML())<=$tinySize+$tinySizePlus;
}

function getBodyTiny()
{
global $tinySize,$tinySizeMinus,$tinySizePlus;

return shortenNote($this->getBodyXML(),$tinySize,$tinySizeMinus,$tinySizePlus);
}

function getBodyTinyXML()
{
global $tinySize,$tinySizeMinus,$tinySizePlus;

return shorten($this->getBodyXML(),$tinySize,$tinySizeMinus,$tinySizePlus);
}

function getBodyTinyHTML()
{
return mtextToHTML($this->getBodyTinyXML(),MTEXT_SHORT,$this->getId());
}

function isBodySmall()
{
global $smallSize,$smallSizeMinus,$smallSizePlus;

return cleanLength($this->getBodyXML())<=$smallSize+$smallSizePlus;
}

function getBodySmallXML()
{
global $smallSize,$smallSizeMinus,$smallSizePlus;

return shorten($this->getBodyXML(),$smallSize,$smallSizeMinus,$smallSizePlus);
}

function getBodySmallHTML()
{
return mtextToHTML($this->getBodySmallXML(),MTEXT_SHORT,$this->getId());
}

function isBodyMedium()
{
global $mediumSize,$mediumSizeMinus,$mediumSizePlus;

return cleanLength($this->getBodyXML())<=$mediumSize+$mediumSizePlus;
}

function getBodyMediumXML()
{
global $mediumSize,$mediumSizeMinus,$mediumSizePlus;

return shorten($this->getBodyXML(),$mediumSize,$mediumSizeMinus,$mediumSizePlus);
}

function getBodyMediumHTML()
{
return mtextToHTML($this->getBodyMediumXML(),MTEXT_SHORT,$this->getId());
}

function getBodyFormat()
{
return $this->body_format;
}

function hasLargeBody()
{
return $this->has_large_body;
}

function getLargeBody()
{
return $this->large_body;
}

function getLargeBodyXML()
{
return $this->large_body_xml;
}

function getLargeBodyHTML()
{
return new LargeText($this->getLargeBodyXML(),$this->getId());
}

function getLargeBodyFormat()
{
return $this->large_body_format;
}

function getLargeBodyFilename()
{
return $this->large_body_filename;
}

function getLargeBodySize()
{
return strlen($this->large_body);
}

function getLargeBodySizeKB()
{
return (int)($this->getLargeBodySize()/1024);
}

function getPriority()
{
return $this->priority;
}

function getIndex0()
{
return $this->index0;
}

function getIndex1()
{
return $this->index1;
}

function getIndex2()
{
return $this->index2;
}

function getVote()
{
return $this->vote;
}

function getVoteCount()
{
return $this->vote_count;
}

function getRating()
{
return $this->rating;
}

function getRatingString()
{
return sprintf("%1.2f",$this->getRating());
}

function getRating20()
{
return (int)round($this->getRating()*4);
}

function getSent()
{
return strtotime($this->sent);
}

function getCreated()
{
return strtotime($this->created);
}

function getModified()
{
return strtotime($this->modified);
}

function getAccessed()
{
return strtotime($this->accessed);
}

function getModbits()
{
return $this->modbits;
}

function getAnswers()
{
return $this->answers;
}

function getLastAnswer()
{
return !empty($this->last_answer) && $this->last_answer!=0
       ? strtotime($this->last_answer) : 0;
}

function getLastAnswerId()
{
return $this->last_answer_id;
}

function getLastAnswerUserId()
{
return $this->last_answer_user_id;
}

function getSmallImage()
{
return $this->small_image;
}

function hasSmallImage()
{
return $this->small_image!=0;
}

function getSmallImageX()
{
return $this->small_image_x;
}

function getSmallImageY()
{
return $this->small_image_y;
}

function getSmallImageURL()
{
global $thumbnailType;

$format=$this->hasLargeImage() ? $thumbnailType : $this->getLargeImageFormat();
return getImageURL($this->getId(),getImageExtension($format),
                   $this->getSmallImage(),'small');
}

function getLargeImage()
{
return $this->large_image;
}

function hasLargeImage()
{
return $this->large_image!=0;
}

function getLargeImageX()
{
return $this->large_image_x;
}

function getLargeImageY()
{
return $this->large_image_y;
}

function getLargeImageURL()
{
$format=$this->getLargeImageFormat();
return getImageURL($this->getId(),getImageExtension($format),
                   $this->getLargeImage(),'large');
}

function getImage()
{
return $this->hasLargeImage() ? $this->getLargeImage()
                              : $this->getSmallImage();
}

function getImageX()
{
return $this->hasLargeImage() ? $this->getLargeImageX()
                              : $this->getSmallImageX();
}

function getImageY()
{
return $this->hasLargeImage() ? $this->getLargeImageY()
                              : $this->getSmallImageY();
}

function getImageURL()
{
return $this->hasLargeImage() ? $this->getLargeImageURL()
                              : $this->getSmallImageURL();
}

function getLargeImageSize()
{
return $this->large_image_size;
}

function getLargeImageSizeKB()
{
return (int)($this->large_image_size/1024);
}

function getLargeImageFormat()
{
return $this->large_image_format;
}

function getLargeImageFilename()
{
return $this->large_image_filename;
}

function getProperty($name)
{
$func='get'.ucfirst($name);
return $this->$func();
}

function getCompositeValue($value)
{
$value=preg_replace('/\$\[([-\d]+)\]/e','$this->getCatalog(\1,0)',$value);
$value=preg_replace('/\$\[([-\d]+),([-\d]+)\]/e','$this->getCatalog(\1,\2)',
                    $value);
$value=preg_replace('/\$\{(\w+)\}/e','$this->getProperty("\1")',$value);
return $value;
}

}

function getGrpsByEntryId($id)
{
$result=sql("select grp
	     from entry_grps
	     where entry_id=$id",
	    __FUNCTION__);
$grps=array();
while(list($grp)=mysql_fetch_row($result))
     $grps[]=$grp;
return $grps;
}

function setGrpsByEntryId($id,$grps)
{
sql('lock tables entry_grps write',
    __FUNCTION__,'lock');
sql("delete
     from entry_grps
     where entry_id=$id",
    __FUNCTION__,'delete');
journal("delete
         from entry_grps
         where entry_id=".journalVar('entries',$id));
foreach($grps as $grp)
       {
       sql("insert into entry_grps(entry_id,grp)
            values($id,$grp)",
	   __FUNCTION__,'insert');
       $eid=sql_insert_id();
       journal('insert into entry_grps(entry_id,grp)
                values('.journalVar('entries',$id).",$grp)",
	       'entry_grps',$eid);
       }
sql('unlock tables',
    __FUNCTION__,'unlock');
}

function setHiddenByEntryId($id,$hidden)
{
if($hidden)
  $op='& ~0x1100';
else
  $op='| 0x1100';
sql("update entries
     set perms=perms $op
     where id=$id",
    __FUNCTION__);
journal("update entries
         set perms=perms $op
	 where id=".journalVar('entries',$id));
dropPostingsInfoCache(DPIC_POSTINGS);
if(getTypeByEntryId($id)==ENT_FORUM)
  answerUpdate($parent_id);
}

function setDisabledByEntryId($id,$disabled)
{
$disabled=(int)$disabled;
sql("update entries
     set disabled=$disabled
     where id=$id",
    __FUNCTION__);
journal("update entries
         set disabled=$disabled
	 where id=".journalVar('entries',$id));
dropPostingsInfoCache(DPIC_POSTINGS);
if(getTypeByEntryId($id)==ENT_FORUM)
  answerUpdate($parent_id);
}

function getTypeByEntryId($id)
{
$result=sql("select entry
	     from entries
	     where id=$id",
	    __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : ENT_NULL;
}

function getGrpByEntryId($id)
{
$result=sql("select grp
	     from entries
	     where id=$id",
	    __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : ENT_NULL;
}

function getParentIdByEntryId($id)
{
$result=sql("select parent_id
	     from entries
	     where id=$id",
	    __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : ENT_NULL;
}

function validateHierarchy($parentId,$up,$entry,$id)
{
if($parentId<0)
  return EVH_NO_PARENT;
if($up<0)
  return EVH_NO_UP;
if($parentId!=0 && $up==0)
  return EVH_NOT_UP_UNDER_PARENT;
$parentTrack=$parentId>0 ? trackById('entries',$parentId) : '';
if($parentTrack===0)
  return EVH_NO_PARENT;
$upTrack=$up>0 ? trackById('entries',$up) : '';
if($upTrack===0)
  return EVH_NO_UP;
if(substr($upTrack,0,strlen($parentTrack))!=$parentTrack)
  return EVH_NOT_UP_UNDER_PARENT;
if(strpos($upTrack,track($id))!==false)
  return EVH_LOOP;
$parentEntry=getTypeByEntryId($parentId);
$upEntry=getTypeByEntryId($up);
$correct=false;
switch($entry)
      {
      case ENT_POSTING:
           $correct=$parentEntry==ENT_TOPIC
	            && ($upEntry==ENT_POSTING || $parentId==$up);
           break;
      case ENT_FORUM:
           $correct=($parentEntry==ENT_POSTING || $parentEntry==ENT_COMPLAIN)
	            && ($upEntry==ENT_FORUM || $parentId==$up);
           break;
      case ENT_TOPIC:
           $correct=$parentId==0 && ($upEntry==ENT_TOPIC || $up==0);
           break;
      case ENT_IMAGE:
           $correct=$parentId==0 && ($upEntry==ENT_POSTING
	                             || $upEntry==ENT_FORUM
				     || $upEntry==ENT_TOPIC
				     || $up==0);
           break;
      case ENT_COMPLAIN:
           $correct=$parentId==0 && $up==0;
           break;
      }
if(!$correct)
  return EVH_INCORRECT;
return EG_OK;
}

function entryExists($entry,$id)
{
$result=sql("select id
	     from entries
	     where id=$id and entry=$entry",
	    __FUNCTION__);
return mysql_num_rows($result)>0;
}
?>
