<?php
# @(#) $Id$

require_once('lib/bug.php');
require_once('lib/profiling.php');

function sql($query,$object_name,$comment='',$profiling=true)
{
if($profiling)
  beginProfiling(POBJ_SQL,$object_name,$comment);
$result=mysql_query($query);
if(!$result)
  sqlbug($object_name);
if($profiling)
  endProfiling();
return $result;
}
?>
