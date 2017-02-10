<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/debug-log.php');
require_once('lib/image-files.php');
require_once('lib/image-file-transforms.php');
require_once('lib/image-types.php');

const EIFU_INVALID_FILE = 1;
const EIFU_FILE_LARGE = 2;
const EIFU_INVALID_IMAGE = 3;
const EIFU_INVALID_IMAGE_TYPE = 4;
const EIFU_WRONG_IMAGE_SIZE = 5;
const EIFU_CANNOT_MOVE = 6;
const EIFU_CANNOT_READ = 7;
const EIFU_CANNOT_WRITE = 8;
const EIFU_IMAGE_LARGE = 9;

function imageUploadUserError($err, $isThumbnail) {
    switch ($err) {
        case EIFU_INVALID_FILE:
        case EIFU_INVALID_IMAGE:
        case EIFU_INVALID_IMAGE_TYPE:
            return isThumbnail ? EIU_UNKNOWN_IMAGE : EIU_UNKNOWN_THUMBNAIL;

        case EIFU_FILE_LARGE:
            return isThumbnail ? EIU_IMAGE_LARGE : EIU_THUMBNAIL_LARGE;

        case EIFU_IMAGE_LARGE:
            return isThumbnail ? EIU_LARGE_IMAGE_SIZE
                               : EIU_LARGE_THUMBNAIL_SIZE;

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
 *     auto - thumbnail is created automatically by resizing
 *     none - thumbnail is not needed
 *     manual - thumbnail is uploaded by user
 *     resize - thumbnail is uploaded by user and resized automatically
 *     clip - thumbnail is created automatically by clipping
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
        $posting->setSmallImage(0);
        $posting->setSmallImageX(0);
        $posting->setSmallImageY(0);
        $posting->setSmallImageFormat('');
        $posting->setLargeImage(0);
        $posting->setLargeImageX(0);
        $posting->setLargeImageY(0);
        $posting->setLargeImageSize(0);
        $posting->setLargeImageFormat('');
        $posting->setLargeImageFilename('');
    }

    switch ($imageFlag) {
        case 'resize':
            $exactX = 0;
            $exactY = 0;
            $maxX = 0;
            $maxY = 0;
            $transform = IFT_RESIZE;
            $transformX = $imageMaxX;
            $transformY = $imageMaxY;
            break;

        default:
            $exactX = $imageExactX;
            $exactY = $imageExactY;
            $maxX = $imageMaxX;
            $maxY = $imageMaxY;
            $transform = IFT_NULL;
            $transformX = 0;
            $transformY = 0;
    }
    $largeImageFile = uploadImageFile($name, $exactX, $exactY, $maxX, $maxY,
                                      $transform, $transformX, $transformY);
    if (!($largeImageFile instanceof ImageFile))
        return imageUploadUserError($largeImageFile, false);
    if ($largeImageFile->getId() == 0 && $posting->getLargeImage() != 0)
        $largeImageFile = getImageFileById($posting->getLargeImage());

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
                if ($posting->getSmallImage() != 0)
                    $smallImageFile = getImageFileById(
                                        $posting->getSmallImage());
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

        case 'clip':
            $smallImageFile = thumbnailImageFile($largeImageFile, IFT_CLIP,
                                                 $thumbExactX, $thumbExactY);
            if (!($smallImageFile instanceof ImageFile))
                return imageUploadUserError($smallImageFile, true);
            if ($smallImageFile->getId() == 0)
                $smallImageFile = $largeImageFile;
            break;

        case 'none':
        default:
            $smallImageFile = $largeImageFile;
    }

    $posting->setSmallImage($smallImageFile->getId());
    $posting->setSmallImageX($smallImageFile->getSizeX());
    $posting->setSmallImageY($smallImageFile->getSizeY());
    $posting->setSmallImageFormat($smallImageFile->getMimeType());
    $posting->setLargeImage($largeImageFile->getId());
    $posting->setLargeImageX($largeImageFile->getSizeX());
    $posting->setLargeImageY($largeImageFile->getSizeY());
    $posting->setLargeImageSize($largeImageFile->getFileSize());
    $posting->setLargeImageFormat($largeImageFile->getMimeType());
    if (isset($_FILES[$name]) && $_FILES[$name]['tmp_name'] != '')
        $posting->setLargeImageFilename($_FILES[$name]['name']);

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
    list($sizeX, $sizeY) = $imageInfo;
    $mimeType = $imageInfo['mime'];
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
?>