<?php
# @(#) $Id$

require_once('lib/ident.php');
require_once('lib/bug.php');

function track($id,$prev='')
{
$track=sprintf('%010u',$id);
return $prev!='' ? "$prev $track" : $track;
}

function upById($table,$id)
{
$result=mysql_query("select up
                     from $table
		     where id=$id")
	  or sqlbug("Ошибка SQL при выборке родителя из $table");
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

$tracks=array();

function trackById($table,$id)
{
global $tracks;

if(isset($tracks[$table]) && isset($tracks[$table][$id]))
  return $tracks[$table][$id];
$result=mysql_query("select track
                     from $table
		     where id=$id")
	  or sqlbug("Ошибка SQL при выборке маршрута из $table");
$track=mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
if(!isset($tracks[$table]))
  $tracks[$table]=array();
$tracks[$table][$id]=$track;
return $track;
}

function ancestorById($table,$id,$level=-1)
{
if($table=='' || $id<=0)
  return 0;
if($level<0)
  return trackById($table,$id);
$levels=explode(' ',trackById($table,$id));
return (int)($levels[$level]);
}

function updateTrackById($table,$id,$track)
{
return mysql_query("update $table
                    set track='$track'
		    where id=$id");
}

function updateTracks($table,$id,$journalize=true)
{
$result=mysql_query("select id,up
                     from $table
		     where track like '%".track($id)."%' or track=''
		     order by track");
if(!$result)
  return false;
$tracks=array();
while($row=mysql_fetch_assoc($result))
     {
     if(!isset($tracks[$row['up']]))
       $tracks[$row['up']]=trackById($table,$row['up']);
     $tracks[$row['id']]=track($row['id'],$tracks[$row['up']]);
     updateTrackById($table,$row['id'],$tracks[$row['id']]);
     }
if($journalize)
  journal("tracks $table ".journalVar($table,$id));
return true;
}

function subtree($id,$recursive=false,$byId='id',$byTrack='track')
{
return !$recursive ? "$byId=$id" : "$byTrack like '%".track($id)."%'";
}
?>
