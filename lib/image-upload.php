<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/images.php');
require_once('lib/image-types.php');

// Large image
define('IU_IMAGE',0x0F);
define('IU_MANUAL',0x00); // Dimensions are set manually by user
define('IU_RESIZE',0x01); // Resize automatically
// Small image (thumbnail)
define('IU_THUMB',0xF0);
define('IU_THUMB_NONE',0x10);     // Thumbnail is not needed
define('IU_THUMB_MANUAL',0x20);   // Thumbnail is uploaded by user
                                  // (FIXME not implemented)
define('IU_THUMB_RESIZE',0x30);   // Thumbnail is uploaded by user and resized
                                  // automatically (FIXME not implemented)
define('IU_THUMB_AUTO',0x00);     // Thumbnail is created automatically
define('IU_THUMB_NO_GLASS',0x40); // Thumbnail is created automatically without
                                  // glass icon

$imageUploadFlags=array(''         => IU_MANUAL,
                        'manual'   => IU_MANUAL,
                        'resize'   => IU_RESIZE,
			'-'        => IU_THUMB_AUTO,
			'none-'    => IU_THUMB_NONE,
			'manual-'  => IU_THUMB_MANUAL,
			'auto-'    => IU_THUMB_AUTO,
			'noglass-' => IU_THUMB_NO_GLASS);

function imageUploadFlags($s)
{
global $imageUploadFlags;

@list($thumb,$image)=explode('-',$s);
return $imageUploadFlags["$thumb-"] | $imageUploadFlags[$image];
}

function imageUpload($name,&$posting,$flags,$thumbExactX,$thumbExactY,
                     $thumbMaxX,$thumbMaxY,$imageExactX,$imageExactY,
		     $imageMaxX,$imageMaxY,$del,$resizeIfExists=false)
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
if(!$resizeIfExists || isset($_FILES[$name]) && $_FILES[$name]['tmp_name']!='')
  {
  // Move uploaded image into archive
  if(!isset($_FILES[$name]))
    return EG_OK;
  $file=$_FILES[$name];
  if($file['tmp_name']=='' || !is_uploaded_file($file['tmp_name'])
     || filesize($file['tmp_name'])!=$file['size'])
    return EG_OK;
  if($file['size']>$maxImageSize)
    return EIU_IMAGE_LARGE;

  $largeId=getNextImageFileId();
  $largeFilename=getImageFilename($posting->getOrigId(),
				  getImageExtension($file['type']),$largeId,
				  'large');
  $largeName="$imageDir/$largeFilename";
  if(!move_uploaded_file($file['tmp_name'],$largeName))
    return EG_OK;
  $posting->large_image_size=$file['size'];
  $posting->large_image_format=$file['type'];
  $posting->large_image_filename=$file['name'];
  }
else
  {
  // Remove thumbnail and rename large image
  if(!$posting->hasSmallImage())
    return EG_OK;
  $largeExt=getImageExtension($posting->getLargeImageFormat());
  $oldFilename=getImageFilename($posting->getOrigId(),$largeExt,
				$posting->getImage(),
				$posting->getImageDimension());
  $oldName="$imageDir/$oldFilename";
  $largeId=getNextImageFileId();
  $largeFilename=getImageFilename($posting->getOrigId(),$largeExt,
				  $largeId,'large');
  $largeName="$imageDir/$largeFilename";
  rename($oldName,$largeName);
  deleteImageFiles($posting->getOrigId(),$posting->getSmallImage(),
                   $posting->getLargeImage(),$posting->getLargeImageFormat());
  }
// Resize the image
if(($flags & IU_IMAGE)==IU_RESIZE)
  {
  $tmpName="$largeName.tmp";
  $err=imageFileResize($largeName,$posting->large_image_format,
                       $tmpName,$posting->large_image_format,
                       $imageExactX,$imageExactY,$imageMaxX,$imageMaxY,false);

  if($err==IFR_UNKNOWN_FORMAT || $err==IFR_UNSUPPORTED_FORMAT
     || $err==IFR_UNSUPPORTED_THUMBNAIL)
    return EIU_UNKNOWN_IMAGE;
  if($err==IFR_OK)
    {
    @unlink($largeName);
    rename($tmpName,$largeName);
    }
  }
// Create thumbnail
$hasThumbnail=false;
if(($flags & IU_THUMB)==IU_THUMB_AUTO
   || ($flags & IU_THUMB)==IU_THUMB_NO_GLASS)
  {
  $smallId=getNextImageFileId();
  $smallName=getImagePath($posting->getOrigId(),
                          getImageExtension($thumbnailType),$smallId,'small');
  $err=imageFileResize($largeName,$posting->large_image_format,
                       $smallName,$thumbnailType,
                       $thumbExactX,$thumbExactY,$thumbMaxX,$thumbMaxY,
		       ($flags & IU_THUMB)!=IU_THUMB_NO_GLASS);
  if($err==IFR_UNKNOWN_FORMAT || $err==IFR_UNSUPPORTED_FORMAT)
    return EIU_UNKNOWN_IMAGE;
  if($err==IFR_UNSUPPORTED_THUMBNAIL)
    return EIU_UNKNOWN_THUMBNAIL;
  $hasThumbnail=$err==IFR_OK;
  }
// Fill the record with thumbnail parameters
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
  $largeExt=getImageExtension($posting->getLargeImageFormat());
  $smallFilename=getImageFilename($posting->getOrigId(),$largeExt,
				  $largeId,'small');
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
   && $posting->getOrigId()==$original->getOrigId()
   && $posting->getSmallImage()==$original->getSmallImage()
   && $posting->getLargeImage()==$original->getLargeImage())
  return;
