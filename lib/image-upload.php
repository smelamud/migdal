<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/debug-log.php');
require_once('lib/image-files.php');
require_once('lib/image-types.php');
require_once('lib/image-upload-flags.php');

// obsolete
function imageUpload($name, Entry $posting, $flags, $thumbExactX, $thumbExactY,
                     $thumbMaxX, $thumbMaxY, $imageExactX, $imageExactY,
                     $imageMaxX, $imageMaxY, $del, $resizeIfExists = false) {
    global $maxImageSize, $thumbnailType, $imageDir;
    
    return EG_OK; //blocked
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
        $largeFilename = getImageFilename($file['type'], $largeId);
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
        $oldFilename = getImageFilename($posting->large_image_format,
                                        $posting->getImage());
        $oldName = "$imageDir/$oldFilename";
        $largeId = getNextImageFileId();
        $largeFilename = getImageFilename($posting->large_image_format,
                                          $largeId);
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
        $smallName = getImagePath($thumbnailType, $smallId);
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
            $smallName = getImagePath($thumbnailType, $smallId);
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
            $smallName = getImagePath($thumbnailType, $smallId);
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
        $smallFilename = getImageFilename($posting->large_image_format,
                                          $largeId);
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

// obsolete
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

// *** New functions ***

const EIFU_INVALID_FILE = 1;
const EIFU_FILE_LARGE = 2;
const EIFU_INVALID_IMAGE = 3;
const EIFU_INVALID_IMAGE_TYPE = 4;
const EIFU_WRONG_IMAGE_SIZE = 5;
const EIFU_CANNOT_MOVE = 6;
const EIFU_CANNOT_READ = 7;
const EIFU_CANNOT_WRITE = 8;

function imageUploadUserError($err, $isThumbnail) {
    switch ($err) {
        case EIFU_INVALID_FILE:
        case EIFU_INVALID_IMAGE:
        case EIFU_INVALID_IMAGE_TYPE:
            return isThumbnail ? EIU_UNKNOWN_IMAGE : EIU_UNKNOWN_THUMBNAIL;

        case EIFU_FILE_LARGE:
            return isThumbnail ? EIU_IMAGE_LARGE : EIU_THUMBNAIL_LARGE;

        case EIFU_WRONG_IMAGE_SIZE:
            return isThumbnail ? EIU_WRONG_IMAGE_SIZE
                               : EIU_WRONG_THUMBNAIL_SIZE;

        case EIFU_CANNOT_MOVE:
        case EIFU_CANNOT_READ:
        case EIFU_CANNOT_WRITE:
        default:
            return EIU_INTERNAL_ERROR;
    }
}

/*
 * Standard image flags: "<thumbnail>-<image>"
 *
 * <image> is one of:
 *     manual - image is uploaded by user
 *     resize - image is uploaded by user and resized automatically
 *
 * <thumbnail> is one of:
 *     auto - thumbnail is created automatically
 *     none - thumbnail is not needed
 *     manual - thumbnail is uploaded by user
 *     resize - thumbnail is uploaded by user and resized automatically
 */
function uploadStandardImage($name, Entry $posting, $flags,
        $thumbExactX, $thumbExactY, $thumbMaxX, $thumbMaxY,
        $imageExactX, $imageExactY, $imageMaxX, $imageMaxY, $deleteIfExists) {
    @list($thumbFlag, $imageFlag) = explode('-', $flags);
    if (!isset($imageFlag) || $imageFlag == '')
        $imageFlag = 'manual';
    if (!isset($thumbFlag) || $thumbFlag == '')
        $thumbFlag = 'auto';

    if ($deleteIfExists) {
        $posting->small_image = 0;
        $posting->small_image_x = $posting->small_image_y = 0;
        $posting->small_image_format = '';
        $posting->large_image = 0;
        $posting->large_image_x = $posting->large_image_y = 0;
        $posting->large_image_size = 0;
        $posting->large_image_format = '';
        $posting->large_image_filename = '';
    }

    switch ($imageFlag) {
        case 'resize':
            $transform = IFT_RESIZE;
            $transformX = $imageMaxX;
            $transformY = $imageMaxY;
            break;

        default:
            $transform = IFT_NULL;
            $transformX = 0;
            $transformY = 0;
    }
    $largeImageFile = uploadImageFile($name, $imageExactX, $imageExactY,
        $imageMaxX, $imageMaxY, $transform, $transformX, $transformY);
    if (!($largeImageFile instanceof ImageFile))
        return imageUploadUserError($largeImageFile, false);
    if ($largeImageFile->getId() == 0 && $posting->large_image != 0)
        $largeImageFile = getImageFileById($posting->large_image);

    switch ($thumbFlag) {
        case 'manual':
        case 'resize':
            switch ($thumbFlag) {
                case 'resize':
                    $transform = IFT_RESIZE;
                    $transformX = $thumbMaxX;
                    $transformY = $thumbMaxY;
                    break;

                default:
                    $transform = IFT_NULL;
                    $transformX = 0;
                    $transformY = 0;
            }
            $smallImageFile = uploadImageFile("{$name}_thumb",
                $thumbExactX, $thumbExactY, $thumbMaxX, $thumbMaxY,
                $transform, $transformX, $transformY);
            if (!($smallImageFile instanceof ImageFile))
                return imageUploadUserError($smallImageFile, true);
            if ($smallImageFile->getId() == 0) {
                if ($posting->small_image != 0)
                    $smallImageFile = getImageFileById($posting->small_image);
                else
                    $smallImageFile = $largeImageFile;
            }
            break;

        case 'auto':
            $smallImageFile = thumbnailImageFile($largeImageFile, IFT_RESIZE,
                $thumbMaxX, $thumbMaxY);
            if (!($smallImageFile instanceof ImageFile))
                return imageUploadUserError($smallImageFile, true);
            if ($smallImageFile->getId() == 0)
                $smallImageFile = $largeImageFile;
            break;

        case 'none':
        default:
            $smallImageFile = $largeImageFile;
    }

    $posting->small_image = $smallImageFile->getId();
    $posting->small_image_x = $smallImageFile->getSizeX();
    $posting->small_image_y = $smallImageFile->getSizeY();
    $posting->small_image_format = $smallImageFile->getMimeType();
    $posting->large_image = $largeImageFile->getId();
    $posting->large_image_x = $largeImageFile->getSizeX();
    $posting->large_image_y = $largeImageFile->getSizeY();
    $posting->large_image_size = $largeImageFile->getFileSize();
    $posting->large_image_format = $largeImageFile->getMimeType();
    if (isset($_FILES[$name]))
        $posting->large_image_filename = $_FILES[$name]['name'];

    return EG_OK;
}

function uploadImageFile($name, $exactX, $exactY, $maxX, $maxY, $transform,
                         $transformX, $transformY) {
    global $maxImageSize;

    if (!isset($_FILES[$name]) || $_FILES[$name]['tmp_name'] == '')
        return new ImageFile(array('id' => 0));
    $file = $_FILES[$name];
    if (!is_uploaded_file($file['tmp_name'])
        || filesize($file['tmp_name']) != $file['size'])
        return EIFU_INVALID_FILE;
    if ($file['size'] > $maxImageSize)
        return EIFU_FILE_LARGE;
    $imageFile = new ImageFile();
    $imageFile->setFileSize($file['size']);
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false)
        return EIFU_INVALID_IMAGE;
    list($sizeX, $sizeY, , , $mimeType) = $imageInfo;
    if (!isImageTypeSupported($mimeType))
        return EIFU_INVALID_IMAGE_TYPE;
    $imageFile->setMimeType($mimeType);
    if ($exactX > 0 && ($sizeX - $exactX) > 1
        || $exactY > 0 && ($sizeY - $exactY) > 1)
        return EIFU_WRONG_IMAGE_SIZE;
    if ($maxX > 0 && $sizeX > $maxX
        || $maxY > 0 && $sizeY > $maxY)
        return EIFU_IMAGE_LARGE;
    $imageFile->setSizeX($sizeX);
    $imageFile->setSizeY($sizeY);
    storeImageFile($imageFile);
    if (!move_uploaded_file($file['tmp_name'], $imageFile->getPath())) {
        deleteImageFile($imageFile->getMimeType(), $imageFile->getId());
        return EIFU_CANNOT_MOVE;
    } else {
        chmod($imageFile->getPath(), 0644);
    }

    if ($transform == IFT_NULL)
        return $imageFile;

    $handle = readImageFile($imageFile->getMimeType(), $imageFile->getId());
    if ($handle === false)
        return EIFU_CANNOT_READ;
    transformImage($handle, $transform, $transformX, $transformY);
    $imageFile->setSizeX(imagesx($handle));
    $imageFile->setSizeY(imagesy($handle));
    $ok = writeImageFile($handle, $imageFile->getMimeType(),
                         $imageFile->getId());
    imagedestroy($handle);
    if (!$ok)
        return EIFU_CANNOT_WRITE;
    $imageFile->setFileSize(filesize($imageFile->getPath()));
    storeImageFile($imageFile);
    
    return $imageFile;
}

