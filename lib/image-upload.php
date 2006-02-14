<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/images.php');
require_once('lib/image-types.php');
require_once('lib/exec.php');

function uploadImageUsingMogrify($image,$image_name,$image_size,$image_type,
                                 $thumbnailX,$thumbnailY,&$err)
{
global $mogrifyPath,$maxImageSize,$thumbnailType,$tmpDir;

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
  $err=EG_OK;
  return false;
  }
$large_size=getImageSize($largeFile);
$fd=fopen($largeFile,'r');
$large=fread($fd,$maxImageSize);
fclose($fd);

$geometry=$thumbnailX.'x'.$thumbnailY;
getCommand("$mogrifyPath -format $smallExt -geometry '$geometry>' $largeFile");

$small_size=getImageSize($smallFile);
$fd=fopen($smallFile,'r');
$small=fread($fd,$maxImageSize);
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
global $maxImageSize,$defaultThumbnail;

if($hasThumbnail)
  {
  $large_size=getImageSize($image);
  $fd=fopen($image,'r');
  $large=fread($fd,$maxImageSize);
  fclose($fd);

  $small_size=getImageSize($defaultThumbnail);
  $fd=fopen($defaultThumbnail,'r');
  $small=fread($fd,$maxImageSize);
  fclose($fd);
  }
else
  {
  $small_size=getImageSize($image);
  $fd=fopen($image,'r');
  $small=fread($fd,$maxImageSize);
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
global $useCopyResampled,$maxImageSize,$thumbnailType,$tmpDir;

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
  
if($useCopyResampled)
  {
  $sHandle=ImageCreateTrueColor($small_size_x,$small_size_y);
  ImageCopyResampled($sHandle,$lHandle,0,0,0,0,$small_size_x,$small_size_y,
		     $large_size_x,$large_size_y);
  }
else
  {
  $sHandle=ImageCreate($small_size_x,$small_size_y);
  ImageCopyResized($sHandle,$lHandle,0,0,0,0,$small_size_x,$small_size_y,
		   $large_size_x,$large_size_y);
  }

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
$large=fread($fd,$maxImageSize);
fclose($fd);

$fd=fopen($smallFile,'r');
$small=fread($fd,$maxImageSize);
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

/*function uploadImage($name,$hasThumbnail,$thumbnailX,$thumbnailY,&$err,
                     $title='',$image_set=0)
{
global $HTTP_POST_FILES,$maxImageSize,$useMogrify,$tmpDir;

$image=$HTTP_POST_FILES[$name]['tmp_name'];
$image_name=$HTTP_POST_FILES[$name]['name'];
$image_size=$HTTP_POST_FILES[$name]['size'];
$image_type=$HTTP_POST_FILES[$name]['type'];

if(!isset($image) || $image=='' || !is_uploaded_file($image)
   || filesize($image)!=$image_size)
  {
  $err=EG_OK;
  return false;
  }
if($image_size>$maxImageSize)
  {
  $err=EIU_IMAGE_LARGE;
  return false;
  }

$image_tmpname=tempnam($tmpDir,'mig-');
if(!move_uploaded_file($image,$image_tmpname))
  {
  $err=EG_OK;
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
$img->store();
$err=EG_OK;
return $img;
}*/

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
  return false;
$img->setImageSet($image_set);
$img->setTitle($title);
$img->store();
$err=EG_OK;
return $img;
}

