<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/bug.php');
require_once('lib/journal.php');

function dbOpen($replication=false)
{
global $dbLink,$dbHost,$dbName,$replicationDbName,$dbUser,$dbPassword;

if($dbLink>0)
  return;
$dbLink=mysql_connect($dbHost,$dbUser,$dbPassword)
            or sqlbug('�� ���� ��������� � �������� ��� ������');
mysql_select_db(!$replication ? $dbName : $replicationDbName)
      or sqlbug('�� ���� ������� ���� ������');
beginJournal();
}

function dbClose()
{
global $dbLink;

if($dbLink<=0)
  return;
endJournal();
mysql_close($dbLink);
$dbLink=0;
}
?>
