<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/forums.php');
require_once('lib/opscript.php');
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

function tag($table)
{
mysql_query("update $table
             set used=1")
     or die(mysql_error());
}

function useLinks($sourceTable,$sourceField,$destTable,$destField)
{
$result=mysql_query("select $destField
                     from $destTable
		     where $destField<>0");
if(!$result)
  die(mysql_error());
while($row=mysql_fetch_array($result))
     mysql_query("update $sourceTable
                  set used=0
		  where $sourceField=".$row[0])
          or die(mysql_error());
}

function deleteTagged($table)
{
mysql_query("delete
             from $table
	     where used=1")
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

tag('images');
useLinks('images','image_set','stotexts','image_set');
useLinks('images','image_set','stotexts','large_imageset');
deleteTagged('images');
}

function closeComplains()
{
$result=mysql_query(
	'select complains.id as complain_id,message_id,link,text,script,
		users.id as shames_id
	 from complains
	      left join messages
		   on complains.message_id=messages.id
	      left join users
		   on messages.sender_id=users.id
	      left join complain_types
		   on complain_types.id=complains.type_id
	      left join complain_actions
		   on complains.type_id=complain_actions.type_id
	      left join complain_scripts
		   on complain_scripts.type_ident=complain_types.ident
	 where closed is null and users.shames<>0
	       and complain_types.deadline<>0
	       and messages.sent+interval complain_types.deadline hour<now()
	       and complain_actions.automatic<>0');
if(!$result)
  die(mysql_error());
while($row=mysql_fetch_assoc($result))
     {
     $forum=new ForumAnswer(array('body' => $row['text']));
     if(!$forum->store())
       die(mysql_error());
     opScript($row['script'],$row);
     }
}

dbOpen();
session(getShamesId());
cleanup();
closeComplains();
dbClose();

?>
