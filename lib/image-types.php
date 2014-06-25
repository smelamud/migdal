<?php
# @(#) $Id$

require_once('lib/mime.php');

function getImageExtension($mime_type) {
    return getMimeExtension($mime_type);
}

$imageTypeNames = array(
    'image/pjpeg' => 'JPEG',
    'image/jpeg' => 'JPEG',
    'image/gif' => 'GIF',
    'image/x-png' => 'PNG',
    'image/png' => 'PNG'
);

function getImageTypeName($mime_type) {
    global $imageTypeNames;
    
    return isset($imageTypeNames[$mime_type]) ? $imageTypeNames[$mime_type]
                                              : '';
}

$imageTypeCodes = array(
    'image/pjpeg' => IMG_JPG,
    'image/jpeg' => IMG_JPG,
    'image/gif' => IMG_GIF,
    'image/x-png' => IMG_PNG,
    'image/png' => IMG_PNG
);

function getImageTypeCode($mime_type) {
    global $imageTypeCodes;
    
    return isset($imageTypeCodes[$mime_type]) ? $imageTypeCodes[$mime_type]
                                              : false;
}

function isImageTypeSupported($mime_type) {
    global $imageTypeCodes;
    
    return isset($imageTypeCodes[$mime_type])
           && (imagetypes() & $imageTypeCodes[$mime_type]) != 0;
}
?>
