<?php
# @(#) $Id$

require_once('lib/sendertag.php');
require_once('lib/tmptexts.php');
require_once('lib/text.php');

class Message
      extends SenderTag
{
var $id;
var $body;
var $large_body;
var $subject;
var $image_set;
var $image_id;
var $has_large_image;
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
if(isset($vars['bodyid']))
  $this->body=tmpTextRestore($vars['bodyid']);
if(isset($vars['large_bodyid']))
  $this->large_body=tmpTextRestore($vars['large_bodyid']);
if(isset($vars['subjectid']))
  $this->subject=tmpTextRestore($vars['subjectid']);
}

function getCorrespondentVars()
{
return array('body','large_body','subject','image_set','hidden','disabled');
}

function getWorldVars()
{
return array('body','large_body','subject','image_set','hidden');
}

function getAdminVars()
{
return array('disabled');
}

function store($id='id',$admin='userModerator')
{
global $userId;

$normal=$this->getNormal($GLOBALS[$admin]);
if($this->$id)
  $result=mysql_query(makeUpdate('messages',$normal,array('id' => $this->$id)));
else
  {
  $sent=date('Y-m-d H:i:s',time());
  $normal['sender_id']=$userId;
  $normal['sent']=$sent;
  $result=mysql_query(makeInsert('messages',$normal));
  $this->$id=mysql_insert_id();
  $this->sender_id=$userId;
  $this->sent=$sent;
  }
return $result;
}

function hasSubject()
{
return true;
}

function mandatorySubject()
{
return $this->hasSubject();
}

function hasLargeBody()
{
return false;
}

function mandatoryLargeBody()
{
return $this->hasLargeBody();
}

function hasImage()
{
return true;
}

function mandatoryImage()
{
return false;
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

function getBody()
{
return $this->body;
}

function getHTMLBody()
{
return enrichedTextToHTML($this->body);
}

function getLargeBody()
{
return $this->large_body;
}

function getHTMLLargeBody()
{
return enrichedTextToHTML($this->large_body);
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
