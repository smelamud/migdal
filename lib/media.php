<?php
require_once('lib/dataobject.php');
require_once('lib/sql.php');

class Media
        extends DataObject {

    private $id;
    private $mime_type;
    private $width;
    private $height;
    private $size;
    private $accessed;
    private $orig_id;
    private $trans_op;
    private $trans_width;
    private $trans_height;

    public __construct(array $row) {
        parent::__construct($row);
    }

    public getId() {
        return $this->id;
    }

    public setId($id) {
        $this->id = $id;
    }

    public getMimeType() {
        return $this->mime_type;
    }

    public setMimeType($mime_type) {
        $this->mime_type = $mime_type;
    }

    public getWidth() {
        return $this->width;
    }

    public setWidth($width) {
        $this->width = $width;
    }

    public getHeight() {
        return $this->height;
    }

    public setHeight($height) {
        $this->height = $height;
    }

    public getSize() {
        return $this->size;
    }

    public setSize($size) {
        $this->size = $size;
    }

    public getAccessed() {
        return strtotime($this->accessed);
    }

    public setAccessed($accessed) {
        $this->accessed = sqlDate($accessed);
    }

    public getOrigId() {
        return $this->orig_id;
    }

    public setOrigId($orig_id) {
        $this->orig_id = $orig_id;
    }

    public getTransOp() {
        return $this->trans_op;
    }

    public setTransOp($trans_op) {
        $this->trans_op = $trans_op;
    }

    public getTransWidth() {
        return $this->trans_width;
    }

    public setTransWidth($trans_width) {
        $this->trans_width = $trans_width;
    }

    public getTransHeight() {
        return $this->trans_height;
    }

    public setTransHeight($trans_height) {
        $this->trans_height = $trans_height;
    }

}

function storeMedia(Media $media) {
    $vars = array(
        'id' => $media->getId(),
        'mime_type' => $media->getMimeType(),
        'width' => $media->getWidth(),
        'height' => $media->getHeight(),
        'size' => $media->getSize(),
        'accessed' => $media->getAccessed(),
        'orig_id' => $media->getOrigId(),
        'trans_op' => $media->getTransOp(),
        'trans_width' => $media->getTransWidth(),
        'trans_height' => $media->getTransHeight()
    );
    return sql(sqlInsert('entries',
                         $vars),
               __FUNCTION__);
}
?>
