<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/complains.php');
require_once('lib/users.php');

function deleteCond($table,$cond)
{
mysql_query("delete
             from $table
	     where $cond")
     or die(mysql_error());
mysql_query("optimize table $table")
     or die(mysql_error());
}

function cleanup()
{
global $sessionTimeout,$tmpTextTimeout,$redirTimeout,$anonVoteTimeout,
       $userVoteTimeout;

deleteCond('sessions',"last+interval $sessionTimeout hour<now()");
deleteCond('users','confirm_deadline is not null and confirm_deadline<now()');
deleteCond('tmp_texts',"last_access+interval $tmpTextTimeout hour<now()");
deleteCond('redirs',"last_access+interval $redirTimeout hour<now()");
deleteCond('votes',"user_id=0 and sent+interval $anonVoteTimeout hour<now()");
deleteCond('votes',"user_id<>0 and sent+interval $userVoteTimeout hour<now()");
}

function closeComplains()
{
$result=mysql_query(
	'select complains.id as complain_id,complains.type_id as type_id,
	        message_id,link,text,script_id,unix_timestamp(sent) as sent
	 from complains
	      left join messages
		   on complains.message_id=messages.id
	      left join users
		   on messages.sender_id=users.id
	      left join complain_actions
		   on complains.type_id=complain_actions.type_id
	 where closed is null and users.shames<>0
	       and complain_actions.automatic<>0');
if(!$result)
  die(mysql_error());
while($row=mysql_fetch_assoc($result))
     {
     $complain=newComplain($row['type_id'],$row);
     if($complain->getDeadline()!=0
        && $row['sent']+$complain->getDeadline()*60*60<time())
       {
       $forum=new ForumAnswer(array('body' => $row['text']));
       if(!$forum->store())
	 die(mysql_error());
       $script=getComplainScriptById($row['script_id']);
       $script->exec($complain);
       }
     }
}

dbOpen();
session(getShamesId());
cleanup();
closeComplains();
dbClose();

?>
