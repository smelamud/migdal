<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/database.php');
require_once('lib/bug.php');
require_once('lib/text.php');

dbOpen();
$id=addslashes($id);
$result=mysql_query("select large_body,large_format
                     from stotexts
		     where id=$id")
	  or sqlbug('������ SQL ��� ������� ������');
header('Content-Type: text/plain');
if(mysql_result($result,0,1)==TF_HTML)
  echo mysql_result($result,0,0);
else
  echo strtr(mysql_result($result,0,0),
	     array_flip(get_html_translation_table(HTML_ENTITIES,ENT_QUOTES)));
dbClose();
?>
