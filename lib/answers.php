<?php
# @(#) $Id$

require_once('lib/bug.php');
require_once('lib/sql.php');

function answerLastSet($message_id,$last)
{
sql("update messages
     set last_answer='$last'
     where id=$message_id",
    'answerLastSet');
}

function answerUpdate($message_id)
{
$result=sql("select count(*) as answers,max(sent) as last_answer
	     from forums
		  left join messages
		       on forums.message_id=messages.id
	     where parent_id=$message_id and (perms & 0x1111)=0x1111
		   and disabled=0",
	    'answerUpdate','open_answers');
list($answers,$last)=mysql_fetch_array($result);
answerSet($message_id,$answers);
answerLastSet($message_id,$last);

$result=sql("select count(*) as answers
	     from forums
	     where parent_id=$message_id",
	    'answerUpdate','all_answers');
list($answers)=mysql_fetch_array($result);
answerSetAll($message_id,$answers);

if($journalSeq!=0)
  journal('answers '.journalVar('messages',$message_id));
}

function answerSet($message_id,$answers)
{
sql("update messages
     set answers=$answers
     where id=$message_id",
    'answerSet');
}

function answerSetAll($message_id,$answers)
{
sql("update messages
     set hidden_answers=$answers-answers
     where id=$message_id",
    'answerSetAll');
}

function answerGet($message_id)
{
$result=sql("select answers,last_answer
	     from messages
	     where id=$message_id",
	    'answerGet');
return mysql_num_rows($result)>0 ? mysql_fetch_assoc($result) : array();
}

function answersRecalculate()
{
$result=sql("select parent_id,count(*) as answers,
		    max(sent) as last_answer
	     from forums
		  left join messages
		       on forums.message_id=messages.id
	     where (perms & 0x1111)=0x1111 and disabled=0
	     group by parent_id",
	    'answersRecalculate','open_answers');
while(list($id,$answers,$last)=mysql_fetch_array($result))
     {
     answerSet($id,$answers);
     answerLastSet($id,$last);
     }

$result=sql("select parent_id,count(*) as answers
	     from forums
	     group by parent_id",
	    'answersRecalculate','all_answers');
while(list($id,$answers)=mysql_fetch_array($result))
     answerSetAll($id,$answers);
}
