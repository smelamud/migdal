<?php
# @(#) $Id$

require_once('lib/bug.php');
require_once('lib/debug-log.php');
require_once('lib/sql.php');
require_once('lib/dataobject.php');

const IFT_RESIZE = 1; // Resize proportionally
const IFT_CLIP = 2; // Fill the rect and clip around

class ImageFileTransform
        extends DataObject {

    private $id;
    private $dest_id;
    private $orig_id;
    private $transform;
    private $size_x;
    private $size_y;

    public function __construct(array $row) {
        parent::__construct($row);
    }

    public function getId() {
        return $this->id;
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

?>
