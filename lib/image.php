<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/logs.php');

dbOpen();
if($size!='small' && $size!='large')
  {
  header('Location: '.remakeURI($REQUEST_URI,
                                array(),
				array('size' => 'small')));
  exit;
  }
$id=addslashes($id);
$result=mysql_query("select $size,format,image_set
                     from images
		     where id=$id")
	     or die('Ошибка SQL при выборке изображения');
if($size=='large')
  logEvent('imageview','imageset('.mysql_result($result,0,2).')');
header('Content-Type: '.($size=='small' ? $thumbnailType 
                                        : mysql_result($result,0,1)));
echo mysql_result($result,0,0);
dbClose();
?>
