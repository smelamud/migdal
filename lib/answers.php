<?php
# @(#) $Id$

require_once('lib/bug.php');
require_once('lib/sql.php');
require_once('lib/entries.php');

function answerGet($id)
{
$result=sql("select answers,last_answer,last_answer_id
	     from entries
	     where id=$id",
	    __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_fetch_assoc($result) : array();
}

function answerSet($id,$answers,$last,$last_id)
{
if($id<=0)
  return;
sql("update entries
     set answers=$answers,last_answer='$last',last_answer_id=$last_id
     where id=$id or orig_id=$id",
    __FUNCTION__);
// id=$id or orig_id=$id требуется для жалоб, у которых не проставлено orig_id
}

function answerFindLastId($id)
{
$result=sql("select id
	     from entries
	     where entry=".ENT_FORUM." and parent_id=$id
	     order by sent desc
	     limit 1",
	    __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function answerUpdate($id)
{
$result=sql("select count(*) as answers,max(sent) as last_answer
	     from entries
	     where entry=".ENT_FORUM." and parent_id=$id
	           and (perms & 0x1111)=0x1111 and disabled=0",
	    __FUNCTION__);
list($answers,$last)=mysql_fetch_array($result);
answerSet($id,$answers,$last,answerFindLastId($id));

if($journalSeq!=0)
  journal('answers '.journalVar('entries',$id));
}

function answersRecalculate()
{
$result=sql("select parent_id,count(*) as answers,
		    max(sent) as last_answer
	     from entries
	     where entry=".ENT_FORUM." and (perms & 0x1111)=0x1111
	           and disabled=0
	     group by parent_id",
	    __FUNCTION__);
while(list($id,$answers,$last)=mysql_fetch_array($result))
     answerSet($id,$answers,$last,answerFindLastId($id));
}
