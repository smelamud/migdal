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
$handle=imageCreateFromString($image->getSmall());
$film=imageCreateFromPNG('pics/film.png');
list($fx,$fy,$hx,$hy)=array(imageSX($film),imageSY($film),
                            imageSX($handle),imageSY($handle));
imageCopy($film,$handle,($fx-$hx)/2,($fy-$hy)/2,0,0,$hx,$hy);
$color=imageColorAt($film,0,0);
imageColorSet($film,$color,136,102,85);
$color=imageColorAt($film,4,4);
imageColorSet($film,$color,248,240,221);
header("Content-Type: $thumbnailType");
imageJPEG($film);
dbClose();
?>
