<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/database.php');
require_once('lib/bug.php');

dbOpen();
$id=addslashes($id);
$result=mysql_query("select large_body
                     from stotexts
		     where id=$id")
	  or sqlbug('Ошибка SQL при выборке текста');
header('Content-Type: text/plain');
echo strtr(mysql_result($result,0,0),
           array_flip(get_html_translation_table(HTML_ENTITIES,ENT_QUOTES)));
dbClose();
?>
