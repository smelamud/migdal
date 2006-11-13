<?php
# @(#) $Id$

// Large image
define('IU_IMAGE',0x0F);
define('IU_MANUAL',0x00); // Dimensions are set manually by user
define('IU_RESIZE',0x01); // Resize automatically
// Small image (thumbnail)
define('IU_THUMB',0xF0);
define('IU_THUMB_NONE',0x10);     // Thumbnail is not needed
define('IU_THUMB_MANUAL',0x20);   // Thumbnail is uploaded by user
define('IU_THUMB_RESIZE',0x30);   // Thumbnail is uploaded by user and resized
                                  // automatically
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
?>
