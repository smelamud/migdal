<?php
# @(#) $Id$

require_once('lib/sendertag.php');
require_once('lib/selectiterator.php');
require_once('lib/limitselect.php');
require_once('lib/tmptexts.php');
require_once('lib/grps.php');
require_once('lib/images.php');
require_once('lib/topics.php');
require_once('lib/text.php');

class Message
      extends SenderTag
{
var $id;
var $body;
var $subject;
var $topic_id;
var $topic_name;
var $personal_id;
var $grp;
var $image_set;
var $image_id;
var $up;
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

$normal=$this->getNormal($userModerator);
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

function isModerable()
{
global $userModerator;

return $userModerator;
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

function getHTMLBody()
{
return enrichedTextToHTML($this->body);
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

function getSentView()
{
$t=strtotime($this->sent);
return date('j/m/Y в H:i:s',$t);
}

function getAnswerCount()
{
return $this->answer_count;
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

function hasTopic()
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

function mandatoryImage()
{
return true;
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
      extends LimitSelectIterator
{

function MessageListIterator($grp,$topic=0,$limit=10,$offset=0,$personal=0)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$topicFilter=$topic==0 ? '' : " and messages.topic_id=$topic ";
$grpFilter=getPackedGrpFilter($grp,'messages.');
$this->LimitSelectIterator(
       'Message',
       "select messages.id as id,messages.body as body,
	       messages.subject as subject,messages.grp as grp,
	       messages.sent as sent,messages.topic_id as topic_id,
	       messages.sender_id as sender_id,messages.hidden as hidden,
	       messages.disabled as disabled,users.hidden as sender_hidden,
	       images.image_set as image_set,images.id as image_id,
	       topics.name as topic_name,users.login as login,
	       users.gender as gender,users.email as email,
	       users.hide_email as hide_email,users.rebe as rebe,
	       count(answers.up) as answer_count
	from messages
	      left join images
		   on messages.image_set=images.image_set
	      left join topics
		   on messages.topic_id=topics.id
	      left join users
		   on messages.sender_id=users.id
	      left join messages as answers
		   on messages.id=answers.up
	where (messages.hidden<$hide or messages.sender_id=$userId) and
	      (messages.disabled<$hide or messages.sender_id=$userId) and
	      messages.personal_id=$personal and messages.up=0
	      $grpFilter $topicFilter
	group by messages.id
	order by messages.sent desc",$limit,$offset,
       "select count(*)
	from messages
	where (messages.hidden<$hide or messages.sender_id=$userId) and
	      (messages.disabled<$hide or messages.sender_id=$userId) and
	      messages.personal_id=$personal and messages.up=0
	      $grpFilter $topicFilter");
      /* здесь нужно поменять, если будут другие ограничения на
	 просмотр TODO */
}

function create($row)
{
return newMessage($row);
}

}

class ForumListIterator
      extends LimitSelectIterator
{

function ForumListIterator($up,$limit=10,$offset=0)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$this->LimitSelectIterator(
       'Message',
	"select messages.id as id,body,subject,sent,sender_id,
	        messages.hidden as hidden,disabled,
		users.hidden as sender_hidden,
		login,gender,email,hide_email,rebe
	 from messages
	       left join users
		    on messages.sender_id=users.id
	 where (messages.hidden<$hide or sender_id=$userId) and
	       (messages.disabled<$hide or sender_id=$userId) and
	       up=$up
	 order by sent desc",$limit,$offset);
      /* здесь нужно поменять, если будут другие ограничения на
	 просмотр TODO */
}

function create($row)
{
return newMessage($row);
}

}

function getMessageById($id,$grp=0,$topic=0)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$result=mysql_query("select id,body,subject,topic_id,personal_id,sender_id,grp,
                     image_set,up,hidden,disabled
		     from messages
		     where id=$id and (hidden<$hide or sender_id=$userId) and
		     (disabled<$hide or sender_id=$userId)")
		    /* здесь нужно поменять, если будут другие ограничения на
		       просмотр TODO */
	     or die('Ошибка SQL при выборке сообщения');
return mysql_num_rows($result)>0 ? newMessage(mysql_fetch_assoc($result))
                                 : newGrpMessage($grp,
				                 array('topic_id' => $topic));
}

function getFullMessageById($id,$grp=0)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$result=mysql_query(
	"select messages.id as id,body,subject,grp,sent,topic_id,sender_id,
	        messages.hidden as hidden,disabled,
		users.hidden as sender_hidden,images.image_set as image_set,
		images.id as image_id,topics.name as topic_name,
		login,gender,email,hide_email,rebe
	 from messages
	       left join images
		    on messages.image_set=images.image_set
	       left join topics
		    on messages.topic_id=topics.id
	       left join users
		    on messages.sender_id=users.id
	 where (messages.hidden<$hide or sender_id=$userId) and
	       (messages.disabled<$hide or sender_id=$userId) and
	       messages.id=$id")
      /* здесь нужно поменять, если будут другие ограничения на
	 просмотр TODO */
 or die('Ошибка SQL при выборке сообщения');
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
		     (disabled<$hide or sender_id=$userId)")
		    /* здесь нужно поменять, если будут другие ограничения на
		       просмотр TODO */
	     or die('Ошибка SQL при выборке сообщения');
return mysql_num_rows($result)>0;
}
?>
