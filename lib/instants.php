<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/limitselect.php');
require_once('lib/messages.php');

class InstantMessage
      extends Message
{
var $message_id;
var $shown;
var $confirmed;
var $recipient_id;

function getWorldInstantVars()
{
return array('message_id','shown','confirmed','recipient_id');
}

function getAdminInstantVars()
{
return array();
}

function getJencodedInstantVars()
{
return array('message_id' => 'messages','recipient_id' => 'users');
}

function getNormalInstant($isAdmin=false)
{
$normal=$this->collectVars($this->getWorldInstantVars());
if($isAdmin)
  $normal=array_merge($normal,$this->collectVars($this->getAdminInstantVars()));
return $normal;
}

function store()
{
global $userModerator;

$result=Message::store('message_id');
if(!$result)
  return $result;
$normal=$this->getNormalInstant($userModerator);
if($this->id)
  {
  $result=mysql_query(makeUpdate('instants',$normal,array('id' => $this->id)));
  journal(makeUpdate('instants',
                     jencodeVars($normal,$this->getJencodedInstantVars()),
		     array('id' => $this->id)));
  }
else
  {
  $result=mysql_query(makeInsert('instants',$normal));
  $this->id=mysql_insert_id();
  journal(makeInsert('instants',
                     jencodeVars($normal,$this->getJencodedInstantVars())),
          'instants',$this->id);
  }
return $result;
}

function getMessageId()
{
return $this->message_id;
}

function getShown()
{
return $this->shown;
}

function isConfirmed()
{
return $this->confirmed;
}

function getRecipientId()
{
return $this->recipient_id;
}

}

class InstantMessageListIterator
      extends SelectIterator
{

function InstantMessageListIterator()
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$this->SelectIterator(
       'InstantMessage',
       "select instants.id as id,message_id,stotext_id,body,sent,sender_id,
	       messages.hidden as hidden,disabled,
	       users.hidden as sender_hidden,
	       login,gender,email,hide_email,rebe
	from instants
	     left join messages
		  on instants.message_id=messages.id
	     left join stotexts
		  on stotexts.id=messages.stotext_id
	     left join users
		  on messages.sender_id=users.id
	where (messages.hidden<$hide or sender_id=$userId) and
	      (messages.disabled<$hide or sender_id=$userId) and
	      recipient_id=$userId and shown>0
	order by sent desc");
}

}

class ConfirmInstantMessageListIterator
      extends SelectIterator
{

function ConfirmInstantMessageListIterator()
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$this->SelectIterator(
       'InstantMessage',
       "select instants.id as id,message_id,sent,recipient_id,
	       messages.hidden as hidden,disabled,
	       users.hidden as sender_hidden,
	       login,gender,email,hide_email,rebe
	from instants
	     left join messages
		  on instants.message_id=messages.id
	     left join users
		  on messages.recipient_id=users.id
	where (messages.hidden<$hide or recipient_id=$userId) and
	      (messages.disabled<$hide or recipient_id=$userId) and
	      sender_id=$userId and confirmed=0
	order by sent desc");
}

}

class ReceivedInstantMessageListIterator
      extends SelectIterator
{

function ReceivedInstantMessageListIterator()
{
global $userId,$userModerator,$instantViews;

$hide=$userModerator ? 2 : 1;
$this->SelectIterator(
       'InstantMessage',
       "select instants.id as id,message_id,sent,recipient_id,
	       messages.hidden as hidden,disabled,
	       users.hidden as sender_hidden,
	       login,gender,email,hide_email,rebe
	from instants
	     left join messages
		  on instants.message_id=messages.id
	     left join users
		  on messages.recipient_id=users.id
	where (messages.hidden<$hide or recipient_id=$userId) and
	      (messages.disabled<$hide or recipient_id=$userId) and
	      sender_id=$userId and shown<$instantViews and shown>0
	order by sent desc");
}

}

/*function getForumAnswerById($id,$up=0)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$result=mysql_query("select forums.id as id,stotext_id,body,sender_id,
                            image_set,up,hidden,disabled
		     from forums
		          left join messages
			       on forums.message_id=messages.id
	                  left join stotexts
	                       on stotexts.id=messages.stotext_id
		     where forums.id=$id and (hidden<$hide or sender_id=$userId)
		           and (disabled<$hide or sender_id=$userId)")
	     or die('Ошибка SQL при выборке сообщения в форуме');
return new ForumAnswer(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                                 : array('up' => $up));
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
 or die('Ошибка SQL при выборке сообщения в форуме');
return new ForumAnswer(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                                 : array());
}*/
?>