function thumbnailImageFile(ImageFile $imageFile, $transform,
                            $transformX, $transformY) {
    global $thumbnailType;

    if ($imageFile->getId() == 0
        || isImageTransformed($imageFile, $transform, $transformX, $transformY))
        return $imageFile;
    $readyFile = getTransformedImageBySource($imageFile->getId(), $transform,
                                             $transformX, $transformY);
    if (!is_null($readyFile) && $readyFile->getId() != 0)
        return $readyFile;
    $handle = readImageFile($imageFile->getMimeType(), $imageFile->getId());
    if ($handle === false)
        return EIFU_CANNOT_READ;
    transformImage($handle, $transform, $transformX, $transformY);
    $sizeX = imagesx($handle);
    $sizeY = imagesy($handle);
    $readyFile = getTransformedImageByResult($imageFile->getId(), $transform,
                                             $sizeX, $sizeY);
    if (!is_null($readyFile) && $readyFile->getId() != 0) {
        imagedestroy($handle);
        return $readyFile;
    }

    $destFile = new ImageFile();
    $destFile->setMimeType($thumbnailType);
    $destFile->setSizeX($sizeX);
    $destFile->setSizeY($sizeY);
    storeImageFile($destFile);
    $ok = writeImageFile($handle, $destFile->getMimeType(), $destFile->getId());
    imagedestroy($handle);
    if (!$ok) {
        deleteImageFile($destFile->getMimeType(), $destFile->getId());
        return EIFU_CANNOT_WRITE;
    }
    $destFile->setFileSize(filesize($destFile->getPath()));
    storeImageFile($destFile);

    $trans = new ImageFileTransform();
    $trans->setDestId($destFile->getId());
    $trans->setOrigId($imageFile->getId());
    $trans->setTransform($transform);
    $trans->setSizeX($transformX);
    $trans->setSizeY($transformY);
    storeImageFileTransform($trans);

    return $destFile;
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
