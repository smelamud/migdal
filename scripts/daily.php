<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/users.php');
require_once('lib/journal.php');

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
mysql_query("update $sourceTable
	     set used=0
	     where $sourceField=0")
     or die(mysql_error());
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

function cleanupJournal()
{
global $unclosedSeqTimeout;

$result=mysql_query("select distinct seq
                     from journal
		     where sent+interval $unclosedSeqTimeout day<now()")
             or die(mysql_error());
while($row=mysql_fetch_assoc($result))
     if(!isSeqClosed($row['seq']))
       mysql_query('delete from journal
                    where seq='.$row['seq'])
            or die(mysql_error());
mysql_query('optimize table journal')
     or die(mysql_error());
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

/*tag('postings');
useLinks('postings','topic_id','topics','id');
deleteTagged('postings');*/

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

cleanupJournal();
}

dbOpen();
session(getShamesId());
cleanup();
dbClose();

?>
