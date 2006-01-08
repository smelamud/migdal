<?php
# @(#) $Id$

require_once('lib/bug.php');
require_once('lib/profiling.php');

function get_method($object,$method)
{
return get_class($object).".$method";
}

$lastInsertId=0;

function sql($query,$object_name,$part='',$comment='',$profiling=true)
{
global $lastInsertId;

if($part!='')
  $part=".$part";
if($object_name!='' && $profiling)
  beginProfiling(POBJ_SQL,"$object_name$part",$comment);
$result=mysql_query($query);
if($object_name!='' && !$result)
  sqlbug("$object_name()$part");
$insertId=mysql_insert_id();
if($object_name!='' && $profiling)
  endProfiling();
$lastInsertId=$insertId;
return $result;
}

function sql_insert_id()
{
global $lastInsertId;

return $lastInsertId;
}

function sqlDate($timestamp)
{
return date('Y-m-d H:i:s',$timestamp);
}

function sqlTime($timestamp)
{
return sqlDate($timestamp);
}

function sqlNow()
{
return sqlDate(time());
}
?>
