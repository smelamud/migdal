<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/sendertag.php');
require_once('lib/tmptexts.php');
require_once('lib/text.php');
require_once('lib/ctypes.php');
require_once('lib/stotext.php');

class Message
      extends SenderTag
{
var $id;
var $subject;
var $author;
var $source;
var $stotext;
var $title;
var $image_size;
var $image_x;
var $image_y;
var $hidden;
var $disabled;
var $sent;
var $url;
var $answer_count;
var $last_answer;

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
return array('subject','author','source','hidden','disabled','url');
}

function getWorldVars()
{
return array('subject','author','source','hidden','sender_id','url');
}

function getAdminVars()
{
return array('disabled');
}

function getNormal($isAdmin=false)
{
$normal=SenderTag::getNormal($isAdmin);
$normal['stotext_id']=$this->stotext->getId();
return $normal;
}

function store($id='id',$admin='userModerator')
{
global $userId;

$result=$this->stotext->store($GLOBALS[$admin]);
if(!$result)
  return $result;
$normal=$this->getNormal($GLOBALS[$admin]);
if($this->$id)
  $result=mysql_query(makeUpdate('messages',$normal,array('id' => $this->$id)));
else
  {
  $sent=date('Y-m-d H:i:s',time());
  if($normal['sender_id']<=0)
    $normal['sender_id']=$userId;
  $normal['sent']=$sent;
  $result=mysql_query(makeInsert('messages',$normal));
  $this->$id=mysql_insert_id();
  $this->sender_id=$userId;
  $this->sent=$sent;
  }
return $result;
}

function isEditable()
{
global $userId,$userModerator;

return $this->sender_id==0 || $this->sender_id==$userId || $userModerator;
}

function isModerable()
{
global $userModerator;

return $userModerator;
}

function getId()
{
return $this->id;
}

function getSubject()
{
return $this->subject;
}

function getSubjectDesc()
{
return $this->getSubject()!='' ? $this->getSubject() : $this->getBodyTiny();
}

function getAuthor()
{
return $this->author;
}

function getHTMLAuthor()
{
return stotextToHTML(TF_MAIL,$this->getAuthor());
}

function getSource()
{
return $this->source;
}

function getHTMLSource()
{
return stotextToHTML(TF_MAIL,$this->getSource());
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
return stotextToHTML(TF_MAIL,$this->getBody());
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
return stotextToHTML(TF_MAIL,$this->getBodyTiny());
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
return stotextToHTML(TF_MAIL,$this->getBodyMedium());
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
return stotextToHTML($this->getLargeFormat(),$this->getLargeBody());
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
return stotextToHTML(TF_MAIL,$this->title);
}

function isHidden()
{
return $this->hidden;
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

function getAnswerCount()
{
return $this->answer_count;
}

function getLastAnswer()
{
return !empty($this->last_answer) ? strtotime($this->last_answer) : 0;
}

}

function messageExists($id)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$result=mysql_query("select id
		     from messages
		     where id=$id and (hidden<$hide or sender_id=$userId) and
		     (disabled<$hide or sender_id=$userId)")
		    /* здесь нужно поменять, если будут другие ограничения на
		       просмотр TODO */
	     or die('Ошибка SQL при выборке сообщения');
return mysql_num_rows($result)>0;
}
?>
