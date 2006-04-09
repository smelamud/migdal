<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/images.php');
require_once('lib/image-types.php');

function uploadImage($name,&$posting,$createThumbnail,$thumbnailX,$thumbnailY,
                     $del)
{
global $maxImageSize,$thumbnailType,$imageDir;

if($del)
  {
  $posting->small_image=0;
  $posting->small_image_x=$posting->small_image_y=0;
  $posting->large_image=0;
  $posting->large_image_x=$posting->large_image_y=0;
  $posting->large_image_size=0;
  $posting->large_image_format='';
  $posting->large_image_filename='';
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
if($createThumbnail)
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
  $posting->large_image_x=$posting->large_image_y=0;
  symlink($smallFilename,$largeName);
  }
//FIXME journal() !
return EG_OK;
}

function commitImages(&$posting,&$original)
{
global $thumbnailType,$imageDir;

if($posting->getId()==$original->getId()
   && $posting->getSmallImage()==$original->getSmallImage()
   && $posting->getLargeImage()==$original->getLargeImage())
  return;
$ext=getImageExtension($thumbnailType);
$mainSmallName=getImagePath($posting->getId(),$ext,0,'small');
@unlink($mainSmallName);
$ext=getImageExtension($original->getLargeImageFormat());
$mainSmallName=getImagePath($posting->getId(),$ext,0,'small');
$mainLargeName=getImagePath($posting->getId(),$ext,0,'large');
@unlink($mainSmallName);
@unlink($mainLargeName);
if(!$posting->hasSmallImage())
  return;
if($posting->hasLargeImage())
  {
  $ext=getImageExtension($thumbnailType);
  $smallFilename=getImageFilename($original->getId(),$ext,
				  $posting->getSmallImage(),'small');
  $newSmallFilename=getImageFilename($posting->getId(),$ext,
				     $posting->getSmallImage(),'small');
  $mainSmallFilename=getImageFilename($posting->getId(),$ext,0,'small');
  $ext=getImageExtension($posting->getLargeImageFormat());
  $largeFilename=getImageFilename($original->getId(),$ext,
				  $posting->getLargeImage(),'large');
  $newLargeFilename=getImageFilename($posting->getId(),$ext,
				     $posting->getLargeImage(),'large');
  $mainLargeFilename=getImageFilename($posting->getId(),$ext,0,'large');
  }
else
  {
  $ext=getImageExtension($posting->getLargeImageFormat());
  $smallFilename=getImageFilename($original->getId(),$ext,
				  $posting->getSmallImage(),'small');
  $newSmallFilename=getImageFilename($posting->getId(),$ext,
				     $posting->getSmallImage(),'small');
  $mainSmallFilename=getImageFilename($posting->getId(),$ext,0,'small');
  $largeFilename=getImageFilename($original->getId(),$ext,
				  $posting->getSmallImage(),'large');
  $newLargeFilename=getImageFilename($posting->getId(),$ext,
				     $posting->getSmallImage(),'large');
  $mainLargeFilename=getImageFilename($posting->getId(),$ext,0,'large');
  }
rename("$imageDir/$smallFilename","$imageDir/$newSmallFilename");
@unlink("$imageDir/$mainSmallFilename");
symlink($newSmallFilename,"$imageDir/$mainSmallFilename");
if(!is_link("$imageDir/$largeFilename"))
  rename("$imageDir/$largeFilename","$imageDir/$newLargeFilename");
else
  symlink($newSmallFilename,"$imageDir/$newLargeFilename");
@unlink("$imageDir/$mainLargeFilename");
symlink($newLargeFilename,"$imageDir/$mainLargeFilename");
//FIXME journal() !
}

define('IFR_OK',0);
define('IFR_SMALL',1);
define('IFR_UNKNOWN_FORMAT',2);
define('IFR_UNSUPPORTED_FORMAT',3);
define('IFR_UNSUPPORTED_THUMBNAIL',4);

function imageFileResize($fnameFrom,$format,$fnameTo,$thumbnailX,$thumbnailY)
{
global $thumbnailType,$glassImagePath;

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
  
if(function_exists('ImageCopyResampled'))
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
