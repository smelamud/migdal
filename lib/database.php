<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/bug.php');

function dbOpen()
{
global $dbLink,$dbHost,$dbName,$dbUser,$dbPassword;

if($dbLink>0)
  return;
$dbLink=mysql_connect($dbHost,$dbUser,$dbPassword)
            or sqlbug('Не могу связаться с сервером баз данных');
mysql_select_db($dbName) or sqlbug("Не могу открыть базу данных $dbName");
}

function dbClose()
{
global $dbLink;

mysql_close($dbLink);
$dbLink=0;
}
?>
