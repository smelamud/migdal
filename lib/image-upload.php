<?php
# @(#) $Id$

require_once('lib/images.php');

$imageExtensions=array('image/pjpeg' => 'jpg',
                       'image/jpeg'  => 'jpg',
		       'image/gif'   => 'gif',
		       'image/x-png' => 'png',
		       'image/png'   => 'png');

function getImageExtension($mime_type)
{
global $imageExtensions;

return $imageExtensions[$mime_type];
}

$imageTypeNames=array('image/pjpeg' => 'JPEG',
                      'image/jpeg'  => 'JPEG',
		      'image/gif'   => 'GIF',
		      'image/x-png' => 'PNG',
		      'image/png'   => 'PNG');

function getImageTypeName($mime_type)
{
global $imageTypeNames;

return $imageTypeNames[$mime_type];
}

$imageTypeCodes=array('image/pjpeg' => IMG_JPG,
                      'image/jpeg'  => IMG_JPG,
		      'image/gif'   => IMG_GIF,
		      'image/x-png' => IMG_PNG,
		      'image/png'   => IMG_PNG);

function getImageTypeCode($mime_type)
{
global $imageTypeCodes;

return $imageTypeCodes[$mime_type];
}

function uploadImageUsingMogrify($image,$image_name,$image_size,$image_type,
                                 $thumbnail,&$err)
{
global $mogrifyPath,$maxImage,$thumbnailType,$thumbnailWidth,$thumbnailHeight,
       $tmpDir;

$largeExt=getImageExtension($image_type);
$smallExt=getImageExtension($thumbnailType);
if($largeExt=='')
  {
  $err=EIU_UNKNOWN_IMAGE;
  return false;
  }

$anyFile=tempnam($tmpDir,'mig-');
unlink($anyFile);
$largeFile="$anyFile.$largeExt";
$smallFile="$anyFile.$smallExt";

if(!rename($image,$largeFile))
  {
  $err=EIU_OK;
  return false;
  }
$large_size=getImageSize($largeFile);
$fd=fopen($largeFile,'r');
$large=fread($fd,$maxImage);
fclose($fd);

$geometry=$thumbnailWidth.'x'.$thumbnailHeight;
exec("$mogrifyPath -format $smallExt -geometry '$geometry>' $largeFile");

$small_size=getImageSize($smallFile);
$fd=fopen($smallFile,'r');
$small=fread($fd,$maxImage);
fclose($fd);

unlink($largeFile);
unlink($smallFile);

return new Image(array('filename' => $image_name,
		       'small'    => $small,
		       'small_x'  => $small_size[0],
		       'small_y'  => $small_size[1],
		       'large'    => $large,
		       'large_x'  => $large_size[0],
		       'large_y'  => $large_size[1],
		       'format'   => $image_type));
}

function uploadImageByDefault($image,$image_name,$image_size,$image_type,
                              $thumbnail,&$err)
{
global $maxImage,$defaultThumbnail;

$large_size=getImageSize($image);
$fd=fopen($image,'r');
$large=fread($fd,$maxImage);
fclose($fd);

$small_size=getImageSize($defaultThumbnail);
$fd=fopen($defaultThumbnail,'r');
$small=fread($fd,$maxImage);
fclose($fd);

return new Image(array('filename' => $image_name,
		       'small'    => $small,
		       'small_x'  => $small_size[0],
		       'small_y'  => $small_size[1],
		       'large'    => $large,
		       'large_x'  => $large_size[0],
		       'large_y'  => $large_size[1],
		       'format'   => $image_type));
}

function uploadImageUsingGD($image,$image_name,$image_size,$image_type,
                            $thumbnail,&$err)
{
global $maxImage,$thumbnailType,$thumbnailWidth,$thumbnailHeight,$tmpDir;

if((ImageTypes() & getImageTypeCode($image_type))==0)
  return uploadImageByDefault($image,$image_name,$image_size,$image_type,
                              $thumbnail,$err);
  
$lFname=getImageTypeName($image_type);
if($lFname=='')
  {
  $err=EIU_UNKNOWN_IMAGE;
  return false;
  }
$imageFrom="ImageCreateFrom$lFname";
$lHandle=$imageFrom($image);

$large_size_x=ImageSX($lHandle);
$large_size_y=ImageSY($lHandle);
if($large_size_x>$thumbnailWidth || $large_size_y>$thumbnailHeight)
  {
  $lAspect=$large_size_x/$large_size_y;
  $small_size_x=$thumbnailWidth;
  $small_size_y=(int)($small_size_x/$lAspect);
  if($small_size_y>$thumbnailHeight)
    {
    $small_size_y=$thumbnailHeight;
    $small_size_x=(int)($small_size_y*$lAspect);
    }
  }
else
  {
  $small_size_x=$large_size_x;
  $small_size_y=$large_size_y;
  }
  
$sHandle=ImageCreate($small_size_x,$small_size_y);
ImageCopyResized($sHandle,$lHandle,0,0,0,0,$small_size_x,$small_size_y,
                 $large_size_x,$large_size_y);

$sFname=getImageTypeName($thumbnailType);
if((ImageTypes() & getImageTypeCode($thumbnailType))==0 || $sFname=='')
  {
  $err=EIU_UNKNOWN_THUMBNAIL;
  return false;
  }
$imageTo="Image$sFname";
$smallFile=tempnam($tmpDir,'mig-');
$imageTo($sHandle,$smallFile);

$fd=fopen($image,'r');
$large=fread($fd,$maxImage);
fclose($fd);

$fd=fopen($smallFile,'r');
$small=fread($fd,$maxImage);
fclose($fd);

unlink($smallFile);

return new Image(array('filename' => $image_name,
		       'small'    => $small,
		       'small_x'  => $small_size_x,
		       'small_y'  => $small_size_y,
		       'large'    => $large,
		       'large_x'  => $large_size_x,
		       'large_y'  => $large_size_y,
		       'format'   => $image_type));
}

function uploadImage($name,$thumbnail,&$err)
{
global $HTTP_POST_FILES,$maxImage,$useMogrify,$tmpDir;

$image=$HTTP_POST_FILES[$name]['tmp_name'];
$image_name=$HTTP_POST_FILES[$name]['name'];
$image_size=$HTTP_POST_FILES[$name]['size'];
$image_type=$HTTP_POST_FILES[$name]['type'];

if(!isset($image) || $image=='' || !is_uploaded_file($image)
   || filesize($image)!=$image_size)
  {
  $err=EIU_OK;
  return false;
  }
if($image_size>$maxImage)
  {
  $err=EIU_IMAGE_LARGE;
  return false;
  }

$image_tmpname=tempnam($tmpDir,'mig-');
if(!move_uploaded_file($image,$image_tmpname))
  {
  $err=EIU_OK;
  return false;
  }
if($useMogrify)
  $img=uploadImageUsingMogrify($image_tmpname,$image_name,$image_size,
                               $image_type,$thumbnail,$err);
else
  $img=uploadImageUsingGD($image_tmpname,$image_name,$image_size,$image_type,
                          $thumbnail,$err);
unlink($image_tmpname);

if(!$img)
  return $img;
if(!$img->store())
  {
  $err=EIU_IMAGE_SQL;
  return false;
  }
$err=EIU_OK;
return $img;
}

?>
