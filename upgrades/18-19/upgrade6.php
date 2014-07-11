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
require_once('lib/entries.php');
require_once('lib/postings.php');

dbOpen();
session(getShamesId());

$result = sql('select trans.id as id, trans.dest_id as dest_id,
                      trans.orig_id as orig_id
               from entries
                    left join image_file_transforms as trans
                         on entries.large_image = trans.orig_id
               where entry='.ENT_POSTING.' and grp in (2,8,16,32,64,128,1024,32768)
                     and trans.size_x < 120 and trans.size_y < 120
                     and entries.large_image<>entries.small_image',
              __FUNCTION__, 'select');
while ($row = mysql_fetch_assoc($result)) {
    $trans = new ImageFileTransform($row);
    $dest = getImageFileById($trans->getDestId());
    $orig = getImageFileById($trans->getOrigId());
    echo "{$trans->getId()} {$dest->getId()} {$orig->getId()}\n";
    $handle = readImageFile($orig->getMimeType(), $orig->getId());
    if ($handle === false)
        break;
    $trans->setTransform(IFT_RESIZE);
    $trans->setSizeX(120);
    $trans->setSizeY(120);
    transformImage($handle, $trans->getTransform(),
                   $trans->getSizeX(), $trans->getSizeY());
    writeImageFile($handle, $dest->getMimeType(), $dest->getId());
    $dest->setSizeX(imagesx($handle));
    $dest->setSizeY(imagesy($handle));
    $dest->setFileSize(filesize($dest->getPath()));
    storeImageFile($dest);
    sql("update entries
         set small_image_x={$dest->getSizeX()},
             small_image_y={$dest->getSizeY()}
         where small_image={$dest->getId()}",
        __FUNCTION__, 'update');
    imagedestroy($handle);
    storeImageFileTransform($trans);
}
dbClose();
?>
