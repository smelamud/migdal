<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/images.php');
require_once('lib/uri.php');
require_once('lib/utils.php');

function savePalette($image)
{
$palette=array();
for($i=0;$i<imageColorsTotal($image);$i++)
   $palette[i]=imageColorsForIndex($image,$i);
return $palette;
}

function restorePalette($image,$palette)
{
for($i=0;$i<count($palette);$i++)
   imageColorSet($image,$i,$palette[$i]['red'],
                           $palette[$i]['green'],
			   $palette[$i]['blue']);
}

dbOpen();
$id=addslashes($id);
$image=getImageContentById($id,'small');
$handle=imageCreateFromString($image->getSmall());
$film=imageCreateFromPNG('pics/film.png');
$palette=savePalette($film);
list($fx,$fy,$hx,$hy)=array(imageSX($film),imageSY($film),
                            imageSX($handle),imageSY($handle));
imageCopy($film,$handle,($fx-$hx)/2,($fy-$hy)/2,0,0,$hx,$hy);
//restorePalette($film,$palette);
header("Content-Type: $thumbnailType");
imageJPEG($film);
dbClose();
?>
