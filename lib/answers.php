<?php
# @(#) $Id$

require_once('lib/bug.php');

function answerAdded($message_id)
{
mysql_query("update messages
             set answers=answers+1
	     where id=$message_id")
  or sqlbug('������ SQL ��� ���������� ������');
}

function answerRemoved($message_id)
{
mysql_query("update messages
             set answers=answers-1
	     where id=$message_id")
  or sqlbug('������ SQL ��� �������� ������');
}

function answerHidden($message_id)
{
mysql_query("update messages
             set answers=answers-1,hidden_answers=hidden_answers+1
	     where id=$message_id")
  or sqlbug('������ SQL ��� ������� ������');
}

function answerShown($message_id)
{
mysql_query("update messages
             set answers=answers+1,hidden_answers=hidden_answers-1
	     where id=$message_id")
  or sqlbug('������ SQL ��� �������� ������');
}

function answerSet($message_id,$answers)
{
mysql_query("update messages
             set answers=$answers
	     where id=$message_id")
  or sqlbug('������ SQL ��� ��������� ����� �������� �������');
}

function answerSetAll($message_id,$answers)
{
mysql_query("update messages
             set hidden_answers=$answers-answers
	     where id=$message_id")
  or sqlbug('������ SQL ��� ��������� ����� ���� �������');
}

function answersRecalculate()
{
$result=mysql_query("select parent_id,count(*) as answers
                     from forums
		          left join messages
			       on forums.message_id=messages.id
		     where (perms & 0x1111)=0x1111 and disabled=0
		     group by parent_id")
          or sqlbug('������ SQL ��� �������� ����� �������� �������');
while(list($id,$answers)=mysql_fetch_array($result))
     answerSet($id,$answers);

$result=mysql_query("select parent_id,count(*) as answers
                     from forums
		     group by parent_id")
          or sqlbug('������ SQL ��� �������� ����� ���� �������');
while(list($id,$answers)=mysql_fetch_array($result))
     answerSetAll($id,$answers);
}
