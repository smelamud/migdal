<?php
# @(#) $Id$

require_once('lib/ident.php');
require_once('lib/bug.php');

function track($id,$prev='')
{
$track=sprintf('%010u',$id);
return $prev!='' ? "$prev $track" : $track;
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
	  or sqlbug("������ SQL ��� ������� �������� �� $table");
$track=mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
if(!isset($tracks[$table]))
  $tracks[$table]=array();
$tracks[$table][$id]=$track;
return $track;
}

function updateTrackById($table,$id,$track)
{
return mysql_query("update $table
                    set track='$track'
		    where id=$id");
}

function updateTracks($table,$id)
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
return true;
}

function subtree($id,$recursive=false,$byId='id',$byTrack='track')
{
return !$recursive ? "$byId=$id" : "$byTrack like '%".track($id)."%'";
}
?>
