<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/journal.php');

function replicate($host)
{
global $siteDomain,$maxImage;

$from=getHorisont($host,HOR_WE_KNOW);
$fd=fopen("http://$host/lib/replication.php?host=".urlencode($host)
                                         ."&from=$from",'r');
if(!$fd)
  return;
while(!feof($fd))
     echo fgets($fd,$maxImage);
fclose($fd);
}

dbOpen();
session();
replicate($argv[1]);
dbClose();
?>
