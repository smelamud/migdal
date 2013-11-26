<?php
# @(#) $Id$

require_once('lib/ident.php');
require_once('lib/bug.php');
require_once('lib/cache.php');
require_once('lib/sql.php');

function track($id,$prev='')
{
if(!is_array($id))
  $track=sprintf('%010u',$id);
else
  {
  $track='';
  foreach($id as $i)
         $track.=' '.sprintf('%010u',$i);
  $track=substr($track,1);
  }
return $prev!='' && $prev!=0 ? "$prev $track" : $track;
}

function upById($table,$id)
{
$result=sql("select up
	     from $table
	     where id=$id",
	    __FUNCTION__,'',"table='$table'");
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function trackById($table,$id)
{
if($id<=0)
  return 0;
if(hasCachedValue('track',$table,$id))
  return getCachedValue('track',$table,$id);
$result=sql("select track
	     from $table
	     where id=$id",
	    __FUNCTION__,'',"table='$table'");
$track=mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
setCachedValue('track',$table,$id,$track);
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
if(trackById($table,$id)!=$track)
  {
  sql("update $table
       set track='$track'
       where id=$id",
      __FUNCTION__,'','',false);
  setCachedValue('track',$table,$id,$track);
  }
}

function createTrack($table,$id)
{
$path=array();
$up=$id;
while($up!=0)
     {
     $path[]=$up;
     $result=sql("select up
                  from $table
		  where id=$up",
		 __FUNCTION__,'up');
     if(mysql_num_rows($result)<=0)
       break;
     $up=mysql_result($result,0,0);
     }
$track='';
for($i=count($path)-1;$i>=0;$i--)
   $track=track($path[$i],$track);
updateTrackById($table,$id,$track);
}

function replaceTracks($table,$oldTrack,$newTrack)
{
if($oldTrack==$newTrack)
  return;
$tailPos=strlen($oldTrack)+1;
sql("update $table
     set track=concat('$newTrack',substring(track from $tailPos))
     where track like '$oldTrack%'",
    __FUNCTION__);
}

function replaceTracksToUp($table,$oldTrack,$newUp,$id)
{
replaceTracks($table,$oldTrack,track($id,trackById('entries',$newUp)));
}

function subtree($table,$id,$recursive=false,$byId='parent_id',$byTrack='track')
{
if(!$recursive)
  return "$byId=$id";
else
  {
  $track=trackById($table,$id);
  return  "$byTrack like '$track%'";
  }
}
?>
