<?php
# @(#) $Id$

require_once('lib/usertag.php');
require_once('lib/mtext-shorten.php');

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
var $id;
var $ident;
var $entry;
var $up;
var $track;
var $parent_id;
var $orig_id;
var $grp;
var $grps;
var $person_id;
var $user_id;
var $group_id;
var $group_login;
var $perms;
var $perm_string;
var $disabled;
var $subject;
var $subject_sort;
var $lang;
var $author;
var $author_xml;
var $source;
var $source_xml;
var $comment0;
var $comment0_xml;
var $comment1;
var $comment1_xml;
var $url;
var $url_domain;
var $url_check;
var $url_check_success;
var $body;
var $body_xml;
var $body_format;
var $has_large_body;
var $large_body;
var $large_body_xml;
var $large_body_format;
var $large_body_filename;
var $priority;
var $index0;
var $index1;
var $index2;
var $vote;
var $vote_count;
var $rating;
var $sent;
var $created;
var $modified;
var $accessed;
var $modbits;
var $answers;
var $last_answer;
var $last_answer_id;
var $last_answer_user_id;
var $small_image;
var $small_image_x;
var $small_image_y;
var $large_image;
var $large_image_x;
var $large_image_y;
var $large_image_size;
var $large_image_format;
var $large_image_filename;

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

function getParent()
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
return $this->getSubject()!='' ? $this->getSubject() : $this->getCleanBodyTiny();
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
return mtextToHTML($this->getLargeBodyXML(),MTEXT_LONG,$this->getId(),true);
}

function getLargeBodyFormat()
{
return $this->large_body_format;
}

function getLargeBodyFilename()
{
return $this->large_body_filename;
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

function getSmallImageX()
{
return $this->small_image_x;
}

function getSmallImageY()
{
return $this->small_image_y;
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
if(($parent_id=getParentIdByEntryId($id))>0) // FIXME Сейчас у всех есть parent
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
if(($parent_id=getParentIdByEntryId($id))>0) // FIXME Сейчас у всех есть parent
  answerUpdate($parent_id);
}
?>