function uploadImage($name,&$posting,$thumbnailX,$thumbnailY,$del)
{
global $maxImageSize,$thumbnailType,$imageDir;

if($del)
  {
  $posting->small_image=0;
  $posting->large_image=0;
  // FIXME update links & files
  }
if(!isset($_FILES[$name]))
  return EG_OK;
$file=$_FILES[$name];
if($file['tmp_name']=='' || !is_uploaded_file($file['tmp_name'])
   || filesize($file['tmp_name'])!=$file['size'])
  return EG_OK;
if($file['size']>$maxImageSize)
  return EIU_IMAGE_LARGE;

$largeId=getNextImageId();
$largeFilename=getImageFilename($posting->getId(),
                                getImageExtension($file['type']),$largeId,
				'large');
$largeName="$imageDir/$largeFilename";
if(!move_uploaded_file($file['tmp_name'],$largeName))
  return EG_OK;
$posting->large_image_size=$file['size'];
$posting->large_image_format=$file['type'];
$posting->large_image_filename=$file['name'];
$hasThumbnail=false;
if($posting->createThumbnail())
  {
  $smallId=getNextImageId();
  $smallName=getImagePath($posting->getId(),getImageExtension($thumbnailType),
                          $smallId,'small');
  $err=imageFileResize($largeName,$posting->large_image_format,$smallName,
                       $thumbnailX,$thumbnailY);
  if($err==IFR_UNKNOWN_FORMAT || $err==IFR_UNSUPPORTED_FORMAT)
    return EIU_UNKNOWN_IMAGE;
  if($err==IFR_UNSUPPORTED_THUMBNAIL)
    return EIU_UNKNOWN_THUMBNAIL;
  $hasThumbnail=$err==IFR_OK;
  }
if($hasThumbnail)
  {
  $posting->small_image=$smallId;
  $posting->large_image=$largeId;
  list($posting->small_image_x,$posting->small_image_y)=getImageSize($smallName);
  list($posting->large_image_x,$posting->large_image_y)=getImageSize($largeName);
  }
else
  {
  $posting->small_image=$largeId;
  $posting->large_image=0;
  $smallFilename=getImageFilename($posting->getId(),
                                  getImageExtension($file['type']),$largeId,
			          'small');
  $smallName="$imageDir/$smallFilename";
  rename($largeName,$smallName);
  list($posting->small_image_x,$posting->small_image_y)=getImageSize($smallName);
  symlink($smallFilename,$largeName);
  }
return EG_OK;
}

define('IFR_OK',0);
define('IFR_SMALL',1);
define('IFR_UNKNOWN_FORMAT',2);
define('IFR_UNSUPPORTED_FORMAT',3);
define('IFR_UNSUPPORTED_THUMBNAIL',4);

function imageFileResize($fnameFrom,$format,$fnameTo,$thumbnailX,$thumbnailY)
{
global $useCopyResampled,$thumbnailType,$glassImagePath;

if((ImageTypes() & getImageTypeCode($format))==0)
  return IFR_UNSUPPORTED_FORMAT;
  
$lFname=getImageTypeName($format);
if($lFname=='')
  return IFR_UNKNOWN_FORMAT;
$imageFrom="ImageCreateFrom$lFname";
$lHandle=$imageFrom($fnameFrom);

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
  
if($useCopyResampled)
  {
  $sHandle=ImageCreateTrueColor($small_size_x,$small_size_y);
  ImageCopyResampled($sHandle,$lHandle,0,0,0,0,$small_size_x,$small_size_y,
		     $large_size_x,$large_size_y);
  }
else
  {
  $sHandle=ImageCreate($small_size_x,$small_size_y);
  ImageCopyResized($sHandle,$lHandle,0,0,0,0,$small_size_x,$small_size_y,
		   $large_size_x,$large_size_y);
  }

if($small_size_x==$large_size_x && $small_size_y==$large_size_y)
  return IFR_SMALL;

$glass=imageCreateFromPNG($glassImagePath);
list($gx,$gy,$hx,$hy)=array(imageSX($glass),imageSY($glass),
                            imageSX($sHandle),imageSY($sHandle));
imageCopy($sHandle,$glass,$hx-$gx,$hy-$gy,0,0,$gx,$gy);

$sFname=getImageTypeName($thumbnailType);
if((ImageTypes() & getImageTypeCode($thumbnailType))==0 || $sFname=='')
  return IFR_UNSUPPORTED_THUMBNAIL;
$imageTo="Image$sFname";
$imageTo($sHandle,$fnameTo);

return IFR_OK;
}
?>
