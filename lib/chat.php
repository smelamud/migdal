<?php
# @(#) $Id$

require_once('lib/usertag.php');
require_once('lib/mtext-html.php');
require_once('lib/selectiterator.php');
require_once('lib/users.php');
require_once('lib/sql.php');

class ChatMessage
      extends UserTag
{
var $id;
var $guest_login;
var $sender_id;
var $private_id;
var $sent;
var $text;
var $text_xml;

function __construct($row)
{
parent::__construct($row);
}

function getId()
{
return $this->id;
}

function getGuestLogin()
{
return $this->guest_login;
}

function getSenderId()
{
return $this->sender_id;
}

function getUserId()
{
return $this->getSenderId();
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

function getTextXML()
{
return $this->text_xml;
}

function getTextHTML()
{
return mtextToHTML($this->getTextXML(),MTEXT_LINE);
}

}

class ChatMessageListIterator
        extends SelectIterator {

    public function __construct($later = 0, $earlier = 0) {
        global $userId;

        $filter = "(private_id=0 or private_id=$userId)";
        if ($later > 0)
            $filter .= " and unix_timestamp(sent)>=$later";
        if ($earlier > 0)
            $filter .= " and unix_timestamp(sent)<$earlier";
        parent::__construct(
                'ChatMessage',
                "select chat_messages.id as id,guest_login,sender_id,
                        login,gender,email,hide_email,
                        users.hidden as user_hidden,
                        users.guest as user_guest,text,text_xml,
                        unix_timestamp(sent) as sent
                 from chat_messages
                      left join users
                           on chat_messages.sender_id=users.id
                 where $filter
                 order by sent desc");
    }

}
