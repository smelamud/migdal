<?php
# @(#) $Id$

require_once('lib/limitselect.php');
require_once('lib/messages.php');
require_once('lib/utils.php');
require_once('lib/bug.php');

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
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$this->LimitSelectIterator(
       'ForumAnswer',
	"select forums.id as id,message_id,stotext_id,body,sent,sender_id,
	        parent_id,messages.hidden as hidden,disabled,
		users.hidden as sender_hidden,
		login,gender,email,hide_email,rebe
	 from forums
	      left join messages
		   on forums.message_id=messages.id
	      left join stotexts
	           on stotexts.id=messages.stotext_id
	      left join users
		   on messages.sender_id=users.id
	 where (messages.hidden<$hide or sender_id=$userId) and
	       (messages.disabled<$hide or sender_id=$userId) and
	       parent_id=$parent_id
	 order by sent desc",$limit,$offset);
      /* здесь нужно поменять, если будут другие ограничения на
	 просмотр TODO */
}

}

function getForumAnswerById($id,$parent_id=0,$quote='',$quoteWidth=75)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$result=mysql_query("select forums.id as id,message_id,stotext_id,body,
                            sender_id,image_set,parent_id,hidden,disabled
		     from forums
		          left join messages
			       on forums.message_id=messages.id
	                  left join stotexts
	                       on stotexts.id=messages.stotext_id
		     where forums.id=$id and (hidden<$hide or sender_id=$userId)
		           and (disabled<$hide or sender_id=$userId)")
		    /* здесь нужно поменять, если будут другие ограничения на
		       просмотр TODO */
	  or sqlbug('Ошибка SQL при выборке сообщения в форуме');
return new ForumAnswer(mysql_num_rows($result)>0
                       ? mysql_fetch_assoc($result)
                       : array('parent_id' => $parent_id,
		               'body'      => $quote!=''
			                       ? getQuote($quote,$quoteWidth)
			                       : ''));
}

function getForumAnswerAuthorBySent($parent_id,$sent)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$result=mysql_query(
	"select forums.id as id,message_id,sender_id,
	        users.hidden as sender_hidden,
		login,gender,email,hide_email,rebe
	 from forums
	      left join messages
		   on forums.message_id=messages.id
	      left join users
		   on messages.sender_id=users.id
	 where (messages.hidden<$hide or sender_id=$userId) and
	       (messages.disabled<$hide or sender_id=$userId) and
	       forums.parent_id=$parent_id and
	       unix_timestamp(sent)=$sent")
      /* здесь нужно поменять, если будут другие ограничения на
	 просмотр TODO */
 or sqlbug('Ошибка SQL при выборке автора сообщения в форуме');
return new ForumAnswer(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                                 : array());
}

function getFullForumAnswerById($id)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$result=mysql_query(
	"select forums.id as id,message_id,stotext_id,body,sent,sender_id,
	        messages.hidden as hidden,disabled,
		users.hidden as sender_hidden,images.image_set as image_set,
		images.id as image_id,
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
	 where (messages.hidden<$hide or sender_id=$userId) and
	       (messages.disabled<$hide or sender_id=$userId) and
	       forums.id=$id")
      /* здесь нужно поменять, если будут другие ограничения на
	 просмотр TODO */
 or sqlbug('Ошибка SQL при выборке сообщения в форуме');
return new ForumAnswer(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                                 : array());
}

function postForumAnswer($message_id,$body,$sender_id=0)
{
$forum=new ForumAnswer(array('body'      => $body,
                             'parent_id' => $message_id,
    			     'sender_id' => $sender_id));
return $forum->store();
}
?>
