<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/bug.php');
require_once('lib/users.php');
require_once('lib/chat.php');

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

$result=mysql_query("select count(*)
		     from users
		     where last_chat+interval $chatTimeout minute>now()")
          or sqlbug('������ SQL ��� ��������� ���������� ������������� � ����');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function getLastChat($id)
{
$result=mysql_query("select unix_timestamp(last_chat)
		     from users
		     where id=$id")
	  or sqlbug('������ SQL ��� ��������� ������� ����������� � ����');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function updateLastChat()
{
global $userId,$realUserId,$allowGuestChat;

$id=$userId!=0 || !$allowGuestChat ? $userId : $realUserId;
if(!isChatLogged($id))
  {
  postChatLoginMessage($id);
  chatLogin($id);
  }
mysql_query("update users
             set last_chat=now()
	     where id=$id")
  or sqlbug('������ SQL ��� ���������� ������� ����������� � ����');
}

function clearLastChat($id)
{
mysql_query("update users
             set last_chat=0
	     where id=$id")
  or sqlbug('������ SQL ��� ������ ������� ����������� � ����');
}

function chatLogin($id)
{
mysql_query("update users
             set in_chat=1
	     where id=$id")
  or sqlbug('������ SQL ��� ������ � ���');
}

function chatLogout($id)
{
mysql_query("update users
             set in_chat=0
	     where id=$id")
  or sqlbug('������ SQL ��� ������ �� ����');
}

function isChatLogged($id)
{
$result=mysql_query("select in_chat
                     from users
		     where id=$id")
	  or sqlbug('������ SQL ��� �������� ����������� � ����');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0)<>0 : false;
}
?>
