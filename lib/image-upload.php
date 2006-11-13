<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/images.php');
require_once('lib/image-types.php');
require_once('lib/image-upload-flags.php');

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
if(!$resizeIfExists && ($flags & IU_THUMB)!=IU_THUMB_MANUAL
   && ($flags & IU_THUMB)!=IU_THUMB_RESIZE
   || isset($_FILES[$name]) && $_FILES[$name]['tmp_name']!='')
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
  $largeFilename=getImageFilename($posting->orig_id,
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
  if($posting->small_image==0)
    return EG_OK;
  $largeExt=getImageExtension($posting->large_image_format);
  $oldFilename=getImageFilename($posting->orig_id,$largeExt,
				$posting->getImage(),
				$posting->getImageDimension());
  $oldName="$imageDir/$oldFilename";
  $largeId=getNextImageFileId();
  $largeFilename=getImageFilename($posting->orig_id,$largeExt,
				  $largeId,'large');
  $largeName="$imageDir/$largeFilename";
  rename($oldName,$largeName);
  if(($flags & IU_THUMB)!=IU_THUMB_MANUAL
     && ($flags & IU_THUMB)!=IU_THUMB_RESIZE)
    deleteImageFiles($posting->orig_id,$posting->small_image,
		     $posting->large_image,$posting->large_image_format);
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
if(($flags & IU_THUMB)==IU_THUMB_AUTO
   || ($flags & IU_THUMB)==IU_THUMB_NO_GLASS)
  {
  // Create thumbnail from large image
  $smallId=getNextImageFileId();
  $smallName=getImagePath($posting->orig_id,
                          getImageExtension($thumbnailType),$smallId,'small');
  $err=imageFileResize($largeName,$posting->large_image_format,
                       $smallName,$thumbnailType,
                       $thumbExactX,$thumbExactY,$thumbMaxX,$thumbMaxY,
		       ($flags & IU_THUMB)!=IU_THUMB_NO_GLASS);
  if($err==IFR_UNKNOWN_FORMAT || $err==IFR_UNSUPPORTED_FORMAT)
    return EIU_UNKNOWN_IMAGE;
  if($err==IFR_UNSUPPORTED_THUMBNAIL)
    return EIU_UNKNOWN_THUMBNAIL;
  if($err!=IFR_OK)
    $smallId=0;
  }
elseif(($flags & IU_THUMB)==IU_THUMB_MANUAL
       || ($flags & IU_THUMB)==IU_THUMB_RESIZE)
  {
  // Upload thumbnail
  $tname="${name}_thumb";
  if(isset($_FILES[$tname]) && $_FILES[$tname]['tmp_name']!='')
    {
    // Move uploaded thumbnail into archive
    $file=$_FILES[$tname];
    if($file['tmp_name']=='' || !is_uploaded_file($file['tmp_name'])
       || filesize($file['tmp_name'])!=$file['size'])
      return EG_OK;
    if($file['size']>$maxImageSize)
      return EIU_THUMBNAIL_LARGE;
    if(getImageTypeCode($file['type'])!=getImageTypeCode($thumbnailType))
      return EIU_UNKNOWN_THUMBNAIL;

    $smallId=getNextImageFileId();
    $smallName=getImagePath($posting->orig_id,
			    getImageExtension($thumbnailType),$smallId,'small');
    if(!move_uploaded_file($file['tmp_name'],$smallName))
      return EG_OK;
    }
  else
    {
    $smallId=$posting->small_image;
    $smallName=getImagePath($posting->orig_id,
			    getImageExtension($thumbnailType),$smallId,'small');
    }
  }
// Resize the thumbnail
if(($flags & IU_THUMB)==IU_THUMB_RESIZE)
  {
  $tmpName="$smallName.tmp";
  $err=imageFileResize($smallName,$thumbnailType,
                       $tmpName,$thumbnailType,
                       $thumbExactX,$thumbExactY,$thumbMaxX,$thumbMaxY,false);

  if($err==IFR_UNKNOWN_FORMAT || $err==IFR_UNSUPPORTED_FORMAT
     || $err==IFR_UNSUPPORTED_THUMBNAIL)
    return EIU_UNKNOWN_THUMBNAIL;
  if($err==IFR_OK)
    {
    @unlink($smallName);
    rename($tmpName,$smallName);
    }
  }
// Fill the record with thumbnail parameters
if($smallId!=0)
  {
  // Image has a thumbnail
  $posting->small_image=$smallId;
  $posting->large_image=$largeId;
  list($posting->small_image_x,
       $posting->small_image_y)=getImageSize($smallName);
  list($posting->large_image_x,
       $posting->large_image_y)=getImageSize($largeName);
  }
else
  {
  // Image hasn't any thumbnail
  $posting->small_image=$largeId;
  $posting->large_image=0;
  $largeExt=getImageExtension($posting->large_image_format);
  $smallFilename=getImageFilename($posting->orig_id,$largeExt,$largeId,
				  'small');
  $smallName="$imageDir/$smallFilename";
  rename($largeName,$smallName);
  list($posting->small_image_x,
       $posting->small_image_y)=getImageSize($smallName);
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
