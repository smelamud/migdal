<?php
# @(#) $Id$

function session($sessionId)
{
global $userId,$userAdminUsers,$userAdminTopics,$userModerator;

settype($sessionId,'integer');
if(!$sessionId)
  {
  $userId=-1;
  return;
  }
$result=mysql_query("select user_id from sessions where sid=$sessionId")
             or die('������ SQL ��� ������� ������');
if(mysql_num_rows($result)<=0)
  {
  SetCookie('sessionid');
  $userId=-1;
  }
else
  {
  $userId=mysql_result($result,0,0);
  $rights=mysql_query("select admin_users,admin_topics,moderator
                       from users
		       where id=$userId")
               or die('������ SQL ��� ��������� ���� ������������');
  list($userAdminUsers,$userAdminTopics,$userModerator)=mysql_fetch_row($rights);
  mysql_query("update sessions set last=null where sid=$sessionId")
       or die('������ SQL ��� ���������� TIMESTAMP ������');
  SetCookie('sessionid',$sessionId,time()+7200);
  }
}
?>
