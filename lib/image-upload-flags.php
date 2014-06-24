<?php
# @(#) $Id$

// Large image
const IU_IMAGE = 0x0F;
const IU_MANUAL = 0x00; // Dimensions are set manually by user
const IU_RESIZE = 0x01; // Resize automatically
// Small image (thumbnail)
const IU_THUMB = 0xF0;
const IU_THUMB_AUTO = 0x00; // Thumbnail is created automatically
const IU_THUMB_NONE = 0x10; // Thumbnail is not needed
const IU_THUMB_MANUAL = 0x20; // Thumbnail is uploaded by user
const IU_THUMB_RESIZE = 0x30; // Thumbnail is uploaded by user and resized automatically
const IU_THUMB_NO_GLASS = 0x40; // Thumbnail is created automatically without glass icon

$imageUploadFlags = array(
    '' => IU_MANUAL,
    'manual' => IU_MANUAL,
    'resize' => IU_RESIZE,
    '-' => IU_THUMB_AUTO,
    'auto-' => IU_THUMB_AUTO,
    'none-' => IU_THUMB_NONE,
    'manual-' => IU_THUMB_MANUAL,
    'resize-' => IU_THUMB_RESIZE,
    'noglass-' => IU_THUMB_NO_GLASS
);

// obsolete
function imageUploadFlags($s) {
    global $imageUploadFlags;
    
    @list($thumb, $image) = explode('-', $s);
    return $imageUploadFlags["$thumb-"] | $imageUploadFlags[$image];
}
?>
