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
$glass=imageCreateFromPNG('pics/glass.png');
list($gx,$gy,$hx,$hy)=array(imageSX($glass),imageSY($glass),
                            imageSX($handle),imageSY($handle));
for($x=0;$x<$gx;$x++)
   for($y=0;$y<$gy;$y++)
      {
      $colors=imageColorsForIndex($glass,imageColorAt($glass,$x,$y));
      $r=$colors['red'];
      $g=$colors['green'];
      $b=$colors['blue'];
      if($r==255 && $g==0 && $b==0)
        continue;
      $color=imageColorClosest($handle,$r,$g,$b);
      imageSetPixel($handle,$hx-$gx+$x,$hy-$gy+$y,$color);
      }
header("Content-Type: $thumbnailType");
imageJPEG($handle);
dbClose();
?>