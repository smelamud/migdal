<?php
# @(#) $Id$

function track($id)
{
return sprintf('%010u',$id);
}

function trackById($table,$id)
{
$result=mysql_query("select track
                     from $table
		     where id=$id")
	     or die("Ошибка SQL при выборке маршрута из $table");
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : '';
}
?>
