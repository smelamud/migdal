<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/bug.php');
require_once('lib/journal.php');
require_once('lib/profiling.php');

function dbOpen($replication=false)
{
global $dbLink,$dbHost,$dbName,$replicationDbName,$dbUser,$dbPassword;

if($dbLink>0)
  return;
$dbLink=mysql_connect($dbHost,$dbUser,$dbPassword)
            or sqlbug(__FUNCTION__.'().mysql_connect');
mysql_select_db(!$replication ? $dbName : $replicationDbName)
      or sqlbug(__FUNCTION__.'().mysql_select_db');
beginJournal();
if(!$replication)
  beginProfiling(POBJ_PAGE,$_SERVER['SCRIPT_NAME']);
}

function dbClose()
{
global $dbLink;

if($dbLink<=0)
  return;
endProfiling();
endJournal();
mysql_close($dbLink);
$dbLink=0;
}
?>
