<?php
# @(#) $Id$

function tmpTextSave($text)
{
mysql_query('insert into tmp_texts(value) values(\''.addslashes($text).'\')')
           or die ('������ SQL ��� ��������� ���������� ������');
return mysql_insert_id();
}

function tmpTextRestore($id)
{
$result=mysql_query("select value from tmp_texts where id=$id")
           or die('������ SQL ��� �������������� �������� ������������ ������');
mysql_query("update tmp_texts set last_access=null where id=$id")
           or die('������ SQL ��� ���������� timestamp �������� ������������
	           ������');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : '';
}
?>
