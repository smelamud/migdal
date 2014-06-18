<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/debug-log.php');
require_once('lib/image-files.php');
require_once('lib/image-types.php');
require_once('lib/image-upload-flags.php');

function imageUpload($name, Entry $posting, $flags, $thumbExactX, $thumbExactY,
                     $thumbMaxX, $thumbMaxY, $imageExactX, $imageExactY,
                     $imageMaxX, $imageMaxY, $del, $resizeIfExists = false) {
    global $maxImageSize, $thumbnailType, $imageDir;
    
    if (isDebugLogging(LL_FUNCTIONS)) {
        debugLog(LL_FUNCTIONS, 'imageUpload(name=%,posting='.
                 imagePostingData($posting).',flags=%,thumbExactX=%,'.
                 'thumbExactY=%,thumbMaxX=%,thumbMaxY=%,imageExactX=%,'.
                 'imageExactY=%,imageMaxX=%,imageMaxY=%,del=%,'.
                 'resizeIfExists=%)',
                 array($name, $flags, $thumbExactX, $thumbExactY, $thumbMaxX,
                       $thumbMaxY, $imageExactX, $imageExactY, $imageMaxX,
                       $imageMaxY, $del, $resizeIfExists));
        debugLog(LL_FUNCTIONS, '\$_FILES[%]=%', array($name, @$_FILES[$name]));
    }
    if ($del) {
        debugLog(LL_DETAILS, 'cleaning up the posting');
        $posting->small_image = 0;
        $posting->small_image_x = $posting->small_image_y = 0;
        $posting->small_image_format = '';
        $posting->large_image = 0;
        $posting->large_image_x = $posting->large_image_y = 0;
        $posting->large_image_size = 0;
        $posting->large_image_format = '';
        $posting->large_image_filename = '';
    }
    if (!$resizeIfExists && ($flags & IU_THUMB) != IU_THUMB_MANUAL
        && ($flags & IU_THUMB) != IU_THUMB_RESIZE || isset($_FILES[$name])
        && $_FILES[$name]['tmp_name'] != '') {
        debugLog(LL_DETAILS, 'moving uploaded image');
        // Move uploaded image into archive
        if (!isset($_FILES[$name])) {
            debugLog(LL_FUNCTIONS, 'EG_OK, no image in POST request');
            return EG_OK;
        }
        $file = $_FILES[$name];
        if ($file['tmp_name'] == '' || !is_uploaded_file($file['tmp_name'])
            || filesize($file['tmp_name']) != $file['size']) {
            debugLog(LL_FUNCTIONS, 'EG_OK, invalid file in POST request');
            return EG_OK;
        }
        if ($file['size'] > $maxImageSize) {
            debugLog(LL_FUNCTIONS, 'EIU_IMAGE_LARGE, file is too large');
            return EIU_IMAGE_LARGE;
        }
        
        $largeId = getNextImageFileId();
        $largeFilename = getImageFilename(getImageExtension($file['type']),
                                          $largeId);
        $largeName = "$imageDir/$largeFilename";
        debugLog(LL_DETAILS, 'move_uploaded_file(%,%)',
                 array($file['tmp_name'], $largeName));
        if (!move_uploaded_file($file['tmp_name'], $largeName)) {
            debugLog(LL_FUNCTIONS, 'EG_OK, failed to move the file');
            return EG_OK;
        }
        chmod($largeName, 0644);
        $posting->large_image_size = $file['size'];
        $posting->large_image_format = $file['type'];
        $posting->large_image_filename = $file['name'];
    } else {
        // Remove thumbnail and rename large image
        debugLog(LL_DETAILS, 'removing thumbnail');
        if ($posting->small_image == 0) {
            debugLog(LL_FUNCTIONS, 'EG_OK, no image in the posting');
            return EG_OK;
        }
        $largeExt = getImageExtension($posting->large_image_format);
        $oldFilename = getImageFilename($largeExt, $posting->getImage());
        $oldName = "$imageDir/$oldFilename";
        $largeId = getNextImageFileId();
        $largeFilename = getImageFilename($largeExt, $largeId);
        $largeName = "$imageDir/$largeFilename";
        debugLog(LL_DETAILS, 'rename(%,%)', array($oldName, $largeName));
        rename($oldName, $largeName);
        if (($flags & IU_THUMB) != IU_THUMB_MANUAL
            && ($flags & IU_THUMB) != IU_THUMB_RESIZE)
            deleteImageFiles($posting->small_image,
                             $posting->small_image_format,
                             $posting->large_image,
                             $posting->large_image_format);
    }
    if (isDebugLogging(LL_DETAILS))
        debugLog(LL_DETAILS, 'posting='.imagePostingData($posting));
    // Resize the image
    if (($flags & IU_IMAGE) == IU_RESIZE) {
        $tmpName = "$largeName.tmp";
        $err = imageFileResize($largeName, $posting->large_image_format,
                               $tmpName, $posting->large_image_format,
                               $imageExactX, $imageExactY,
                               $imageMaxX, $imageMaxY, false);
        
        debugLog(LL_DETAILS, 'err=%', array($err));
        if ($err == IFR_UNKNOWN_FORMAT || $err == IFR_UNSUPPORTED_FORMAT
            || $err == IFR_UNSUPPORTED_THUMBNAIL) {
            debugLog(LL_FUNCTIONS,
                     'EIU_UNKNOWN_IMAGE, imageFileResize() returned error');
            return EIU_UNKNOWN_IMAGE;
        }
        if ($err == IFR_OK) {
            debugLog(LL_DETAILS, 'unlink(%)', array($largeName));
            @unlink($largeName);
            debugLog(LL_DETAILS, 'rename(%,%)', array($tmpName, $largeName));
            rename($tmpName, $largeName);
        }
    }
    if (($flags & IU_THUMB) == IU_THUMB_AUTO
        || ($flags & IU_THUMB) == IU_THUMB_NO_GLASS) {
        // Create thumbnail from large image
        debugLog(LL_DETAILS, 'create thumbnail from large image');
        $smallId = getNextImageFileId();
        $smallName = getImagePath(getImageExtension($thumbnailType), $smallId);
        $err = imageFileResize($largeName, $posting->large_image_format,
                               $smallName, $thumbnailType,
                               $thumbExactX, $thumbExactY,
                               $thumbMaxX, $thumbMaxY,
                               ($flags & IU_THUMB) != IU_THUMB_NO_GLASS);
        debugLog(LL_DETAILS, 'err=%', array($err));
        if ($err == IFR_UNKNOWN_FORMAT || $err == IFR_UNSUPPORTED_FORMAT)
            return EIU_UNKNOWN_IMAGE;
        if ($err == IFR_UNSUPPORTED_THUMBNAIL)
            return EIU_UNKNOWN_THUMBNAIL;
        if ($err != IFR_OK)
            $smallId = 0;
    } elseif (($flags & IU_THUMB) == IU_THUMB_MANUAL
              || ($flags & IU_THUMB) == IU_THUMB_RESIZE) {
        // Upload thumbnail
        $tname = "${name}_thumb";
        debugLog(LL_DETAILS, 'uploading thumbnail');
        debugLog(LL_FUNCTIONS, '\$_FILES[%]=%',
                 array($tname, @$_FILES[$tname]));
        if (isset($_FILES[$tname]) && $_FILES[$tname]['tmp_name'] != '') {
            // Move uploaded thumbnail into archive
            debugLog(LL_DETAILS, 'moving uploaded thumbnail');
            $file = $_FILES[$tname];
            if ($file['tmp_name'] == ''
                || !is_uploaded_file($file['tmp_name'])
                || filesize($file['tmp_name']) != $file['size']) {
                debugLog(LL_FUNCTIONS, 'EG_OK, invalid file in POST request');
                return EG_OK;
            }
            if ($file['size'] > $maxImageSize) {
                debugLog(LL_FUNCTIONS,
                         'EIU_THUMBNAIL_LARGE, file is too large');
                return EIU_THUMBNAIL_LARGE;
            }
            if (getImageTypeCode($file['type'])
                != getImageTypeCode($thumbnailType)) {
                debugLog(LL_FUNCTIONS,
                         'EIU_UNKNOWN_THUMBNAIL, thumbnail is not of the required type');
                return EIU_UNKNOWN_THUMBNAIL;
            }
            
            $smallId = getNextImageFileId();
            $smallName = getImagePath(getImageExtension($thumbnailType),
                                      $smallId);
            debugLog(LL_DETAILS, 'move_uploaded_file(%,%)',
                     array($file['tmp_name'], $smallName));
            if (!move_uploaded_file($file['tmp_name'], $smallName)) {
                debugLog(LL_FUNCTIONS, 'EG_OK, failed to move the file');
                return EG_OK;
            }
            chmod($smallName, 0644);
        } else {
            debugLog(LL_DETAILS, 'no thumbnail file in the POST request');
            $smallId = $posting->small_image;
            $smallName = getImagePath(getImageExtension($thumbnailType),
                                      $smallId);
        }
    }
    // Resize the thumbnail
    if (($flags & IU_THUMB) == IU_THUMB_RESIZE) {
        $tmpName = "$smallName.tmp";
        $err = imageFileResize($smallName, $thumbnailType, $tmpName,
                               $thumbnailType, $thumbExactX, $thumbExactY,
                               $thumbMaxX, $thumbMaxY, false);
        
        if ($err == IFR_UNKNOWN_FORMAT || $err == IFR_UNSUPPORTED_FORMAT
            || $err == IFR_UNSUPPORTED_THUMBNAIL)
            return EIU_UNKNOWN_THUMBNAIL;
        if ($err == IFR_OK) {
            @unlink($smallName);
            rename($tmpName, $smallName);
        }
    }
    // Fill the record with thumbnail parameters
    debugLog(LL_DETAILS, 'filling the record with thumbnail parameters');
    if ($smallId != 0) {
        // Image has a thumbnail
        debugLog(LL_DETAILS, 'image has a thumbnail');
        $posting->small_image = $smallId;
        $posting->small_image_format = $thumbnailType;
        $posting->large_image = $largeId;
        $smallImageSize = getImageSize($smallName);
        debugLog(LL_DEBUG, 'small getImageSize(%)=%',
                 array($smallName, $smallImageSize));
        list($posting->small_image_x,
             $posting->small_image_y) = $smallImageSize;
        $largeImageSize = getImageSize($largeName);
        debugLog(LL_DEBUG, 'large getImageSize(%)=%',
                 array($largeName, $largeImageSize));
        list($posting->large_image_x,
             $posting->large_image_y) = $largeImageSize;
    } else {
        // Image hasn't any thumbnail
        debugLog(LL_DETAILS, 'image hasn\'t any thumbnail');
        $posting->small_image = $largeId;
        $posting->small_image_format = $posting->large_image_format;
        $posting->large_image = $largeId;
        $largeExt = getImageExtension($posting->large_image_format);
        $smallFilename = getImageFilename($largeExt, $largeId);
        $smallName = "$imageDir/$smallFilename";
        debugLog(LL_DETAILS, 'rename(%,%)', array($largeName, $smallName));
        rename($largeName, $smallName);
        $smallImageSize = getImageSize($smallName);
        debugLog(LL_DEBUG, 'small getImageSize(%)=%',
                 array($smallName, $smallImageSize));
        list($posting->small_image_x,
             $posting->small_image_y) = $smallImageSize;
        list($posting->large_image_x,
             $posting->large_image_y) = $smallImageSize;
    }
    if (isDebugLogging(LL_FUNCTIONS))
        debugLog(LL_FUNCTIONS, 'EG_OK, posting='.imagePostingData($posting));
    return EG_OK;
}

