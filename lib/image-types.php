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
?>
