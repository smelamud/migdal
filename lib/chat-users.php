<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/bug.php');
require_once('lib/users.php');
require_once('lib/chat.php');
require_once('lib/sql.php');

class ChatUsersIterator
      extends SelectIterator
{

function ChatUsersIterator()
{
global $chatTimeout;

$this->SelectIterator('User',
                      "select id,login,gender,email,hide_email,hidden
		       from users
		       where last_chat+interval $chatTimeout minute>now()
		       order by login_sort");
}

}

function getChatUsersCount()
{
global $chatTimeout;

$result=sql("select count(*)
	     from users
	     where last_chat+interval $chatTimeout minute>now()",
	    'getChatUsersCount');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function getLastChat($id)
{
$result=sql("select unix_timestamp(last_chat)
	     from users
	     where id=$id",
	    'getLastChat');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function updateLastChat()
{
global $userId,$realUserId,$allowGuestChat;

$id=$userId!=0 || !$allowGuestChat ? $userId : $realUserId;
if($id!=0 && !isChatLogged($id))
  {
  postChatLoginMessage($id);
  chatLogin($id);
  }
sql("update users
     set last_chat=now()
     where id=$id",
    'updateLastChat');
}

function clearLastChat($id)
{
sql("update users
     set last_chat=0
     where id=$id",
    'clearLastChat');
}

function chatLogin($id)
{
sql("update users
     set in_chat=1
     where id=$id",
    'chatLogin');
}

function chatLogout($id)
{
sql("update users
     set in_chat=0
     where id=$id",
    'chatLogout');
}

function isChatLogged($id)
{
$result=sql("select in_chat
	     from users
	     where id=$id",
	    'isChatLogged');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0)<>0 : false;
}
?>
