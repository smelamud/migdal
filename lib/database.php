<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/bug.php');
require_once('lib/profiling.php');

function dbOpen()
{
global $dbLink,$dbHost,$dbName,$dbUser,$dbPassword,$dbCharset;

if($dbLink>0)
  return;
$dbLink=mysql_pconnect($dbHost,$dbUser,$dbPassword)
            or sqlbug(__FUNCTION__.'().mysql_connect');
mysql_select_db($dbName)
      or sqlbug(__FUNCTION__.'().mysql_select_db');
@mysql_query("set names '$dbCharset'"); // Ignore error, if MySQL do not support charsets
beginProfiling(POBJ_PAGE,$ScriptName);
}

function dbClose()
{
global $dbLink;

if($dbLink<=0)
  return;
endProfiling();
mysql_close($dbLink);
$dbLink=0;
}
?>
