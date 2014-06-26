<?php
# @(#) $Id: daily.php 2762 2014-06-16 10:51:04Z balu $

require_once('conf/migdal.conf');
$debug=true;

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/sql.php');
require_once('lib/session.php');
require_once('lib/image-files.php');
require_once('lib/image-file-transforms.php');

dbOpen();
session(getShamesId());

$result = sql('select *
               from image_file_transforms',
              __FUNCTION__);
while ($row = mysql_fetch_assoc($result)) {
    $trans = new ImageFileTransform($row);
    $dest = getImageFileById($trans->getDestId());
    $orig = getImageFileById($trans->getOrigId());
    echo "{$trans->getId()} {$dest->getId()} {$orig->getId()}\n";
    $handle = readImageFile($orig->getMimeType(), $orig->getId());
    if ($handle === false)
        break;
    if ($orig->getSizeX() > 900 || $orig->getSizeY() > 900) {
        resizeImage($handle, 900, 900);
        writeImageFile($handle, $orig->getMimeType(), $orig->getId());
        $orig->setSizeX(imagesx($handle));
        $orig->setSizeY(imagesy($handle));
        $orig->setFileSize(filesize($orig->getPath()));
        storeImageFile($orig);
    }
    transformImage($handle, $trans->getTransform(),
                   $trans->getSizeX(), $trans->getSizeY());
    writeImageFile($handle, $dest->getMimeType(), $dest->getId());
    $dest->setSizeX(imagesx($handle));
    $dest->setSizeY(imagesy($handle));
    $dest->setFileSize(filesize($dest->getPath()));
    storeImageFile($dest);
    imagedestroy($handle);
}
dbClose();
?>
