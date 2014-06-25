<?php
# @(#) $Id$

require_once('lib/bug.php');
require_once('lib/debug-log.php');
require_once('lib/sql.php');
require_once('lib/dataobject.php');

const IFT_NULL = 0;
const IFT_RESIZE = 1; // Resize proportionally
const IFT_CLIP = 2; // Fill the rect and clip around

class ImageFileTransform
        extends DataObject {

    protected $id = 0;
    protected $dest_id = 0;
    protected $orig_id = 0;
    protected $transform = 0;
    protected $size_x = 0;
    protected $size_y = 0;

    public function __construct(array $row = array()) {
        parent::__construct($row);
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getDestId() {
        return $this->dest_id;
    }

    public function setDestId($dest_id) {
        $this->dest_id = $dest_id;
    }

    public function getOrigId() {
        return $this->orig_id;
    }

    public function setOrigId($orig_id) {
        $this->orig_id = $orig_id;
    }

    public function getTransform() {
        return $this->transform;
    }

    public function setTransform($transform) {
        $this->transform = $transform;
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
    
}

function storeImageFileTransform(ImageFileTransform $imageFileTransform) {
    $vars = array(
        'dest_id' => $imageFileTransform->getDestId(),
        'orig_id' => $imageFileTransform->getOrigId(),
        'transform' => $imageFileTransform->getTransform(),
        'size_x' => $imageFileTransform->getSizeX(),
        'size_y' => $imageFileTransform->getSizeY()
    );
    if ($imageFileTransform->getId()) {
        $result = sql(sqlUpdate('image_file_transforms',
                                $vars,
                                array('id' => $imageFileTransform->getId())),
                      __FUNCTION__, 'update');
    } else {
        $result = sql(sqlInsert('image_file_transforms',
                                $vars),
                      __FUNCTION__, 'insert');
        $imageFileTransform->setId(sql_insert_id());
    }
    return $result;
}

function getTransformedImageBySource($id, $transform,
                                     $transformX, $transformY) {
    $result = sql("select image_files.id as id,mime_type,
                          image_files.size_x as size_x,
                          image_files.size_y as size_y,file_size
                   from image_files
                        left join image_file_transforms
                             on image_file_transforms.dest_id=image_files.id
                   where orig_id=$id and transform=$transform
                         and image_file_transforms.size_x=$transformX
                         and image_file_transforms.size_y=$transformY",
                  __FUNCTION__);
    return new ImageFile(mysql_num_rows($result) > 0
                         ? mysql_fetch_assoc($result)
                         : array());
}

function getTransformedImageByResult($id, $transform, $sizeX, $sizeY) {
    $result = sql("select image_files.id as id,mime_type,
                          image_files.size_x as size_x,
                          image_files.size_y as size_y,file_size
                   from image_files
                        left join image_file_transforms
                             on image_file_transforms.dest_id=image_files.id
                   where orig_id=$id and transform=$transform
                         and image_files.size_x=$sizeX
                         and image_files.size_y=$sizeY",
                  __FUNCTION__);
    return new ImageFile(mysql_num_rows($result) > 0
                         ? mysql_fetch_assoc($result)
                         : array());
}

function isImageTransformed(ImageFile $imageFile, $transform,
                            $transformX, $transformY) {
    switch ($transform) {
        case IFT_RESIZE:
            return ($transformX <= 0 || $imageFile->getSizeX() <= $transformX)
                && ($transformY <= 0 || $imageFile->getSizeY() <= $transformY);

        case IFT_CLIP:
            return ($transformX <= 0 || $imageFile->getSizeX() == $transformX)
                && ($transformY <= 0 || $imageFile->getSizeY() == $transformY);
    }
}

function transformImage(&$handle, $transform, $transformX, $transformY) {
    switch ($transform) {
        case IFT_RESIZE:
            resizeImage($handle, $transformX, $transformY);
            break;

        case IFT_CLIP:
            clipImage($handle, $transformX, $transformY);
            break;
    }
}

function resizeImage(&$handle, $maxX, $maxY) {
    // Calculate the dimensions
    $largeSizeX = imagesx($handle);
    $largeSizeY = imagesy($handle);

    $aspect = $largeSizeX / $largeSizeY;

    if ($maxX == 0)
        $maxX = 65535;
    if ($maxY == 0)
        $maxY = 65535;
    if ($largeSizeX > $maxX || $largeSizeY > $maxY) {
        $smallSizeX = $maxX;
        $smallSizeY = (int) ($smallSizeX / $aspect);
        if ($smallSizeY > $maxY) {
            $smallSizeY = $maxY;
            $smallSizeX = (int) ($smallSizeY * $aspect);
        }
    } else {
        $smallSizeX = $largeSizeX;
        $smallSizeY = $largeSizeY;
    }

    // Resize the image
    $smallHandle = imagecreatetruecolor($smallSizeX, $smallSizeY);
    imagecopyresampled($smallHandle, $handle, 0, 0, 0, 0,
                       $smallSizeX, $smallSizeY, $largeSizeX, $largeSizeY);
    imagedestroy($handle);
    $handle = $smallHandle;
}

function clipImage(&$handle, $clipX, $clipY) {
}
?>
