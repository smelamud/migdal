<?php
require_once('conf/migdal.conf');

require_once('lib/database.php');

dbOpen();
if($size!='small' && $size!='large')
  {
  header('Location: '.remakeURI($REQUEST_URI,
                                array(),
				array('size' => 'small')));
  exit;
  }
$id=addslashes($id);
$result=mysql_query("select $size,format
                     from images
		     where id=$id")
	     or die('Ошибка SQL при выборке изображения');
header('Content-Type: '.($size=='small' ? $thumbnailType 
                                        : mysql_result($result,0,1)));
echo mysql_result($result,0,0);
dbClose();
?>
