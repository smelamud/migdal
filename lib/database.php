<?php
# @(#) $Id$

require_once('conf/migdal.conf');

function dbOpen()
{
global $dbLink,$dbHost,$dbName,$dbUser,$dbPassword;

$dbLink=mysql_connect($dbHost,$dbUser,$dbPassword) or
        die('Cannot connect to database host');
mysql_select_db($dbName) or die("Cannot select database $dbName");
}

function dbClose()
{
global $dbLink;

mysql_close($dbLink);
}
?>
