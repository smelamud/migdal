<?php
# @(#) $Id$

require_once('lib/usertag.php');
require_once('lib/selectiterator.php');
require_once('lib/tmptexts.php');
require_once('lib/grps.php');
require_once('lib/images.php');
require_once('lib/topics.php');

class Message
      extends UserTag
{
var $id;
var $body;
var $subject;
var $topic_id;
var $topic_name;
var $personal_id;
var $sender_id;
var $sender_hidden;
var $grp;
var $image_set;
var $image_id;
var $up;
var $hidden;
var $disabled;
var $sent;

function Message($row)
{
$this->UserTag($row);
}

function setup($vars)
{
if(!isset($vars['edittag']))
  return;
foreach($this->getCorrespondentVars() as $var)
       $this->$var=htmlspecialchars($vars[$var],ENT_QUOTES);
if(isset($vars['bodyid']))
  $this->body=tmpTextRestore($vars['bodyid']);
if(isset($vars['subjectid']))
  $this->subject=tmpTextRestore($vars['subjectid']);
}

function getCorrespondentVars()
{
return array('body','subject','topic_id','grp','image_set','up','hidden',
             'disabled','personal_id');
}

function getWorldVars()
{
return array('body','subject','topic_id','grp','image_set','up','hidden',
             'personal_id');
}

function getAdminVars()
{
return array('disabled');
}

function store()
{
global $userId,$userModerator;

$normal=$this->getWorldVarValues();
if($userModerator)
  $normal=array_merge($normal,$this->getAdminVarValues());
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

function getImageId()
{
return $this->image_id;
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

function isDisabled()
{
return $this->disabled;
}

function getTopicId()
{
return $this->topic_id;
}

function getTopicName()
{
return $this->topic_name;
}

function getPersonalId()
{
return $this->personal_id;
}

function getSenderId()
{
return $this->sender_id;
}

function isSenderHidden()
{
return $this->sender_hidden ? 1 : 0;
}

function isSenderAdminHidden()
{
return $this->sender_hidden>1 ? 1 : 0;
}

function isSenderVisible()
{
global $userAdminUsers;

return !$this->isSenderHidden()
       || ($userAdminUsers && !$this->isSenderAdminHidden());
}

function getSentView()
{
$t=strtotime($this->sent);
return date('j/m/Y в H:i:s',$t);
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

function newGrpMessage($grp,$row=array())
{
$name=getGrpClassName($grp);
return new $name($row);
}

class MessageListIterator
      extends SelectIterator
{

function MessageListIterator($grp,$topic=0,$personal=0)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$topicFilter=$topic==0 ? '' : " and messages.topic_id=$topic ";
$grpFilter=$this->getGrpCondition($grp);
$this->SelectIterator('Message',
		      "select messages.id as id,body,subject,grp,sent,topic_id,
		              sender_id,messages.hidden as hidden,disabled,
			      users.hidden as sender_hidden,
			      images.image_set as image_set,
			      images.id as image_id,
			      topics.name as topic_name,
			      login,gender,email,hide_email
		       from messages
			     left join images
				  on messages.image_set=images.image_set
			     left join topics
				  on messages.topic_id=topics.id
			     left join users
			          on messages.sender_id=users.id
		       where (messages.hidden<$hide or sender_id=$userId) and
			     (messages.disabled<$hide or sender_id=$userId) and
			     personal_id=$personal and
			     up=0 $grpFilter $topicFilter
		       order by sent desc");
		    /* здесь нужно поменять, если будут другие ограничения на
		       просмотр TODO */
}

function getGrpCondition($grp)
{
return $grp==GRP_ANY ? ''
                     : 'and ('.join(' or ',
		               $this->Eq(getGrpNumbers($grp))).')';
}

function Eq($nums)
{
$conds=array();
foreach($nums as $num)
       $conds[]="grp=$num";
return $conds;
}

function create($row)
{
return newMessage($row);
}

}

function getMessageById($id,$grp=0)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$result=mysql_query("select id,body,subject,topic_id,personal_id,sender_id,grp,
                     image_set,up,hidden,disabled
		     from messages
		     where id=$id and (hidden<$hide or sender_id=$userId) and
		     (disabled<$hide or sender_id=$userId)");
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
		     where id=$id and (hidden<$hide or sender_id=$userId) and
		     (disabled<$hide or sender_id=$userId)");
		    /* здесь нужно поменять, если будут другие ограничения на
		       просмотр TODO */
return mysql_num_rows($result)>0;
}
?>
