<?php
# @(#) $Id$

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
}

function getCorrespondentVars()
{
return array('subject','author','source','hidden','disabled');
}

function getWorldVars()
{
return array('subject','author','source','hidden','sender_id');
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

function getLocalConf($name)
{
return $GLOBALS[get_class($this).$name];
}

function hasSubject()
{
return $this->getLocalConf('HasSubject');
}

function mandatorySubject()
{
return $this->hasSubject() && $this->getLocalConf('MandatorySubject');
}

function hasAuthor()
{
return $this->getLocalConf('HasAuthor');
}

function mandatoryAuthor()
{
return $this->hasAuthor() && $this->getLocalConf('MandatoryAuthor');
}

function hasSource()
{
return $this->getLocalConf('HasSource');
}

function mandatorySource()
{
return $this->hasSource() && $this->getLocalConf('MandatorySource');
}

function hasBody()
{
return $this->getLocalConf('HasBody');
}

function mandatoryBody()
{
return $this->hasBody() && $this->getLocalConf('MandatoryBody');
}

function hasLargeBody()
{
return $this->getLocalConf('HasLargeBody');
}

function mandatoryLargeBody()
{
return $this->hasLargeBody() && $this->getLocalConf('MandatoryLargeBody');
}

function hasImage()
{
return $this->getLocalConf('HasImage');
}

function mandatoryImage()
{
return $this->hasImage() && $this->getLocalConf('MandatoryImage');
}

function hasTitle()
{
return $this->getLocalConf('HasTitle');
}

function mandatoryTitle()
{
return $this->hasTitle() && $this->getLocalConf('MandatoryTitle');
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

function getAuthor()
{
return $this->author;
}

function getSource()
{
return $this->source;
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
