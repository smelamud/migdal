<?php
# @(#) $Id$

function tmpTextSave($text)
{
mysql_query('insert into tmp_texts(value) values(\''.AddSlashes($text).'\')')
           or die ('Ошибка SQL при временном сохранении текста');
return mysql_insert_id();
}

function tmpTextRestore($id)
{
$result=mysql_query("select value from tmp_texts where id=$id")
           or die('Ошибка SQL при восстановлении временно сохраненного текста');
mysql_query("update tmp_texts set last_access=null where id=$id")
           or die('Ошибка SQL при обновлении timestamp временно сохраненного
	           текста');
return mysql_result($result,0,0);
}
?>
