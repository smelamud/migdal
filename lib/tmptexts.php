<?php
# @(#) $Id$

require_once('lib/bug.php');

function tmpTextSave($text)
{
mysql_query('insert into tmp_texts(value) values(\''.addslashes($text).'\')')
  or sqlbug('Ошибка SQL при временном сохранении текста');
return mysql_insert_id();
}

function tmpTextRestore($id)
{
$result=mysql_query("select value from tmp_texts where id=$id")
        or sqlbug('Ошибка SQL при восстановлении временно сохраненного текста');
mysql_query("update tmp_texts set last_access=null where id=$id")
  or sqlbug('Ошибка SQL при обновлении timestamp временно сохраненного текста');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : '';
}
?>
