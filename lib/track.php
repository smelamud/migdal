<?php
# @(#) $Id$

function track($id,$prev='')
{
$track=sprintf('%010u',$id);
return $prev!='' ? "$prev $track" : $track;
}

function trackById($table,$id)
{
$result=mysql_query("select track
                     from $table
		     where id=$id")
	     or die("Ошибка SQL при выборке маршрута из $table");
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : '';
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
?>
