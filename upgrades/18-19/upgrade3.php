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
               from image_files
               where file_size=0',
              __FUNCTION__);
while ($row = mysql_fetch_assoc($result)) {
    $file = new ImageFile($row);
    echo "{$file->getId()}\n";
    $file->setFileSize(filesize($file->getPath()));
    storeImageFile($file);
}
dbClose();
?>
