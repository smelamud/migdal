<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/journal.php');
require_once('lib/horisont.php');
require_once('lib/track.php');

function executeTrackQuery($query)
{
list($command,$table,$id)=explode(' ',$query);
$up=upById($table,$id);
updateTrackById($table,$id,track($id,$up==0 ? '' : trackById($table,$up)));
if($command=='tracks')
  updateTracks($table,$id,false);
}

function executeAction($host,$action)
{
global $replicationMaster;

if($replicationMaster)
  beginJournal();
foreach($action as $line)
       {
       $query=$line->getQuery();
       if($replicationMaster)
         $query=subJournalVars($host,$query);
       $query=jdecode($query);
       if(substr($query,0,5)=='track')
         executeTrackQuery($query);
       else
	 mysql_query($query)
	   or journalFailure('Error executing replicated query in seq '.
			     $line->getSeq().' id='.$line->getId().
			     ": $query");
       if($line->getResultTable()!='')
         {
	 $lastId=mysql_insert_id();
	 if(!$replicationMaster)
	   {
	   if($line->getResultId()!=$lastId)
	     journalFailure("Identifier shift detected in '".
	                     $line->getResultTable().
			     "': got $lastId instead of ".$line->getResultId());
	   }
	 else
	   {
	   setJournalVar($host,$line->getResultVar(),$lastId);
	   journal(jencode($query),$line->getResultTable(),$lastId);
	   }
	 }
       else
	 if($replicationMaster)
	   journal(jencode($query));
       }
if($replicationMaster)
  endJournal();
}

function replicate($host)
{
global $siteDomain,$maxImage,$replicationMaster;

if(isReplicationLocked($host))
  return;
$from=getHorisont($host,HOR_WE_KNOW);
setHorisont($host,$from,HOR_WE_KNOW); // Create horisont record if absent
$fd=fopen("http://$host/lib/replication.php?host=".urlencode($siteDomain)
                                         ."&from=$from",'r');
if(!$fd)
  return;
lockReplication($host);
$action=array();
while(!feof($fd))
     {
     $s=fgets($fd,$maxImage);
     if($s=='')
       continue;
     $line=parseJournalTransfer($s);
     if($line->getQuery()!='')
       $action[]=$line;
     else
       {
       executeAction($host,$action);
       $action=array();
       setHorisont($host,$line->getSeq(),HOR_WE_KNOW);
       updateReplicationLock($host);
       }
     }
unlockReplication($host);
fclose($fd);
if(!$replicationMaster && isJournalEmpty())
  duplicateDatabase();
}

dbOpen(!$replicationMaster);
endJournal();
session();
replicate($argv[1]);
beginJournal();
dbClose();
?>
