<?php
# @(#) $Id$

require_once('lib/images.php');
require_once('lib/image-types.php');

function uploadImageUsingMogrify($image,$image_name,$image_size,$image_type,
                                 $thumbnailX,$thumbnailY,&$err)
{
global $mogrifyPath,$maxImage,$thumbnailType,$tmpDir;

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

$geometry=$thumbnailX.'x'.$thumbnailY;
exec("$mogrifyPath -format $smallExt -geometry '$geometry>' $largeFile");

$small_size=getImageSize($smallFile);
$fd=fopen($smallFile,'r');
$small=fread($fd,$maxImage);
fclose($fd);

unlink($largeFile);
unlink($smallFile);

if($small_size[0]==$large_size[0] && $small_size[1]==$large_size[1])
  {
  $has_large=false;
  $large='';
  $large_size=array(0,0);
  }
else
  $has_large=true;
  
return new Image(array('filename'  => $image_name,
		       'small'     => $small,
		       'small_x'   => $small_size[0],
		       'small_y'   => $small_size[1],
		       'has_large' => $has_large,
		       'large'     => $large,
		       'large_x'   => $large_size[0],
		       'large_y'   => $large_size[1],
		       'format'    => $image_type));
}

function uploadImageByDefault($image,$image_name,$image_size,$image_type,
                              $hasThumbnail,$thumbnailX,$thumbnailY,&$err)
{
global $maxImage,$defaultThumbnail;

if($hasThumbnail)
  {
  $large_size=getImageSize($image);
  $fd=fopen($image,'r');
  $large=fread($fd,$maxImage);
  fclose($fd);

  $small_size=getImageSize($defaultThumbnail);
  $fd=fopen($defaultThumbnail,'r');
  $small=fread($fd,$maxImage);
  fclose($fd);
  }
else
  {
  $small_size=getImageSize($image);
  $fd=fopen($image,'r');
  $small=fread($fd,$maxImage);
  fclose($fd);

  $large_size=array(0,0);
  $large='';
  }

return new Image(array('filename'  => $image_name,
		       'small'     => $small,
		       'small_x'   => $small_size[0],
		       'small_y'   => $small_size[1],
		       'has_large' => $hasThumbnail,
		       'large'     => $large,
		       'large_x'   => $large_size[0],
		       'large_y'   => $large_size[1],
		       'format'    => $image_type));
}

function uploadImageUsingGD($image,$image_name,$image_size,$image_type,
                            $thumbnailX,$thumbnailY,&$err)
{
global $maxImage,$thumbnailType,$tmpDir;

if((ImageTypes() & getImageTypeCode($image_type))==0)
  return uploadImageByDefault($image,$image_name,$image_size,$image_type,
                              true,$thumbnailX,$thumbnailY,$err);
  
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
if($large_size_x>$thumbnailX || $large_size_y>$thumbnailY)
  {
  $lAspect=$large_size_x/$large_size_y;
  $small_size_x=$thumbnailX;
  $small_size_y=(int)($small_size_x/$lAspect);
  if($small_size_y>$thumbnailY)
    {
    $small_size_y=$thumbnailY;
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

if($small_size_x==$large_size_x && $small_size_y==$large_size_y)
  {
  $has_large=false;
  $large='';
  $large_size_x=$large_size_y=0;
  }
else
  $has_large=true;
  
return new Image(array('filename'  => $image_name,
		       'small'     => $small,
		       'small_x'   => $small_size_x,
		       'small_y'   => $small_size_y,
		       'has_large' => $has_large,
		       'large'     => $large,
		       'large_x'   => $large_size_x,
		       'large_y'   => $large_size_y,
		       'format'    => $image_type));
}

function uploadImage($name,$hasThumbnail,$thumbnailX,$thumbnailY,&$err,
                     $title='',$image_set=0)
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
if(!$hasThumbnail)
  $img=uploadImageByDefault($image_tmpname,$image_name,$image_size,$image_type,
                            $hasThumbnail,$thumbnailX,$thumbnailY,$err);
else
  if($useMogrify)
    $img=uploadImageUsingMogrify($image_tmpname,$image_name,$image_size,
				 $image_type,$thumbnailX,$thumbnailY,$err);
  else
    $img=uploadImageUsingGD($image_tmpname,$image_name,$image_size,$image_type,
			    $thumbnailX,$thumbnailY,$err);
unlink($image_tmpname);

if(!$img)
  return $img;
$img->setImageSet($image_set);
$img->setTitle($title);
if(!$img->store())
  {
  $err=EIU_IMAGE_SQL;
  return false;
  }
$err=EIU_OK;
return $img;
}

function uploadMemoryImage($content,$image_name,$image_type,$hasThumbnail,
                           $thumbnailX,$thumbnailY,&$err,$title='',
			   $image_set=0)
{
global $HTTP_POST_FILES,$useMogrify,$tmpDir;

$image_tmpname=tempnam($tmpDir,'mig-');
$image_size=strlen($content);
$fd=fopen($image_tmpname,'w');
fwrite($fd,$content,$image_size);
fclose($fd);
if(!$hasThumbnail)
  $img=uploadImageByDefault($image_tmpname,$image_name,$image_size,$image_type,
                            $hasThumbnail,$thumbnailX,$thumbnailY,$err);
else
  if($useMogrify)
    $img=uploadImageUsingMogrify($image_tmpname,$image_name,$image_size,
				 $image_type,$thumbnailX,$thumbnailY,$err);
  else
    $img=uploadImageUsingGD($image_tmpname,$image_name,$image_size,$image_type,
			    $thumbnailX,$thumbnailY,$err);
unlink($image_tmpname);

if(!$img)
  return $img;
$img->setImageSet($image_set);
$img->setTitle($title);
if(!$img->store())
  {
  $err=EIU_IMAGE_SQL;
  return false;
  }
$err=EIU_OK;
return $img;
}

?>
