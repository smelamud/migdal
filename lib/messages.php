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

class Message
      extends SenderTag
{
var $id;
var $up;
var $track;
var $subject;
var $author;
var $source;
var $stotext;
var $title;
var $image_size;
var $image_x;
var $image_y;
var $group_id;
var $perms;
var $hidden;
var $disabled;
var $sent;
var $url;
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
if(isset($vars['subjectid']))
  $this->subject=tmpTextRestore($vars['subjectid']);
if(isset($vars['authorid']))
  $this->author=tmpTextRestore($vars['authorid']);
if(isset($vars['sourceid']))
  $this->source=tmpTextRestore($vars['sourceid']);
if(isset($vars['urlid']))
  $this->url=tmpTextRestore($vars['urlid']);
}

function getCorrespondentVars()
{
return array('up','lang','subject','author','source','hidden','disabled','url');
}

function getWorldVars()
{
return array('up','track','lang','subject','author','source','hidden',
             'sender_id','url');
}

function getAdminVars()
{
return array('disabled');
}

function getJencodedVars()
{
return array('up' => 'messages','subject' => '','author' => '','source' => '',
             'stotext_id' => 'stotexts','sender_id' => 'users','url' => '');
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
if($this->$id)
  {
  $result=mysql_query(makeUpdate('messages',$normal,array('id' => $this->$id)));
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
  $normal['sent']=$sent;
  $result=mysql_query(makeInsert('messages',$normal));
  $this->$id=mysql_insert_id();
  journal(makeInsert('messages',
                     jencodeVars($normal,$this->getJencodedVars())),
	  'messages',$this->$id);
  $this->sender_id=$senderId;
  $this->sent=$sent;
  }
journal("track messages ".journalVar('messages',$this->$id));
return $result;
}

function isEditable()
{
global $userId,$userModerator;

return $this->sender_id==0 || $this->sender_id==$userId || $userModerator;
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

function getStotext()
{
return $this->stotext;
}

function getStotextId()
{
return $this->stotext->getId();
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
global $userModerator;

return $userModerator
       ||
       perm($this->getUserId(),$this->getGroupId(),
            ($this->getPerms() & ~$this->getDisabled()),$right);
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

function getDisabled()
{
return $this->disabled;
}

function isDisabled()
{
return $this->disabled;
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
return !empty($this->last_answer) ? strtotime($this->last_answer) : 0;
}

}

function messagesPermFilter($right,$prefix='')
{
global $userModerator;

if($userModerator)
  return '1';
return permFilter($right,'sender_id',true,$prefix);
}

function messageExists($id)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$result=mysql_query("select id
		     from messages
		     where id=$id and (hidden<$hide or sender_id=$userId) and
		     (disabled<$hide or sender_id=$userId)")
		    /* ����� ����� ��������, ���� ����� ������ ����������� ��
		       �������� TODO */
          or sqlbug('������ SQL ��� �������� ������� ���������');
return mysql_num_rows($result)>0;
}

function setDisabledByMessageId($id,$disabled)
{
$disabled=(int)$disabled;
mysql_query("update messages
             set disabled=$disabled
	     where id=$id")
  or sqlbug('������ SQL ��� ������������� ���������');
journal("update messages
         set disabled=$disabled
	 where id=".journalVar('messages',$id));
}
?>