const IFR_OK = 0;
const IFR_SMALL = 1;
const IFR_UNKNOWN_FORMAT = 2;
const IFR_UNSUPPORTED_FORMAT = 3;
const IFR_UNSUPPORTED_THUMBNAIL = 4;

function imageFileResize($fnameFrom, $formatFrom, $fnameTo, $formatTo,
                         $thumbExactX = 0, $thumbExactY = 0,
                         $thumbMaxX = 0, $thumbMaxY = 0, $addGlass = true) {
    global $glassImagePath;
    
    debugLog(LL_FUNCTIONS, 'imageFileResize(fnameFrom=%,formatFrom=%,'.
             'fnameTo=%,formatTo=%,thumbExactX=%,thumbExactY=%,thumbMaxX=%,'.
             'thumbMaxY=%,addGlass=%)',
             array($fnameFrom, $formatFrom, $fnameTo, $formatTo,
                   $thumbExactX, $thumbExactY, $thumbMaxX, $thumbMaxY,
                   $addGlass));
    if ((ImageTypes() & getImageTypeCode($formatFrom)) == 0)
        return IFR_UNSUPPORTED_FORMAT;
    
    // Load the image
    $lFname = getImageTypeName($formatFrom);
    if ($lFname == '')
        return IFR_UNKNOWN_FORMAT;
    $imageFrom = "ImageCreateFrom$lFname";
    $lHandle = $imageFrom($fnameFrom);
    
    // Calculate the dimensions of the thumbnail
    $large_size_x = ImageSX($lHandle);
    $large_size_y = ImageSY($lHandle);
    $lAspect = $large_size_x / $large_size_y;
    if ($thumbExactX > 0 || $thumbExactY > 0) {
        // Exact dimensions given
        if ($thumbExactX > 0 && $thumbExactY > 0) {
            $small_size_x = $thumbExactX;
            $small_size_y = $thumbExactY;
        } else if ($thumbExactX > 0) {
            $small_size_x = $thumbExactX;
            if (abs($small_size_x - $large_size_x) <= 0)
                $small_size_x = $large_size_x;
            $small_size_y = (int) ($small_size_x / $lAspect);
        } else {
            $small_size_y = $thumbExactY;
            if (abs($small_size_y - $large_size_y) <= 0)
                $small_size_y = $large_size_y;
            $small_size_x = (int) ($small_size_y * $lAspect);
        }
        if (abs($small_size_x - $large_size_x) <= 0)
            $small_size_x = $large_size_x;
        if (abs($small_size_y - $large_size_y) <= 0)
            $small_size_y = $large_size_y;
    } else {
        // Maximal dimensions given
        if ($thumbMaxX == 0)
            $thumbMaxX = 65535;
        if ($thumbMaxY == 0)
            $thumbMaxY = 65535;
        if ($large_size_x > $thumbMaxX || $large_size_y > $thumbMaxY) {
            $small_size_x = $thumbMaxX;
            $small_size_y = (int) ($small_size_x / $lAspect);
            if ($small_size_y > $thumbMaxY) {
                $small_size_y = $thumbMaxY;
                $small_size_x = (int) ($small_size_y * $lAspect);
            }
        } else {
            $small_size_x = $large_size_x;
            $small_size_y = $large_size_y;
        }
    }
    
    // Resize the image
    if (function_exists('ImageCopyResampled')) {
        $sHandle = ImageCreateTrueColor($small_size_x, $small_size_y);
        ImageCopyResampled($sHandle, $lHandle, 0, 0, 0, 0,
                           $small_size_x, $small_size_y,
                           $large_size_x, $large_size_y);
    } else {
        $sHandle = ImageCreate($small_size_x, $small_size_y);
        ImageCopyResized($sHandle, $lHandle, 0, 0, 0, 0,
                         $small_size_x, $small_size_y,
                         $large_size_x, $large_size_y);
    }
    
    if ($small_size_x == $large_size_x && $small_size_y == $large_size_y)
        return IFR_SMALL;
    
    // Add the glass
    if ($addGlass) {
        $glass = imageCreateFromPNG($glassImagePath);
        list($gx, $gy, $hx, $hy) = array(imageSX($glass), imageSY($glass),
                                         imageSX($sHandle), imageSY($sHandle));
        imageCopy($sHandle, $glass, $hx - $gx, $hy - $gy, 0, 0, $gx, $gy);
    }
    
    // Save the thumbnail
    $sFname = getImageTypeName($formatTo);
    if ((ImageTypes() & getImageTypeCode($formatTo)) == 0 || $sFname == '')
        return IFR_UNSUPPORTED_THUMBNAIL;
    $imageTo = "Image$sFname";
    $imageTo($sHandle, $fnameTo);
    chmod($fnameTo, 0644);
    
    return IFR_OK;
}

function imagePostingData($posting) {
    if (!is_a($posting, 'Posting'))
        return '!Posting';
    $s = 'Posting[image]{';
    foreach (array('id', 'small_image', 'small_image_x', 'small_image_y',
                   'small_image_format', 'large_image', 'large_image_x',
                   'large_image_y', 'large_image_size', 'large_image_format',
                   'large_image_filename') as $key)
        $s .= "$key => ".debugLogData($posting->$key).', ';
    $s .= '}';
    return $s;
}
?>
