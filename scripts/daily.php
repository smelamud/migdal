<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/users.php');
require_once('lib/journal.php');
require_once('lib/answers.php');
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

function tag($table)
{
sql("update $table
     set used=1",
    'tag');
}

function useLinks($sourceTable,$sourceField,$destTable,$destField)
{
$result=sql("select $destField
	     from $destTable
	     where $destField<>0",
	    'useLinks','select');
sql("update $sourceTable
     set used=0
     where $sourceField=0",
    'useLinks','clear_marks');
while($row=mysql_fetch_array($result))
     sql("update $sourceTable
	  set used=0
	  where $sourceField=".$row[0],
	 'useLinks','mark');
}

function deleteTagged($table)
{
deleteCond($table,'used=1');
}

function cleanupJournal()
{
global $unclosedSeqTimeout;

$result=sql("select distinct seq
	     from journal
	     where sent+interval $unclosedSeqTimeout day<now()",
	    'cleanupJournal','select_old');
while($row=mysql_fetch_assoc($result))
     if(!isSeqClosed($row['seq']))
       sql('delete from journal
	    where seq='.$row['seq'],
	   'cleanupJournal','delete');
sql('optimize table journal',
    'cleanupJournal','optimize');
}

function cleanup()
{
global $disabledMessageTimeout,$journalVarTimeout,$statisticsTimeout;

if($disabledMessageTimeout>=0)
  deleteCond('messages',"disabled<>0 and
			 sent+interval $disabledMessageTimeout day<now()");
deleteCond('journal_vars',"last_read+interval $journalVarTimeout day<now()");
if($statisticsTimeout>0)
  deleteCond('logs',"sent+interval $statisticsTimeout day<now()");

tag('images');
useLinks('images','image_set','stotexts','image_set');
useLinks('images','image_set','stotexts','large_imageset');
deleteTagged('images');

tag('complains');
useLinks('complains','message_id','messages','id');
deleteTagged('complains');

tag('counters');
useLinks('counters','message_id','messages','id');
deleteTagged('counters');

tag('forums');
useLinks('forums','message_id','messages','id');
deleteTagged('forums');

tag('forums');
useLinks('forums','parent_id','messages','id');
deleteTagged('forums');

tag('messages');
useLinks('messages','id','complains','message_id');
useLinks('messages','id','forums','message_id');
useLinks('messages','id','postings','message_id');
deleteTagged('messages');

tag('messages');
useLinks('messages','stotext_id','stotexts','id');
deleteTagged('messages');

tag('messages');
useLinks('messages','up','messages','id');
deleteTagged('messages');

tag('packages');
useLinks('packages','message_id','messages','id');
deleteTagged('packages');

tag('postings');
useLinks('postings','message_id','messages','id');
deleteTagged('postings');

tag('postings');
useLinks('postings','topic_id','topics','id');
deleteTagged('postings');

tag('stotext_images');
useLinks('stotext_images','stotext_id','stotexts','id');
deleteTagged('stotext_images');

tag('stotexts');
useLinks('stotexts','id','messages','stotext_id');
useLinks('stotexts','id','topics','stotext_id');
deleteTagged('stotexts');

tag('topics');
useLinks('topics','stotext_id','stotexts','id');
deleteTagged('topics');
answersRecalculate();

cleanupJournal();
}

dbOpen();
session(getShamesId());
cleanup();
dbClose();
?>
