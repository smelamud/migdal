<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/bug.php');
require_once('lib/journal.php');
require_once('lib/profiling.php');

function dbOpen($replication=false)
{
global $dbLink,$dbHost,$dbName,$replicationDbName,$dbUser,$dbPassword,
       $dbCharset;

if($dbLink>0)
  return;
$dbLink=mysql_connect($dbHost,$dbUser,$dbPassword)
            or sqlbug(__FUNCTION__.'().mysql_connect');
mysql_select_db(!$replication ? $dbName : $replicationDbName)
      or sqlbug(__FUNCTION__.'().mysql_select_db');
mysql_query("set names '$dbCharset'")
      or sqlbug(__FUNCTION__.'().set_names');
beginJournal();
if(!$replication)
  beginProfiling(POBJ_PAGE,$ScriptName);
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
