<?php
# @(#) $Id$

require_once('lib/sendertag.php');
require_once('lib/tmptexts.php');
require_once('lib/text.php');
require_once('lib/ctypes.php');

class Message
      extends SenderTag
{
var $id;
var $subject;
var $stotext_id;
var $body;
var $image_set;
var $large_filename;
var $large_format;
var $large_body;
var $large_imageset;
var $image_id;
var $has_large_image;
var $title;
var $hidden;
var $disabled;
var $sent;
var $answer_count;

function Message($row)
{
$this->SenderTag($row);
}

function setup($vars)
{
if(!isset($vars['edittag']))
  return;
foreach($this->getCorrespondentVars() as $var)
       $this->$var=htmlspecialchars($vars[$var],ENT_QUOTES);

if(!c_digit($this->large_format) || $this->large_format>TF_MAX)
  $this->large_format=TF_PLAIN;

if($vars['large_body']!='')
  $this->large_body=textToStotext($this->large_format,$vars['large_body']);
if(isset($vars['large_bodyid']))
  {
  $lb=tmpTextRestore($vars['large_bodyid']);
  if($lb!='')
    $this->large_body=$lb;
  }

if(isset($vars['bodyid']))
  $this->body=tmpTextRestore($vars['bodyid']);
if(isset($vars['subjectid']))
  $this->subject=tmpTextRestore($vars['subjectid']);
}

function getWorldStotextVars()
{
return array('body','large_filename','large_format','large_body','image_set');
}

function getAdminStotextVars()
{
return array();
}

function getNormalStotext($isAdmin=false)
{
$normal=$this->collectVars($this->getWorldStotextVars());
if($isAdmin)
  $normal=array_merge($normal,$this->collectVars($this->getAdminStotextVars()));
return $normal;
}

function storeStotext($id='id',$admin='userModerator')
{
$normal=$this->getNormalStotext($GLOBALS[$admin]);
if($this->$id)
  $result=mysql_query(makeUpdate('stotexts',
                                 $normal,
				 array('id' => $this->stotext_id)));
else
  {
  $result=mysql_query(makeInsert('stotexts',$normal));
  $this->stotext_id=mysql_insert_id();
  }
return $result;
}

function getCorrespondentVars()
{
return array('body','large_format','image_set','subject','hidden','disabled');
}

function getWorldVars()
{
return array('subject','stotext_id','hidden','sender_id');
}

function getAdminVars()
{
return array('disabled');
}

function store($id='id',$admin='userModerator')
{
global $userId;

$result=$this->storeStotext($id,$admin);
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

function getStotextId()
{
return $this->stotext_id;
}

function getBody()
{
return $this->body;
}

function getHTMLBody()
{
return stotextToHTML(TF_PLAIN,$this->body);
}

function getLargeFilename()
{
return $this->large_filename;
}

function isLargeTextAvailable()
{
return $this->large_filename!='';
}

function getLargeFormat()
{
return $this->large_format;
}

function getLargeBody()
{
return $this->large_body;
}

function getHTMLLargeBody()
{
return stotextToHTML($this->large_format,$this->large_body);
}

function getLargeImageSet()
{
return $this->large_imageset;
}

function getImageSet()
{
return $this->image_set;
}

function setImageSet($image_set)
{
$this->image_set=$image_set;
}

function getImageId()
{
return $this->image_id;
}

function hasLargeImage()
{
return $this->has_large_image;
}

function getTitle()
{
return $this->title;
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
