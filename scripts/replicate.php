<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/journal.php');

function executeAction($action)
{
foreach($action as $line)
       echo $line->getQuery();
}

function replicate($host)
{
global $siteDomain,$maxImage;

$from=getHorisont($host,HOR_WE_KNOW);
$fd=fopen("http://$host/lib/replication.php?host=".urlencode($host)
                                         ."&from=$from",'r');
if(!$fd)
  return;
$action=array();
while(!feof($fd))
     {
     $line=parseJournalTransfer(fgets($fd,$maxImage));
     if($line->getQuery()!='')
       $action[]=$line;
     else
       {
       executeAction($action);
       $action=array();
       }
     }
fclose($fd);
}

dbOpen();
session();
replicate($argv[1]);
dbClose();
?>
