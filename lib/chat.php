<?php
# @(#) $Id$

require_once('lib/sendertag.php');
require_once('lib/text.php');
require_once('lib/selectiterator.php');

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

function getSentTimeView()
{
return date('H:i:s',$this->sent);
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
var $index;

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
$this->index=$this->getCount()-1;
}

function next()
{
if($this->index<0)
  return 0;
else
  {
  mysql_data_seek($this->getResult(),$this->index--);
  return parent::next();
  }
}

}
?>
