<?php
# @(#) $Id$

require_once('lib/sendertag.php');
require_once('lib/text.php');
require_once('lib/selectiterator.php');
require_once('lib/users.php');
require_once('lib/sql.php');

class ChatMessage
      extends SenderTag
{
var $id;
var $private_id;
var $sent;
var $text;

function ChatMessage($row)
{
$this->SenderTag($row);
}

function getId()
{
return $this->id;
}

function getPrivateId()
{
return $this->private_id;
}

function getSent()
{
return $this->sent;
}

function getText()
{
return $this->text;
}

function getHTMLText()
{
return stotextToHTML(TF_MAIL,$this->text);
}

}

class ChatMessageListIterator
      extends SelectIterator
{

function ChatMessageListIterator($limit=20)
{
global $userId;

$this->SelectIterator('ChatMessage',
                      "select chat_messages.id as id,login,text,
		              unix_timestamp(sent) as sent
		       from chat_messages
		            left join users
			         on chat_messages.sender_id=users.id
		       where private_id=0 or private_id=$userId
		       order by sent desc
		       limit $limit");
}

}

function postChatAdminMessage($message)
{
return sql("insert into chat_messages(sender_id,text)
	    values(".getShamesId().",'".
	    addslashes(htmlspecialchars($message,ENT_QUOTES))."')",
	   'postChatAdminMessage');
}

function postChatLoginMessage($id)
{
$s=getUserGenderById($id)=='mine' ? 'зашел' : 'зашла';
postChatAdminMessage("В чат $s _".getUserLoginById($id).'_');
}

function postChatSwitchMessage($id,$prevId)
{
$s=getUserGenderById($prevId)=='mine' ? 'переименовался' : 'переименовалась';
postChatAdminMessage('_'.getUserLoginById($prevId)."_ $s в _".
                         getUserLoginById($id).'_');
}

function postChatLogoutMessage($id)
{
$s=getUserGenderById($id)=='mine' ? 'покинул' : 'покинула';
postChatAdminMessage("Нас $s _".getUserLoginById($id).'_');
}
?>
