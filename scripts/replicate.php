<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/journal.php');
require_once('lib/track.php');

function executeTrackQuery($query)
{
list($command,$table,$id)=explode(' ',$query);
$up=upById($table,$id);
updateTrackById($table,$id,track($id,$up==0 ? '' : trackById($table,$up)));
if($command=='tracks')
  updateTracks($table,$id,false);
}

function executeAction($action)
{
foreach($action as $line)
       {
       $query=jdecode($line->getQuery());
       if(substr($query,0,5)=='track')
         executeTrackQuery($query);
       else
	 mysql_query($query)
	   or journalFailure('Error executing replicated query in seq '.
			     $line->getSeq().' id='.$line->getId().
			     ": $query");
       }
}

function replicate($host)
{
global $siteDomain,$maxImage;

if(isReplicationLocked($host))
  return;
$from=getHorisont($host,HOR_WE_KNOW);
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
       executeAction($action);
       $action=array();
       setHorisont($host,$line->getSeq(),HOR_WE_KNOW);
       updateReplicationLock($host);
       }
     }
unlockReplication($host);
fclose($fd);
}

dbOpen();
session();
replicate($argv[1]);
dbClose();
?>
