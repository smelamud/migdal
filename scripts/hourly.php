<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/complains.php');
require_once('lib/users.php');
require_once('lib/forums.php');
require_once('lib/complainscripts.php');
require_once('lib/postings.php');
require_once('lib/modbits.php');
require_once('lib/counters.php');
require_once('lib/sql.php');

function deleteCond($table,$cond)
{
sql("delete
     from $table
     where $cond",
    'deleteCond','delete');
sql("optimize table $table",
    'deleteCond','optimize');
}

function cleanup()
{
global $sessionTimeout,$tmpTextTimeout,$redirTimeout,$anonVoteTimeout,
       $userVoteTimeout;

deleteCond('counters_ip','expires<now()');
deleteCond('sessions',"last+interval $sessionTimeout hour<now()");
deleteCond('users','confirm_deadline is not null and confirm_deadline<now()');
deleteCond('tmp_texts',"last_access+interval $tmpTextTimeout hour<now()");
deleteCond('redirs',"last_access+interval $redirTimeout hour<now()");
deleteCond('votes',"user_id=0 and sent+interval $anonVoteTimeout hour<now()");
deleteCond('votes',"user_id<>0 and sent+interval $userVoteTimeout hour<now()");
}

function closeComplains()
{
global $replicationMaster;

if(!$replicationMaster)
  return;
$result=sql('select complains.id as id,complains.type_id as type_id,message_id,
	            link,text,script_id,unix_timestamp(sent) as sent
	     from complains
		  left join messages
		       on complains.message_id=messages.id
		  left join users
		       on messages.sender_id=users.id
		  left join complain_actions
		       on complains.type_id=complain_actions.type_id
	     where closed is null and no_auto=0 and users.shames<>0
		   and complain_actions.automatic<>0',
	    'closeComplains');
while($row=mysql_fetch_assoc($result))
     {
     $complain=newComplain($row['type_id'],$row);
     if($complain->getDeadline()!=0
        && $row['sent']+$complain->getDeadline()*60*60<time())
       {
       if($row['text']!='')
	 postForumAnswer($complain->getMessageId(),$row['text']);
       $script=getComplainScriptById($row['script_id']);
       $script->exec($complain);
       }
     }
}

function enableMessages()
{
global $messageEnableTimeout;

$result=sql('select id
	     from messages
	     where disabled<>0 and (modbits & '.MOD_MODERATE.')!=0 and
		   (modbits & '.MOD_HTML.")=0 and last_updated+
		   interval $messageEnableTimeout hour<now()",
	    'enableMessages');
while($row=mysql_fetch_assoc($result))
     setDisabledByMessageId($row['id'],0);
}

function rotateCounters()
{
global $replicationMaster;

if(!$replicationMaster)
  return;
$result=sql("select message_id,mode
	     from counters
	     where finished<now() and serial=0",
	    'rotateCounters');
while(list($message_id,$mode)=mysql_fetch_array($result))
     rotateCounter($message_id,$mode);
}

dbOpen();
session(getShamesId());
cleanup();
closeComplains();
enableMessages();
rotateCounters();
dbClose();
?>
