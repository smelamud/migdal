<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/database.php');
require_once('lib/bug.php');
require_once('lib/text.php');
require_once('lib/utils.php');

dbOpen();
$id=addslashes($id);
$result=mysql_query("select large_body,large_format
                     from stotexts
		     where id=$id")
	  or sqlbug('Ошибка SQL при выборке текста');
header('Content-Type: text/plain');
if(mysql_result($result,0,1)==TF_HTML)
  $s=mysql_result($result,0,0);
else
  $s=unhtmlentities(mysql_result($result,0,0));
if($win)
  echo convert_cyr_string($s,'k','w');
else
  echo $s;
dbClose();
?>