$postingId=$posting->getEntry()==ENT_POSTING ? $posting->getOrigId()
                                             : $posting->getId();
$originalId=$original->getEntry()==ENT_POSTING ? $original->getOrigId()
                                               : $original->getId();
$ext=getImageExtension($thumbnailType);
$mainSmallName=getImagePath($postingId,$ext,0,'small');
@unlink($mainSmallName);
$ext=getImageExtension($original->getLargeImageFormat());
$mainSmallName=getImagePath($postingId,$ext,0,'small');
$mainLargeName=getImagePath($postingId,$ext,0,'large');
@unlink($mainSmallName);
@unlink($mainLargeName);
if(!$posting->hasSmallImage())
  return;
if($posting->hasLargeImage())
  {
  $ext=getImageExtension($thumbnailType);
  $smallFilename=getImageFilename($originalId,$ext,
				  $posting->getSmallImage(),'small');
  $newSmallFilename=getImageFilename($postingId,$ext,
				     $posting->getSmallImage(),'small');
  $mainSmallFilename=getImageFilename($postingId,$ext,0,'small');
  $ext=getImageExtension($posting->getLargeImageFormat());
  $largeFilename=getImageFilename($originalId,$ext,
				  $posting->getLargeImage(),'large');
  $newLargeFilename=getImageFilename($postingId,$ext,
				     $posting->getLargeImage(),'large');
  $mainLargeFilename=getImageFilename($postingId,$ext,0,'large');
  }
else
  {
  $ext=getImageExtension($posting->getLargeImageFormat());
  $smallFilename=getImageFilename($originalId,$ext,
				  $posting->getSmallImage(),'small');
  $newSmallFilename=getImageFilename($postingId,$ext,
				     $posting->getSmallImage(),'small');
  $mainSmallFilename=getImageFilename($postingId,$ext,0,'small');
  $largeFilename=getImageFilename($originalId,$ext,
				  $posting->getSmallImage(),'large');
  $newLargeFilename=getImageFilename($postingId,$ext,
				     $posting->getSmallImage(),'large');
  $mainLargeFilename=getImageFilename($postingId,$ext,0,'large');
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

function imageFileResize($fnameFrom,$formatFrom,$fnameTo,$formatTo,
                         $thumbExactX=0,$thumbExactY=0,
                         $thumbMaxX=0,$thumbMaxY=0,$addGlass=true)
{
global $thumbnailType,$glassImagePath;

if((ImageTypes() & getImageTypeCode($formatFrom))==0)
  return IFR_UNSUPPORTED_FORMAT;
  
// Load the image
$lFname=getImageTypeName($formatFrom);
if($lFname=='')
  return IFR_UNKNOWN_FORMAT;
$imageFrom="ImageCreateFrom$lFname";
$lHandle=$imageFrom($fnameFrom);

// Calculate the dimensions of the thumbnail
$large_size_x=ImageSX($lHandle);
$large_size_y=ImageSY($lHandle);
$lAspect=$large_size_x/$large_size_y;
if($thumbExactX>0 || $thumbExactY>0)
  {
  // Exact dimensions given
  if($thumbExactX>0 && $thumbExactY>0)
    {
    $small_size_x=$thumbExactX;
    $small_size_y=$thumbExactY;
    }
  else
    if($thumbExactX>0)
      {
      $small_size_x=$thumbExactX;
      if(abs($small_size_x-$large_size_x)<=0)
	$small_size_x=$large_size_x;
      $small_size_y=(int)($small_size_x/$lAspect);
      }
    else
      {
      $small_size_y=$thumbExactY;
      if(abs($small_size_y-$large_size_y)<=0)
	$small_size_y=$large_size_y;
      $small_size_x=(int)($small_size_y*$lAspect);
      }
  if(abs($small_size_x-$large_size_x)<=0)
    $small_size_x=$large_size_x;
  if(abs($small_size_y-$large_size_y)<=0)
    $small_size_y=$large_size_y;
  }
else
  {
  // Maximal dimensions given
  if($thumbMaxX==0)
    $thumbMaxX=65535;
  if($thumbMaxY==0)
    $thumbMaxY=65535;
  if($large_size_x>$thumbMaxX || $large_size_y>$thumbMaxY)
    {
    $small_size_x=$thumbMaxX;
    $small_size_y=(int)($small_size_x/$lAspect);
    if($small_size_y>$thumbMaxY)
      {
      $small_size_y=$thumbMaxY;
      $small_size_x=(int)($small_size_y*$lAspect);
      }
    }
  else
    {
    $small_size_x=$large_size_x;
    $small_size_y=$large_size_y;
    }
  }
  
// Resize the image
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

// Add the glass
if($addGlass)
  {
  $glass=imageCreateFromPNG($glassImagePath);
  list($gx,$gy,$hx,$hy)=array(imageSX($glass),imageSY($glass),
			      imageSX($sHandle),imageSY($sHandle));
  imageCopy($sHandle,$glass,$hx-$gx,$hy-$gy,0,0,$gx,$gy);
  }

// Save the thumbnail
$sFname=getImageTypeName($formatTo);
if((ImageTypes() & getImageTypeCode($formatTo))==0 || $sFname=='')
  return IFR_UNSUPPORTED_THUMBNAIL;
$imageTo="Image$sFname";
$imageTo($sHandle,$fnameTo);

return IFR_OK;
}
?>
