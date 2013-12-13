<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/entries.php');
require_once('lib/selectiterator.php');
require_once('lib/bug.php');
require_once('lib/debug-log.php');
require_once('lib/tmptexts.php');
require_once('lib/text.php');
require_once('lib/image-types.php');
require_once('lib/sql.php');
require_once('lib/text-any.php');

class Image
      extends Entry
{

function __construct($row)
{
global $tfRegular;

$this->entry=ENT_IMAGE;
$this->body_format=$tfRegular;
parent::__construct($row);
}

function setup($vars)
{
global $tfRegular;

if(!isset($vars['edittag']) || !$vars['edittag'])
  return;
$this->up=$vars['postid'];
$this->body_format=$tfRegular;
$this->title=$vars['title'];
$this->title_xml=anyToXML($this->title,$this->body_format,MTEXT_LINE);
$this->small_image=$vars['small_image'];
$this->small_image_x=$vars['small_image_x'];
$this->small_image_y=$vars['small_image_y'];
$this->large_image=$vars['large_image'];
$this->large_image_x=$vars['large_image_x'];
$this->large_image_y=$vars['large_image_y'];
$this->large_image_size=$vars['large_image_size'];
$this->large_image_format=$vars['large_image_format'];
$this->large_image_filename=$vars['large_image_filename'];
}

}

class ImagesIterator
        extends SelectIterator {

    public function __construct($postid) {
        // Показываем все нижестоящие entries, в которых есть картинка, не обязательно
        // типа ENT_IMAGE
        parent::__construct(
            'Entry',
            "select id,ident,entry,up,track,catalog,parent_id,
                    orig_id,user_id,group_id,perms,disabled,title,
                    title_xml,body_format,sent,created,modified,
                    accessed,small_image,small_image_x,small_image_y,
                    large_image,large_image_x,large_image_y,
                    large_image_size,large_image_format,
                    large_image_filename,count(entry_id) as inserted
             from entries
                  left join inner_images
                       on entries.id=image_id
             where up=$postid and small_image<>0
             group by id");
    }

}

function storeImage(&$image) {
    global $userId;
    
    $vars = array(
        'ident' => $image->ident,
        'up' => $image->up,
        'parent_id' => $image->parent_id,
        'user_id' => $image->user_id,
        'group_id' => $image->group_id,
        'perms' => $image->perms,
        'title' => $image->title,
        'title_xml' => $image->title_xml,
        'body_format' => $image->body_format,
        'small_image' => $image->small_image,
        'small_image_x' => $image->small_image_x,
        'small_image_y' => $image->small_image_y,
        'large_image' => $image->large_image,
        'large_image_x' => $image->large_image_x,
        'large_image_y' => $image->large_image_y,
        'large_image_size' => $image->large_image_size,
        'large_image_format' => $image->large_image_format,
        'large_image_filename' => $image->large_image_filename,
        'modified' => sqlNow()
    );
    if ($image->id) {
        $image->track = trackById('entries', $image->id);
        $result = sql(sqlUpdate('entries',
                                $vars,
                                array('id' => $image->id)),
                      __FUNCTION__, 'update');
        updateCatalogs($image->track);
        replaceTracksToUp('entries', $image->track, $image->up, $image->id);
    } else {
        $vars['entry'] = $image->entry;
        $vars['sent'] = sqlNow();
        $vars['created'] = sqlNow();
        $vars['track'] = (string) time();
        $result = sql(sqlInsert('entries',
                                $vars),
                      __FUNCTION__, 'insert');
        $image->id = sql_insert_id();
        setOrigIdToEntryId($image);
        createTrack('entries', $image->id);
        updateCatalogs(trackById('entries', $image->id));
    }
    return $result;
}

function getImageById($id) {
    $result = sql("select id,ident,entry,up,track,catalog,parent_id,orig_id,
                          user_id,group_id,perms,disabled,title,title_xml,
                          body_format,sent,created,modified,accessed,
                          small_image,small_image_x,small_image_y,
                          large_image,large_image_x,large_image_y,
                          large_image_size,large_image_format,
                          large_image_filename
                   from entries
                   where id=$id",
                  __FUNCTION__);
    return new Image(mysql_num_rows($result) > 0 ? mysql_fetch_assoc($result)
                                                 : array());
}

function deleteImage($id, $small_image, $large_image, $large_image_format) {
    sql("delete
         from inner_images
         where image_id=$id",
        __FUNCTION__, 'inner');
    sql("delete
         from entries
         where id=$id",
        __FUNCTION__, 'entry');
    deleteImageFiles($id, $small_image, $large_image, $large_image_format);
}

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
        @unlink(getImagePath($id, $smallExt, 0, 'small'));
        @unlink(getImagePath($id, $largeExt, 0, 'large'));
    } else {
        @unlink(getImagePath($id, $largeExt, $small_image, 'small'));
        @unlink(getImagePath($id, $largeExt, $small_image, 'large'));
        @unlink(getImagePath($id, $largeExt, 0, 'small'));
        @unlink(getImagePath($id, $largeExt, 0, 'large'));
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
        @unlink(getImagePath($id, $smallExt, 0, 'small'));
        symlink(getImagePath($destid, $smallExt, $small_image, 'small'),
                getImagePath($destid, $smallExt, 0, 'small'));
        @unlink(getImagePath($id, $largeExt, 0, 'large'));
        symlink(getImagePath($destid, $largeExt, $large_image, 'large'),
                getImagePath($destid, $largeExt, 0, 'large'));
    } else {
        rename(getImagePath($id, $largeExt, $small_image, 'small'),
               getImagePath($destid, $largeExt, $small_image, 'small'));
        @unlink(getImagePath($id, $largeExt, $small_image, 'large'));
        symlink(getImagePath($destid, $largeExt, $small_image, 'small'),
                getImagePath($destid, $largeExt, $small_image, 'large'));
        @unlink(getImagePath($id, $largeExt, 0, 'small'));
        symlink(getImagePath($destid, $largeExt, $small_image, 'small'),
                getImagePath($destid, $largeExt, 0, 'small'));
        @unlink(getImagePath($id, $largeExt, 0, 'large'));
        symlink(getImagePath($destid, $largeExt, $small_image, 'large'),
                getImagePath($destid, $largeExt, 0, 'large'));
    }
}

function getImageFilename($id, $ext, $fileId = 0, $size = 'large') {
    if ($size != '' && $size != 'large')
        $sizeC = "-$size";
    else
        $sizeC = '';
    if ($fileId != 0)
        $fileC = "-$fileId";
    else
        $fileC = '';
    return "migdal$sizeC-$id$fileC.$ext";
}

function parseImageFilename($fname) {
    list($name, $ext) = explode('.', basename($fname));
    $parts = explode('-', $name);
    $info  = array();
    if ($parts[0] != 'migdal')
        return $info;
    $info['ext'] = $ext;
    if ($parts[1] == 'small') {
        $info['size'] = 'small';
        $pos = 2;
    } else {
        $info['size'] = 'large';
        $pos = 1;
    }
    $info['entry_id'] = $parts[$pos];
    if (isset($parts[$pos + 1]))
        $info['file_id'] = $parts[$pos + 1];
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
    sql("update image_files
         set max_id=$max_id",
        __FUNCTION__);
}

function getNextImageFileId() {
    sql('lock tables image_files write',
        __FUNCTION__, 'lock');
    $result = sql('select max_id
                   from image_files',
                  __FUNCTION__, 'select');
    $id = mysql_num_rows($result) > 0 ? mysql_result($result, 0, 0) : 0;
    sql('update image_files
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
