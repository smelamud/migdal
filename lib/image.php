<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/logs.php');
require_once('lib/images.php');
require_once('lib/uri.php');
require_once('lib/utils.php');

dbOpen();
if($size!='small' && $size!='large')
  reload(remakeURI($REQUEST_URI,array(),array('size' => 'small')));
$id=addslashes($id);
$image=getImageContentById($id,$size);
if($size=='large')
  logEvent('imageview','imageset('.$image->getImageSet().')');
header('Content-Type: '.($size=='small' ? $thumbnailType 
                                        : $image->getFormat()));
$method='get'.ucfirst($size);
echo $image->$method();
dbClose();
?>
