<?php
# @(#) $Id$

require_once('lib/bug.php');
require_once('lib/sql.php');

function tmpTextSave($text)
{
sql('insert into tmp_texts(value)
     values(\''.addslashes($text).'\')',
    'tmpTextSave');
return sql_insert_id();
}

function tmpTextRestore($id)
{
$result=sql("select value
             from tmp_texts
	     where id=$id",
            'tmpTextRestore','restore');
sql("update tmp_texts
     set last_access=null
     where id=$id",
    'tmpTextRestore','timestamp');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : '';
}
?>
