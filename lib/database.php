<?php
# @(#) $Id$

require_once('conf/migdal.conf');

function dbOpen()
{
global $dbLink,$dbHost,$dbName,$dbUser,$dbPassword;

$dbLink=mysql_connect($dbHost,$dbUser,$dbPassword) or
        die('Не могу связаться с сервером баз данных');
mysql_select_db($dbName) or die("Не могу открыть базу данных $dbName");
}

function dbClose()
{
global $dbLink;

mysql_close($dbLink);
}
?>
