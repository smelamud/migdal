<?php
require_once('conf/migdal.conf');

require_once('lib/database.php');

dbOpen();
$id=addslashes($id);
$result=mysql_query("select large_body
                     from stotexts
		     where id=$id")
	     or die('Ошибка SQL при выборке текста');
header('Content-Type: text/plain');
echo mysql_result($result,0,0);
dbClose();
?>
