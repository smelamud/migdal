<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/images.php');
require_once('lib/uri.php');
require_once('lib/utils.php');

dbOpen();
$id=addslashes($id);
$image=getImageContentById($id,'small');
$image_tmpname=tempnam($tmpDir,'mig-');
$image_size=strlen($image->getSmall());
$fd=fopen($image_tmpname,'w');
fwrite($fd,$image->getSmall(),$image_size);
fclose($fd);
$handle=imageCreateFromJPEG($image_tmpname);
unlink($image_tmpname);
$glass=imageCreateFromPNG('pics/glass.png');
list($gx,$gy,$hx,$hy)=array(imageSX($glass),imageSY($glass),
                            imageSX($handle),imageSY($handle));
imageCopy($handle,$glass,$hx-$gx,$hy-$gy,0,0,$gx,$gy);
header("Content-Type: $thumbnailType");
imageJPEG($handle);
dbClose();
?>
