<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/images.php');

dbOpen();
$id=addslashes($id);
$image=getImageContentById($id,'small');
header("Content-Type: $thumbnailType");
echo $image->getSmall();
dbClose();
?>
