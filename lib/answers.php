<?php
# @(#) $Id$

require_once('lib/bug.php');

function answerLastSet($message_id,$last)
{
mysql_query("update messages
             set last_answer=$last
	     where id=$message_id")
  or sqlbug('Ошибка SQL при установке даты последнего ответа');
}

function answerLastRecalculate($message_id)
{
$result=mysql_query("select max(sent)
                     from forums
		          left join messages
			       on forums.message_id=messages.id
		     where parent_id=$message_id and (perms & 0x1111)=0x1111
		           and disabled=0")
          or sqlbug('Ошибка SQL при выяснении даты последнего ответа');
$last=mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
answerLastSet($message_id,$last);
}

function answerAdded($message_id)
{
mysql_query("update messages
             set answers=answers+1
	     where id=$message_id")
  or sqlbug('Ошибка SQL при добавлении ответа');
answerLastRecalculate($message_id);
}

function answerRemoved($message_id)
{
mysql_query("update messages
             set answers=answers-1
	     where id=$message_id")
  or sqlbug('Ошибка SQL при удалении ответа');
answerLastRecalculate($message_id);
}

function answerHidden($message_id)
{
mysql_query("update messages
             set answers=answers-1,hidden_answers=hidden_answers+1
	     where id=$message_id")
  or sqlbug('Ошибка SQL при скрытии ответа');
answerLastRecalculate($message_id);
}

function answerShown($message_id)
{
mysql_query("update messages
             set answers=answers+1,hidden_answers=hidden_answers-1
	     where id=$message_id")
  or sqlbug('Ошибка SQL при открытии ответа');
answerLastRecalculate($message_id);
}

function answerSet($message_id,$answers)
{
mysql_query("update messages
             set answers=$answers
	     where id=$message_id")
  or sqlbug('Ошибка SQL при установке числа открытых ответов');
}

function answerSetAll($message_id,$answers)
{
mysql_query("update messages
             set hidden_answers=$answers-answers
	     where id=$message_id")
  or sqlbug('Ошибка SQL при установке числа всех ответов');
}

function answerGet($message_id)
{
$result=mysql_query("select answers,last_answer
                     from messages
		     where id=$message_id")
          or sqlbug("Ошибка SQL при получении информации об ответах");
return mysql_num_rows($result)>0 ? mysql_fetch_assoc($result) : array();
}

function answersRecalculate()
{
$result=mysql_query("select parent_id,count(*) as answers,
                            max(sent) as last_answer
                     from forums
		          left join messages
			       on forums.message_id=messages.id
		     where (perms & 0x1111)=0x1111 and disabled=0
		     group by parent_id")
          or sqlbug('Ошибка SQL при подсчете числа открытых ответов');
while(list($id,$answers,$last)=mysql_fetch_array($result))
     {
     answerSet($id,$answers);
     answerLastSet($id,$last);
     }

$result=mysql_query("select parent_id,count(*) as answers
                     from forums
		     group by parent_id")
          or sqlbug('Ошибка SQL при подсчете числа всех ответов');
while(list($id,$answers)=mysql_fetch_array($result))
     answerSetAll($id,$answers);
}
