<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/selectiterator.php');
require_once('lib/tmptexts.php');
require_once('lib/grps.php');
require_once('lib/images.php');

class Message
      extends DataObject
{
var $id;
var $body;
var $subject;
var $topic_id;
var $personal_id;
var $sender_id;
var $grp;
var $image_set;
var $up;
var $hidden;
var $sent;

function Message($row)
{
$this->DataObject($row);
}

function setup($vars)
{
if(!isset($vars['edittag']))
  return;
foreach($this->getCorrespondentVars() as $var)
       $this->$var=htmlspecialchars($vars[$var],ENT_QUOTES);
if(isset($vars['bodyid']))
  $this->body=tmpTextRestore($vars['bodyid']);
}

function getCorrespondentVars()
{
return array('body','subject','topic_id','grp','image_set','up','hidden',
             'personal_id');
}

function getWorldVars()
{
return array('body','subject','topic_id','grp','image_set','up','hidden',
             'personal_id');
}

function getWorldVarValues()
{
$vals=array();
foreach($this->getWorldVars() as $var)
       $vals[$var]=$this->$var;
return $vals;
}

function store()
{
global $userId;

$normal=$this->getWorldVarValues();
if($this->id)
  $result=mysql_query(makeUpdate('messages',$normal,array('id' => $this->id)));
else
  {
  $sent=date('Y-m-d H:i:s',time());
  $normal['sender_id']=$userId;
  $normal['sent']=$sent;
  $result=mysql_query(makeInsert('messages',$normal));
  $this->id=mysql_insert_id();
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

function hasTopic()
{
return true;
}

function mandatoryTopic()
{
return $this->hasTopic();
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

function getGrp()
{
return $this->grp;
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

function getImageSet()
{
return $this->image_set;
}

function setImageSet($image_set)
{
$this->image_set=$image_set;
}

function getUpValue()
{
return $this->up;
}

function setUpValue($up)
{
$this->up=$up;
}

function isHidden()
{
return $this->hidden;
}

function getTopicId()
{
return $this->topic_id;
}

function getPersonalId()
{
return $this->personal_id;
}

}

class Forum
      extends Message
{

function Forum($row)
{
$this->grp=GRP_FORUMS;
$this->Message($row);
}

function hasSubject()
{
return $this->up==0;
}

function hasImage()
{
return false;
}

}

class News
      extends Message
{

function News($row)
{
$this->grp=GRP_NEWS;
$this->Message($row);
}

}

class Gallery
      extends Message
{

function Gallery($row)
{
$this->grp=GRP_GALLERY;
$this->Message($row);
}

}

class Poll
      extends Message
{

function Poll($row)
{
$this->grp=GRP_POLL;
$this->Message($row);
}

function hasImage()
{
return false;
}

}

function newMessage($row)
{
$name=getGrpClassName($row['grp']);
return new $name($row);
}

function newGrpMessage($grp)
{
$name=getGrpClassName($grp);
return new $name($row);
}

function getMessageById($id,$grp=0)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$result=mysql_query("select id,body,subject,topic_id,personal_id,sender_id,grp,
                     image_set,up,hidden
		     from messages
		     where id=$id and (hidden<$hide or sender_id=$userId)");
		    /* здесь нужно поменять, если будут другие ограничения на
		       просмотр TODO */
return mysql_num_rows($result)>0 ? newMessage(mysql_fetch_assoc($result))
                                 : newGrpMessage($grp);
}

function messageExists($id)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$result=mysql_query("select id
		     from messages
		     where id=$id and (hidden<$hide or sender_id=$userId)");
		    /* здесь нужно поменять, если будут другие ограничения на
		       просмотр TODO */
return mysql_num_rows($result)>0;
}
?>
