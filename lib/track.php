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
return sql("update $table
	    set track='$track'
	    where id=$id",
	   __FUNCTION__,'','',false);
}

function updateTracks($table,$id,$journalize=true)
{
$result=sql("select id,up
	     from $table
	     where track like '%".track($id)."%' or track=''
	     order by track",
	    __FUNCTION__);
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
