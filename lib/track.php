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
	     or die("������ SQL ��� ������� �������� �� $table");
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : '';
}

function updateTrackById($table,$id,$track)
{
return mysql_query("update $table
                    set track='$track'
		    where id=$id");
}
?>
