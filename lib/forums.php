<?php
# @(#) $Id$

require_once('lib/limitselect.php');
require_once('lib/messages.php');
require_once('lib/permissions.php');
require_once('lib/utils.php');
require_once('lib/bug.php');
require_once('lib/answers.php');

class ForumAnswer
      extends Message
{
var $message_id;
var $parent_id;

function getCorrespondentVars()
{
$list=Message::getCorrespondentVars();
array_push($list,'parent_id');
return $list;
}

function getWorldForumVars()
{
return array('message_id','parent_id');
}

function getAdminForumVars()
{
return array();
}

function getJencodedForumVars()
{
return array('message_id' => 'messages','parent_id' => 'messages');
}

function getNormalForum($isAdmin=false)
{
$normal=$this->collectVars($this->getWorldForumVars());
if($isAdmin)
  $normal=array_merge($normal,$this->collectVars($this->getAdminForumVars()));
return $normal;
}

function store()
{
global $userModerator;

$result=Message::store('message_id');
if(!$result)
  return $result;
$normal=$this->getNormalForum($userModerator);
if($this->id)
  {
  $result=mysql_query(makeUpdate('forums',$normal,array('id' => $this->id)));
  journal(makeUpdate('forums',
                     jencodeVars($normal,$this->getJencodedForumVars()),
		     array('id' => journalVar('forums',$this->id))));
  }
else
  {
  $result=mysql_query(makeInsert('forums',$normal));
  $this->id=mysql_insert_id();
  journal(makeInsert('forums',
                     jencodeVars($normal,$this->getJencodedForumVars())),
	  'forums',$this->id);
  }
return $result;
}

function getMessageId()
{
return $this->message_id;
}

function getParentId()
{
return $this->parent_id;
}

}

class ForumAnswerListIterator
      extends LimitSelectIterator
{

function ForumAnswerListIterator($parent_id,$limit=10,$offset=0)
{
$hide=messagesPermFilter(PERM_READ,'messages');
$this->LimitSelectIterator(
       'ForumAnswer',
	"select forums.id as id,message_id,stotext_id,body,sent,sender_id,
	        group_id,parent_id,perms,
		if((messages.perms & 0x1100)=0,1,0) as hidden,disabled,
		users.hidden as sender_hidden,
		login,gender,email,hide_email,rebe
	 from forums
	      left join messages
		   on forums.message_id=messages.id
	      left join stotexts
	           on stotexts.id=messages.stotext_id
	      left join users
		   on messages.sender_id=users.id
	 where $hide and parent_id=$parent_id
	 order by sent desc",$limit,$offset);
}

}

function getForumAnswerById($id,$parent_id=0,$quote='',$quoteWidth=75)
{
$hide=messagesPermFilter(PERM_READ);
$result=mysql_query("select forums.id as id,message_id,stotext_id,body,
                            sender_id,group_id,image_set,parent_id,perms,
			    if((perms & 0x1100)=0,1,0) as hidden,disabled
		     from forums
		          left join messages
			       on forums.message_id=messages.id
	                  left join stotexts
	                       on stotexts.id=messages.stotext_id
		     where forums.id=$id and $hide")
	  or sqlbug('Ошибка SQL при выборке сообщения в форуме');
if(mysql_num_rows($result)>0)
  return new ForumAnswer(mysql_fetch_assoc($result));
else
  {
  global $rootForumPerms;

  if($parent_id>0)
    {
    $perms=getPermsById('messages',$parent_id);
    $group_id=$perms->getGroupId();
    }
  else
    $group_id=0;
  return new ForumAnswer(array('parent_id' => $parent_id,
		               'body'      => $quote!=''
			                       ? getQuote($quote,$quoteWidth)
			                       : '',
			       'group_id'  => $group_id,
			       'perms'     => $rootForumPerms));
  }
}

function getForumAnswerAuthorBySent($parent_id,$sent)
{
$hide=messagesPermFilter(PERM_READ,'messages');
$result=mysql_query(
	"select forums.id as id,message_id,sender_id,
	        users.hidden as sender_hidden,
		login,gender,email,hide_email,rebe
	 from forums
	      left join messages
		   on forums.message_id=messages.id
	      left join users
		   on messages.sender_id=users.id
	 where $hide and forums.parent_id=$parent_id and
	       unix_timestamp(sent)=$sent")
 or sqlbug('Ошибка SQL при выборке автора сообщения в форуме');
return new ForumAnswer(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                                 : array());
}

function getFullForumAnswerById($id)
{
$hide=messagesPermFilter(PERM_READ,'messages');
$result=mysql_query(
	"select forums.id as id,message_id,stotext_id,body,sent,sender_id,
	        group_id,perms,if((messages.perms & 0x1100)=0,1,0) as hidden,
		disabled,users.hidden as sender_hidden,
		images.image_set as image_set,images.id as image_id,
		images.small_x<images.large_x or
		images.small_y<images.large_y as has_large_image,
		login,gender,email,hide_email,rebe
	 from forums
	      left join messages
		   on forums.message_id=messages.id
	      left join stotexts
	           on stotexts.id=messages.stotext_id
	      left join images
		   on stotexts.image_set=images.image_set
	      left join users
		   on messages.sender_id=users.id
	 where $hide and forums.id=$id")
 or sqlbug('Ошибка SQL при выборке сообщения в форуме');
return new ForumAnswer(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                                 : array());
}

function postForumAnswer($message_id,$body,$sender_id=0)
{
global $rootForumPerms;

if($parent_id>0)
  {
  $perms=getPermsById('messages',$message_id);
  $group_id=$perms->getGroupId();
  }
else
  $group_id=0;
$forum=new ForumAnswer(array('body'      => $body,
                             'parent_id' => $message_id,
    			     'sender_id' => $sender_id,
			     'group_id'  => $group_id,
			     'perms'     => $rootForumPerms));
return $forum->store();
}

function getForumAnswerInfoByMessageId($message_id)
{
global $userId;

if($userId<=0)
  return answerGet($message_id)
else
  {
  $hide=messagesPermFilter(PERM_READ,'messages');
  $result=mysql_query("select count(*) as answers,max(sent) as last_answer
                       from forums
		            left join messages
			         on forums.message_id=messages.id
                       where parent_id=$message_id and $hide")
            or sqlbug("Ошибка SQL при получении числа ответов");
  return mysql_num_rows($result)>0 ? mysql_fetch_assoc($result) : array();
  }
}
?>
