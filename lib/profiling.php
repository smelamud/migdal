<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/sql.php');
require_once('lib/track.php');

define('POBJ_PAGE',1);
define('POBJ_FUNCTION',2);
define('POBJ_SQL',3);

define('PLEVEL_NONE',0);
define('PLEVEL_PAGES',1);
define('PLEVEL_ALL',2);

$profileStack=array();

function millitime()
{
list($usec,$sec)=explode(" ",microtime());
return (int)((float)$usec*1000)+$sec%1000*1000;
}

function beginProfiling($object,$name,$comment='')
{
global $profileStack,$profilingLevel;

if($profilingLevel<=PLEVEL_NONE)
  return;
if($object==POBJ_PAGE || $profilingLevel>PLEVEL_PAGES)
  {
  if(count($profileStack)==0)
    $up=0;
  else
    $up=$profileStack[count($profileStack)-1];
  $time=millitime();
  $name=addslashes($name);
  $comment=addslashes($comment);
  sql("insert into profiling(up,object,name,begin_time,comment)
       values($up,$object,'$name',$time,'$comment')",
      'beginProfiling','','',false);
  $id=sql_insert_id();
  array_push($profileStack,$id);
  updateTrackById('profiling',$id,track($profileStack));
  }
else
  array_push($profileStack,0);
}

function endProfiling()
{
global $profileStack,$profilingLevel;

if($profilingLevel<=PLEVEL_NONE)
  return;
$id=array_pop($profileStack);
if(!$id)
  return;
$time=millitime();
sql("update profiling
     set end_time=$time
     where id=$id",
    'endProfiling','','',false);
}
?>
