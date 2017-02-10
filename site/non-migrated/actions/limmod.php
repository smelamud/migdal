<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/postings.php');
require_once('lib/images.php');
require_once('lib/image-upload.php');
require_once('lib/errors.php');
require_once('lib/modbits.php');
require_once('lib/sql.php');

function modifyImage($image, $original) {
    if ($original->getId() != 0 && !$original->isWritable())
        return ELIM_NO_EDIT;
    if ($image->getUpValue() == 0)
        return ELIM_NO_POSTING;
    $correct = validateHierarchy($image->getParentId(), $image->getUpValue(),
                                 ENT_IMAGE, $image->getId());
    if ($correct != EG_OK)
        return $correct;
    $posting = getPostingById($image->getUpValue());
    if (!$posting->isAppendable())
        return ELIM_POSTING_APPEND;
    if (!$image->hasSmallImage())
        return ELIM_IMAGE_ABSENT;
    storeImage($image);
    setPremoderates($posting, $posting);
    return EG_OK;
}

function insertImage($inner, $image, $append) {
    if ($inner->getImageId() == 0 || !$append && $image->getId() != 0)
        $inner->setImageId($image->getId());
    if ($inner->getEntryId() == 0)
        return ELIM_NO_POSTING;
    $perms = getPermsById($inner->getEntryId());
    if (!$perms->isWritable())
        return ELIM_POSTING_WRITE;
    storeInnerImage($inner);
    return EG_OK;
}

httpRequestString('okdir');
httpRequestString('faildir');

httpRequestInteger('edittag');
httpRequestInteger('postid');
httpRequestInteger('editid');
httpRequestInteger('par');
httpRequestInteger('x');
httpRequestInteger('y');
httpRequestInteger('placement');
httpRequestInteger('insert');
httpRequestInteger('append');
httpRequestInteger('small_image');
httpRequestInteger('small_image_x');
httpRequestInteger('small_image_y');
httpRequestString('small_image_format');
httpRequestInteger('has_large_image');
httpRequestInteger('large_image');
httpRequestInteger('large_image_x');
httpRequestInteger('large_image_y');
httpRequestInteger('large_image_size');
httpRequestString('large_image_format');
httpRequestString('large_image_filename');
httpRequestInteger('del_image');
httpRequestInteger('no_resize');
httpRequestString('title');

dbOpen();
session();
$image = getImageById($append ? 0 : $editid);
$original = clone $image;
$image->setup($Args);
$err = uploadStandardImage('image_file', $image,
                           !$no_resize
                           ? ($has_large_image ? 'auto-resize' : 'none-resize')
                           : ($has_large_image ? 'auto-manual' : 'none-manual'),
                           0, 0, $small_image_x, $small_image_y,
                           0, 0,
                           !$no_resize ? $innerImageMaxWidth : 0,
                           !$no_resize ? $innerImageMaxHeight : 0,
                           $del_image);
if ($err == EG_OK)
    $err = modifyImage($image, $original);
if ($insert) {
    if ($err == ELIM_IMAGE_ABSENT)
        $err = EG_OK;
    if ($err == EG_OK) {
        $inner = getInnerImageByParagraph($postid, $par, $x, $y);
        $inner->setup($Args);
        $err = insertImage($inner, $image, $append);
    }
}
if ($err == EG_OK) {
    if (!$insert)
        $okdir = remakeMakeURI(
            $okdir,
            $Args,
            array(
                'err',
                'title',
                'title_i',
                'edittag',
                'par',
                'x',
                'y',
                'postid',
                'placement',
                'insert',
                'append',
                'small_image',
                'small_image_x',
                'small_image_y',
                'small_image_format',
                'small_image_format_i',
                'large_image',
                'large_image_x',
                'large_image_y',
                'large_image_size',
                'large_image_format',
                'large_image_format_i',
                'large_image_filename',
                'large_image_filename_i',
                'has_large_image',
                'del_image',
                'no_resize',
                'okdir',
                'faildir'
            )
        );
    header("Location: $okdir");
} else {
    $smallImageFormatId = tmpTextSave($image->getSmallImageFormat());
    $largeImageFormatId = tmpTextSave($image->getLargeImageFormat());
    $largeImageFilenameId = tmpTextSave($image->getLargeImageFilename());
    $titleId = tmpTextSave($title);
    header(
        'Location: '.
        remakeMakeURI(
            $faildir,
            $Args,
            array(
                'title',
                'insert',
                'append',
                'okdir',
                'faildir'
            ),
            array(
                'err'     => $err,
                'small_image'   => $image->getSmallImage(),
                'small_image_x' => $image->getSmallImageX(),
                'small_image_y' => $image->getSmallImageY(),
                'small_image_format_i' => $smallImageFormatId,
                'large_image'   => $image->getLargeImage(),
                'large_image_x' => $image->getLargeImageX(),
                'large_image_y' => $image->getLargeImageY(),
                'large_image_size' => $image->getLargeImageSize(),
                'large_image_format_i' => $largeImageFormatId,
                'large_image_filename_i' => $largeImageFilenameId,
                'title_i' => $titleId,
                'no_resize' => $no_resize
            )
        ).'#error'
    );
}
dbClose();
?>
