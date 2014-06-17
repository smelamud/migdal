<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/bug.php');
require_once('lib/debug-log.php');
require_once('lib/image-types.php');
require_once('lib/sql.php');

function deleteImageFiles($id, $small_image, $large_image,
                          $large_image_format) {
    global $thumbnailType;
    
    debugLog(LL_FUNCTIONS, 'deleteImageFiles(id=%,small_image=%,'.
             'large_image=%,large_image_format=%)',
             array($id, $small_image, $large_image, $large_image_format));
    $smallExt = getImageExtension($thumbnailType);
    $largeExt = getImageExtension($large_image_format);
    if ($large_image != 0) {
        @unlink(getImagePath($id, $smallExt, $small_image, 'small'));
        @unlink(getImagePath($id, $largeExt, $large_image, 'large'));
    } else {
        @unlink(getImagePath($id, $largeExt, $small_image, 'small'));
    }
}

function moveImageFiles($id, $destid, $small_image, $large_image,
                        $large_image_format) {
    global $thumbnailType;
    
    $smallExt = getImageExtension($thumbnailType);
    $largeExt = getImageExtension($large_image_format);
    if ($large_image != 0) {
        rename(getImagePath($id, $smallExt, $small_image, 'small'),
               getImagePath($destid, $smallExt, $small_image, 'small'));
        rename(getImagePath($id, $largeExt, $large_image, 'large'),
               getImagePath($destid, $largeExt, $large_image, 'large'));
    } else {
        rename(getImagePath($id, $largeExt, $small_image, 'small'),
               getImagePath($destid, $largeExt, $small_image, 'small'));
    }
}

function getImageFilename($id, $ext, $fileId = 0, $size = 'large') {
    return "migdal-$fileId.$ext";
}

function parseImageFilename($fname) {
    list($name, $ext) = explode('.', basename($fname));
    $parts = explode('-', $name);
    $info  = array();
    if ($parts[0] != 'migdal')
        return $info;
    $info['ext'] = $ext;
    $info['size'] = 'small'; // for backward compatibility
    $info['entry_id'] = 0; // for backward compatibility
    if (isset($parts[1]))
        $info['file_id'] = $parts[1];
    return $info;
}

function getImagePath($id, $ext, $fileId = 0, $size = 'large') {
    global $imageDir;
    
    $fname = getImageFilename($id, $ext, $fileId, $size);
    return "$imageDir/$fname";
}

function getImageURL($id, $ext, $fileId = 0, $size = 'large') {
    global $imageURL;
    
    $fname = getImageFilename($id, $ext, $fileId, $size);
    if ($imageURL[0] != '/')
        $imageURL = "/$imageURL";
    return "$imageURL/$fname";
}

function imageFileExists($id, $format, $fileId = 0, $size = 'large') {
    return file_exists(getImagePath($id, getMimeExtension($format), $fileId,
                                    $size));
}

function setMaxImageFileId($max_id) {
    sql("update image_files_c
         set max_id=$max_id",
        __FUNCTION__);
}

function getNextImageFileId() {
    sql('lock tables image_files_c write',
        __FUNCTION__, 'lock');
    $result = sql('select max_id
                   from image_files_c',
                  __FUNCTION__, 'select');
    $id = mysql_num_rows($result) > 0 ? mysql_result($result, 0, 0) : 0;
    sql('update image_files_c
         set max_id=max_id+1',
        __FUNCTION__, 'update');
    sql('unlock tables',
        __FUNCTION__, 'unlock');
    return $id;
}

// Если появятся ссылки на файлы картинок из других таблиц и полей, не забыть
// упомянуть их здесь
function deleteObsoleteImageFiles() {
    global $imageDir, $imageFileTimeout;
    
    $used   = array();
    $result = sql('select small_image,large_image
                   from entries
                   where small_image<>0 or large_image<>0',
                  __FUNCTION__);
    while ($row = mysql_fetch_array($result)) {
        if ($row[0] != 0)
            $used[$row[0]] = true;
        if ($row[1] != 0)
            $used[$row[1]] = true;
    }
    $dh = opendir($imageDir);
    while (($fname = readdir($dh)) !== false) {
        $ffname = "$imageDir/$fname";
        if (is_link($ffname)) {
            if (!file_exists("$imageDir/".readlink($ffname)))
                @unlink("$ffname");
            continue;
        }
        if (!($info = parseImageFilename($fname)))
            continue;
        if (!isset($used[$info['file_id']]) || !$used[$info['file_id']]) {
            $stat = stat($ffname);
            if (time() - $stat['mtime'] > $imageFileTimeout * 3600)
                @unlink($ffname);
        }
    }
    closedir($dh);
}
?>
