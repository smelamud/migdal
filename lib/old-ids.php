<?php
# @(#) $Id$

require_once('lib/sql.php');

function getOldId($entry_id,$table_name)
{
$result=sql("select old_id
             from old_ids
	     where table_name='$table_name' and entry_id=$entry_id",
	    __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function getNewId($table_name,$old_id)
{
$result=sql("select entry_id
             from old_ids
	     where table_name='$table_name' and old_id=$old_id",
	    __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function putOldId($entry_id,$table_name,$old_id)
{
$new_id=getNewId($table_name,$old_id);
if($new_id==0)
  sql("insert into old_ids(table_name,old_id,entry_id)
       values('$table_name',$old_id,$entry_id)",
      __FUNCTION__);
else
  echo "Duplicate id for $table_name($old_id): $new_id and $entry_id\n";
}
?>
