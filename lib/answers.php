<?php
# @(#) $Id$

require_once('lib/bug.php');

function answerLastSet($message_id,$last)
{
mysql_query("update messages
             set last_answer='$last'
	     where id=$message_id")
  or sqlbug('Ошибка SQL при установке даты последнего ответа');
}

function answerUpdate($message_id)
{
$result=mysql_query("select count(*) as answers,max(sent) as last_answer
                     from forums
		          left join messages
			       on forums.message_id=messages.id
		     where parent_id=$message_id and (perms & 0x1111)=0x1111
		           and disabled=0")
          or sqlbug('Ошибка SQL при подсчете числа открытых ответов');
list($answers,$last)=mysql_fetch_array($result);
answerSet($message_id,$answers);
answerLastSet($message_id,$last);

$result=mysql_query("select count(*) as answers
                     from forums
		     where parent_id=$message_id")
          or sqlbug('Ошибка SQL при подсчете числа всех ответов');
list($answers)=mysql_fetch_array($result);
answerSetAll($message_id,$answers);

if($journalSeq!=0)
  journal('answers '.journalVar('messages',$message_id));
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
