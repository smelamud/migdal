<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/bug.php');
require_once('lib/journal.php');

function dbOpen()
{
global $dbLink,$dbHost,$dbName,$dbUser,$dbPassword;

if($dbLink>0)
  return;
$dbLink=mysql_connect($dbHost,$dbUser,$dbPassword)
            or sqlbug('�� ���� ��������� � �������� ��� ������');
mysql_select_db($dbName) or sqlbug("�� ���� ������� ���� ������ $dbName");
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
