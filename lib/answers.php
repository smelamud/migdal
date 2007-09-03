<?php
# @(#) $Id$

require_once('lib/bug.php');
require_once('lib/sql.php');
require_once('lib/entry-types.php');

function answerGet($id)
{
$result=sql("select answers,last_answer,last_answer_id,last_answer_user_id,
                    last_answer_guest_login
	     from entries
	     where id=$id",
	    __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_fetch_assoc($result) : array();
}

function answerSet($id,$answers,$last,$last_id,$last_user_id,$last_guest_login,
                   $incVersion=true)
{
if($id<=0)
  return;
sql("update entries
     set answers=$answers,last_answer='$last',last_answer_id=$last_id,
         last_answer_user_id=$last_user_id,
	 last_answer_guest_login='$last_guest_login'
     where id=$id or orig_id=$id",
    __FUNCTION__);
if($incVersion)
  incContentVersions('forums');
// id=$id or orig_id=$id требуется для жалоб, у которых не проставлено orig_id
}

function answerFindLastId($id)
{
$result=sql("select id,user_id,guest_login
	     from entries
	     where entry=".ENT_FORUM." and parent_id=$id
	           and (perms & 0x1111)=0x1111 and disabled=0
	     order by sent desc
	     limit 1",
	    __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_fetch_array($result) : array(0,0);
}

function answerUpdate($id)
{
$result=sql("select count(*) as answers,max(sent) as last_answer
	     from entries
	     where entry=".ENT_FORUM." and parent_id=$id
	           and (perms & 0x1111)=0x1111 and disabled=0",
	    __FUNCTION__);
list($answers,$last)=mysql_fetch_array($result);
list($last_id,$last_user_id,$last_guest_login)=answerFindLastId($id);
answerSet($id,$answers,$last,$last_id,$last_user_id,$last_guest_login);

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
     {
     list($last_id,$last_user_id,$last_guest_login)=answerFindLastId($id);
     answerSet($id,$answers,$last,$last_id,$last_user_id,$last_guest_login,
               false);
     }
}
