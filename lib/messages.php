<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/sendertag.php');
require_once('lib/bug.php');
require_once('lib/tmptexts.php');
require_once('lib/text.php');
require_once('lib/ctypes.php');
require_once('lib/stotext.php');
require_once('lib/langs.php');
require_once('lib/url-domain.php');
require_once('lib/postings-info.php');
require_once('lib/permissions.php');
require_once('lib/sql.php');

class Message
      extends SenderTag
{
var $id;
var $up;
var $track;
var $subject;
var $author;
var $source;
var $comment0;
var $comment1;
var $stotext;
var $title;
var $image_size;
var $image_x;
var $image_y;
var $group_id;
var $group_login;
var $perms;
var $hidden;
var $disabled;
var $sent;
var $url;
var $url_domain;
var $answer_count;
var $last_answer;
var $url_fail_time;

function Message($row)
{
$this->SenderTag($row);
$this->stotext=new Stotext($row);
}

function setup($vars)
{
if(!isset($vars['edittag']))
  return;
foreach($this->getCorrespondentVars() as $var)
       $this->$var=htmlspecialchars($vars[$var],ENT_QUOTES);
$this->stotext->setup($vars);
if($vars['user_name']!='')
  $this->login=$vars['user_name'];
if($vars['group_name']!='')
  $this->group_login=$vars['group_name'];
if($this->perm_string!='')
  $this->perms=permString($this->perm_string,strPerms($this->perms));
else
  if($this->hidden)
    $this->perms&=~0x1100;
  else
    $this->perms|=0x1100;
if(isset($vars['subjectid']))
  $this->subject=tmpTextRestore($vars['subjectid']);
if(isset($vars['authorid']))
  $this->author=tmpTextRestore($vars['authorid']);
if(isset($vars['sourceid']))
  $this->source=tmpTextRestore($vars['sourceid']);
if(isset($vars['comment0id']))
  $this->comment0=tmpTextRestore($vars['comment0id']);
if(isset($vars['comment1id']))
  $this->comment1=tmpTextRestore($vars['comment1id']);
if(isset($vars['urlid']))
  $this->url=tmpTextRestore($vars['urlid']);
}

function getCorrespondentVars()
{
return array('up','login','group_login','perm_string','hidden','lang',
             'subject','author','source','comment0','comment1','disabled',
	     'url');
}

function getWorldVars()
{
return array('up','track','lang','subject','author','source','comment0',
             'comment1','sender_id','group_id','perms','url','url_domain');
}

function getAdminVars()
{
return array('disabled');
}

function getJencodedVars()
{
return array('up' => 'messages','subject' => '','author' => '','source' => '',
             'comment0' => '','comment1' => '','stotext_id' => 'stotexts',
	     'sender_id' => 'users','group_id' => 'users','url' => '',
	     'url_domain' => '');
}

function getNormal($isAdmin=false)
{
$normal=SenderTag::getNormal($isAdmin);
$normal['stotext_id']=$this->stotext->getId();
return $normal;
}

function store($id='id',$admin='userModerator')
{
global $userId,$realUserId;

$result=$this->stotext->store($GLOBALS[$admin]);
if(!$result)
  return $result;
$normal=$this->getNormal($GLOBALS[$admin]);
if(isset($normal['url']))
  $normal['url_domain']=getURLDomain($normal['url']);
if($this->$id)
  {
  $result=sql(makeUpdate('messages',
                         $normal,
			 array('id' => $this->$id)),
	      get_method($this,'store'),'update');
  journal(makeUpdate('messages',
                     jencodeVars($normal,$this->getJencodedVars()),
		     array('id' => journalVar('messages',$this->$id))));
  }
else
  {
  $sent=date('Y-m-d H:i:s',time());
  $senderId=$userId>0 ? $userId : $realUserId;
  if($normal['sender_id']<=0)
    $normal['sender_id']=$senderId;
  if($normal['group_id']<=0)
    $normal['group_id']=$normal['sender_id'];
  if($this->sent=='')
    $this->sent=$sent;
  $normal['sent']=$this->sent;
  $result=sql(makeInsert('messages',
                         $normal),
	      get_method($this,'store'),'insert');
  $this->$id=sql_insert_id();
  journal(makeInsert('messages',
                     jencodeVars($normal,$this->getJencodedVars())),
	  'messages',$this->$id);
  $this->sender_id=$normal['sender_id'];
  $this->group_id=$normal['group_id'];
  }
journal("track messages ".journalVar('messages',$this->$id));
return $result;
}

function getId()
{
return $this->id;
}

function getTrack()
{
return $this->track;
}

function getUpValue()
{
return $this->up;
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

function getSubject()
{
return $this->subject;
}

function getSubjectDesc()
{
return $this->getSubject()!='' ? $this->getSubject() : $this->getCleanBodyTiny();
}

function getAuthor()
{
return $this->author;
}

function getHTMLAuthor()
{
return stotextToHTML(TF_MAIL,$this->getAuthor(),$this->getMessageId());
}

function getSource()
{
return $this->source;
}

function getHTMLSource()
{
return stotextToHTML(TF_MAIL,$this->getSource(),$this->getMessageId());
}

function getComment0()
{
return $this->comment0;
}

function getHTMLComment0()
{
return stotextToHTML(TF_MAIL,$this->getComment0(),$this->getMessageId());
}

function getComment1()
{
return $this->comment1;
}

function getHTMLComment1()
{
return stotextToHTML(TF_MAIL,$this->getComment1(),$this->getMessageId());
}

function getStotext()
{
return $this->stotext;
}

function getStotextId()
{
return $this->stotext->getId();
}

function setStotextId($id)
{
$this->stotext->setId($id);
}

function getBody()
{
return $this->stotext->getBody();
}

function getHTMLBody()
{
return stotextToHTML(TF_MAIL,$this->getBody(),$this->getMessageId());
}

function isBodyTiny()
{
global $tinySize,$tinySizeMinus,$tinySizePlus;

return strlen($this->getBody())<=$tinySize+$tinySizePlus;
}

function getBodyTiny()
{
global $tinySize,$tinySizeMinus,$tinySizePlus;

return shorten($this->getBody(),$tinySize,$tinySizeMinus,$tinySizePlus);
}

function getHTMLBodyTiny()
{
return stotextToHTML(TF_MAIL,$this->getBodyTiny(),$this->getMessageId());
}

function getCleanBodyTiny()
{
global $tinySize,$tinySizeMinus,$tinySizePlus;

return shorten(clearStotext(TF_MAIL,$this->getBody()),
               $tinySize,$tinySizeMinus,$tinySizePlus);
}

function isBodySmall()
{
global $smallSize,$smallSizeMinus,$smallSizePlus;

return strlen($this->getBody())<=$smallSize+$smallSizePlus;
}

function getBodySmall()
{
global $smallSize,$smallSizeMinus,$smallSizePlus;

return shorten($this->getBody(),$smallSize,$smallSizeMinus,$smallSizePlus);
}

function getHTMLBodySmall()
{
return stotextToHTML(TF_MAIL,$this->getBodySmall(),$this->getMessageId());
}

function isBodyMedium()
{
global $mediumSize,$mediumSizeMinus,$mediumSizePlus;

return strlen($this->getBody())<=$mediumSize+$mediumSizePlus;
}

function getBodyMedium()
{
global $mediumSize,$mediumSizeMinus,$mediumSizePlus;

return shorten($this->getBody(),$mediumSize,$mediumSizeMinus,$mediumSizePlus);
}

function getHTMLBodyMedium()
{
return stotextToHTML(TF_MAIL,$this->getBodyMedium(),$this->getMessageId());
}

function getLargeFilename()
{
return $this->stotext->getLargeFilename();
}

function isLargeTextAvailable()
{
return $this->getLargeFilename()!='';
}

function getLargeFormat()
{
return $this->stotext->getLargeFormat();
}

function getLargeBody()
{
return $this->stotext->getLargeBody();
}

function getHTMLLargeBody()
{
return stotextToHTML($this->getLargeFormat(),$this->getLargeBody(),$this->getMessageId());
}

function getLargeImageSet()
{
return $this->stotext->getLargeImageset();
}

function setLargeImageSet($image_set)
{
$this->stotext->setLargeImageSet($image_set);
}

function getImageSet()
{
return $this->stotext->getImageSet();
}

function setImageSet($image_set)
{
$this->stotext->setImageSet($image_set);
}

function getImageId()
{
return $this->stotext->getImageId();
}

function hasLargeImage()
{
return $this->stotext->hasLargeImage();
}

function getTitle()
{
return $this->title;
}

function getImageSize()
{
return $this->image_size;
}

function getImageSizeKB()
{
return (int)($this->image_size/1024);
}

function getImageX()
{
return $this->image_x;
}

function getImageY()
{
return $this->image_y;
}

function getHTMLTitle()
{
return stotextToHTML(TF_MAIL,$this->title,$this->getMessageId());
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
return strPerms($this->getPerms());
}

function getPermHTML()
{
return strPerms($this->getPerms(),true);
}

function isPermitted($right)
{
global $userModerator,$userId;

return $userModerator
       ||
       (!$this->isDisabled() || $this->getUserId()==$userId) &&
       perm($this->getUserId(),$this->getGroupId(),$this->getPerms(),$right);
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
return $this->hidden;
}

function isDisabled()
{
return $this->disabled;
}

function getModbits()
{
return $this->modbits;
}

function getSent()
{
return strtotime($this->sent);
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

function getURLFailTime()
{
return $this->url_fail_time;
}

function getURLFailDays()
{
return floor($this->getURLFailTime()/(24*60*60));
}

function getAnswerCount()
{
return $this->answer_count;
}

function getLastAnswer()
{
return !empty($this->last_answer) && $this->last_answer!=0
       ? strtotime($this->last_answer) : 0;
}

}

function messagesPermFilter($right,$prefix='')
{
global $userModerator,$userId;

if($userModerator)
  return '1';
$filter=permFilter('messages',$right,'sender_id',$prefix);
if($prefix!='' && substr($prefix,-1)!='.')
  $prefix.='.';
return "$filter and (${prefix}disabled=0".
       ($userId>0 ? " or ${prefix}sender_id=$userId)" : ')');
}

function messageExists($id)
{
$hide=messagesPermFilter(PERM_READ);
$result=sql("select id
	     from messages
	     where id=$id and $hide",
	    'messageExists');
return mysql_num_rows($result)>0;
}

function setHiddenByMessageId($id,$hidden)
{
if($hidden)
  $op='& ~0x1100';
else
  $op='| 0x1100';
sql("update messages
     set perms=perms $op
     where id=$id",
    'setHiddenByMessageId');
journal("update messages
         set perms=perms $op
	 where id=".journalVar('messages',$id));
dropPostingsInfoCache(DPIC_POSTINGS);
if(($parent_id=getParentIdByMessageId($id))>0)
  answerUpdate($parent_id);
}

function setDisabledByMessageId($id,$disabled)
{
$disabled=(int)$disabled;
sql("update messages
     set disabled=$disabled
     where id=$id",
    'setDisabledByMessageId');
journal("update messages
         set disabled=$disabled
	 where id=".journalVar('messages',$id));
dropPostingsInfoCache(DPIC_POSTINGS);
if(($parent_id=getParentIdByMessageId($id))>0)
  answerUpdate($parent_id);
}
?>
