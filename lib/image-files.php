<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/bug.php');
require_once('lib/debug-log.php');
require_once('lib/image-types.php');
require_once('lib/sql.php');
require_once('lib/dataobject.php');

class ImageFile
        extends DataObject {

    protected $id = 0;
    protected $mime_type = '';
    protected $size_x = 0;
    protected $size_y = 0;
    protected $file_size = 0;
    protected $created = 0;
    protected $accessed = 0;

    public function __construct(array $row = array()) {
        parent::__construct($row);
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getMimeType() {
        return $this->mime_type;
    }

    public function setMimeType($mime_type) {
        $this->mime_type = $mime_type;
    }

    public function getSizeX() {
        return $this->size_x;
    }

    public function setSizeX($size_x) {
        $this->size_x = $size_x;
    }

    public function getSizeY() {
        return $this->size_y;
    }

    public function setSizeY($size_y) {
        $this->size_y = $size_y;
    }

    public function getFileSize() {
        return $this->file_size;
    }

    public function setFileSize($file_size) {
        $this->file_size = $file_size;
    }

    public function getCreated() {
        return strtotime($this->created);
    }

    public function getAccessed() {
        return strtotime($this->accessed);
    }

    public function getFilename() {
        return getImageFilename($this->mime_type, $this->id);
    }

    public function getPath() {
        return getImagePath($this->mime_type, $this->id);
    }

    public function getURL() {
        return getImageURL($this->mime_type, $this->id);
    }

}

function storeImageFile(ImageFile $imageFile) {
    $vars = array(
        'mime_type' => $imageFile->getMimeType(),
        'size_x' => $imageFile->getSizeX(),
        'size_y' => $imageFile->getSizeY(),
        'file_size' => $imageFile->getFileSize(),
        'accessed' => sqlNow()
    );
    if ($imageFile->getId()) {
        $result = sql(sqlUpdate('image_files',
                                $vars,
                                array('id' => $imageFile->getId())),
                      __FUNCTION__, 'update');
    } else {
        $vars['created'] = sqlNow();
        $result = sql(sqlInsert('image_files',
                                $vars),
                      __FUNCTION__, 'insert');
        $imageFile->setId(sql_insert_id());
    }
    return $result;
}

function deleteImageFile($format, $id) {
    if ($id == 0)
        return;
    @unlink(getImagePath($format, $id));
    sql("delete
         from image_files
         where id=$id",
        __FUNCTION__, 'image_files');
    sql("delete
         from image_file_transforms
         where dest_id=$id or orig_id=$id",
        __FUNCTION__, 'image_file_transforms');
}

function getImageFileById($id) {
    $result = sql("select id,mime_type,size_x,size_y,file_size
                   from image_files
                   where id=$id",
                  __FUNCTION__);
    return new ImageFile(mysql_num_rows($result) > 0
                         ? mysql_fetch_assoc($result)
                         : array());
}

function readImageFile($format, $id) {
    $typeName = getImageTypeName($format);
    if ($typeName == '')
        return false;
    $func = "imagecreatefrom$typeName";
    return $func(getImagePath($format, $id));
}

function writeImageFile($handle, $format, $id) {
    $typeName = getImageTypeName($format);
    if ($typeName == '')
        return false;
    $func = "image$typeName";
    $fileName = getImagePath($format, $id);
    $ok = $func($handle, $fileName);
    if ($ok)
        chmod($fileName, 0644);
    return $ok;
}

// obsolete
function deleteImageFiles($small_image, $small_image_format,
                          $large_image, $large_image_format) {
    debugLog(LL_FUNCTIONS, 'deleteImageFiles(small_image=%,'.
             'small_image_format=%,large_image=%,large_image_format=%)',
             array($small_image, $small_image_format,
                   $large_image, $large_image_format));
    @unlink(getImagePath($small_image_format, $small_image));
    if ($large_image != 0)
        @unlink(getImagePath($large_image_format, $large_image));
}

function getImageFilename($format, $fileId = 0) {
    return "migdal-$fileId.".getMimeExtension($format);
}

function parseImageFilename($fname) {
    list($name, $ext) = explode('.', basename($fname));
    $parts = explode('-', $name);
    $info  = array();
    if ($parts[0] != 'migdal')
        return $info;
    $info['ext'] = $ext;
    $info['format'] = getMimeType($ext);
    $info['size'] = 'small'; // for backward compatibility
    $info['entry_id'] = 0; // for backward compatibility
    if (isset($parts[1]))
        $info['file_id'] = $parts[1];
    return $info;
}

function getImagePath($format, $fileId = 0) {
    global $imageDir;
    
    $fname = getImageFilename($format, $fileId);
    return "$imageDir/$fname";
}

function getImageURL($format, $fileId = 0) {
    global $imageURL;
    
    $fname = getImageFilename($format, $fileId);
    if ($imageURL[0] != '/')
        $imageURL = "/$imageURL";
    return "$imageURL/$fname";
}

function imageFileExists($format, $fileId = 0) {
    return file_exists(getImagePath($format, $fileId));
}

// obsolete
function setMaxImageFileId($max_id) {
    sql("update image_files_c
         set max_id=$max_id",
        __FUNCTION__);
}

// obsolete
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
                deleteImageFile($info['format'], $info['file_id']);
        }
    }
    closedir($dh);
}
?>
