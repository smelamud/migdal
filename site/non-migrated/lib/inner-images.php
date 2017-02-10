<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/sql.php');
require_once('lib/selectiterator.php');

const IPL_LEFT = 1;
const IPL_HCENTER = 2;
const IPL_RIGHT = 3;
const IPL_HORIZONTAL = 3;
const IPL_TOP = 4;
const IPL_VCENTER = 8;
const IPL_BOTTOM = 12;
const IPL_VERTICAL = 12;

define('IPL_TOPLEFT', IPL_TOP | IPL_LEFT);
define('IPL_TOPCENTER', IPL_TOP | IPL_HCENTER);
define('IPL_TOPRIGHT', IPL_TOP | IPL_RIGHT);
define('IPL_CENTERLEFT', IPL_VCENTER | IPL_LEFT);
define('IPL_CENTER', IPL_VCENTER | IPL_HCENTER);
define('IPL_CENTERRIGHT', IPL_VCENTER | IPL_RIGHT);
define('IPL_BOTTOMLEFT', IPL_BOTTOM | IPL_LEFT);
define('IPL_BOTTOMCENTER', IPL_BOTTOM | IPL_HCENTER);
define('IPL_BOTTOMRIGHT', IPL_BOTTOM | IPL_RIGHT);

class InnerImage
        extends DataObject {

    protected $entry_id;
    protected $par;
    protected $x;
    protected $y;
    protected $image_id;
    protected $placement;
    protected $image;

    public function __construct(array $row) {
        $this->placement = IPL_CENTER;
        parent::__construct($row);
        $this->image = new Entry($row);
    }

    public function setup(array $vars) {
        if (!isset($vars['edittag']) || !$vars['edittag'])
            return;
        $this->image_id = $vars['editid'];
        $this->placement = $vars['placement'];
    }

    public function getEntryId() {
        return $this->entry_id;
    }

    public function getPar() {
        return $this->par;
    }

    public function getX() {
        return $this->x;
    }

    public function getY() {
        return $this->y;
    }

    public function getImageId() {
        return $this->image_id;
    }

    public function setImageId($image_id) {
        $this->image_id = $image_id;
    }

    public function getPlacement() {
        return $this->placement;
    }

    public function isPlaced($place) {
        $hplace = $place & IPL_HORIZONTAL;
        $h = $hplace == 0 || ($this->placement & IPL_HORIZONTAL) == $hplace;
        $vplace = $place & IPL_VERTICAL;
        $v = $vplace == 0 || ($this->placement & IPL_VERTICAL) == $vplace;
        return $h && $v;
    }

    public function getImage() {
        return $this->image;
    }

}

class InnerImagesIterator
        extends SelectIterator {

    public function __construct($id) {
        parent::__construct('InnerImage',
                "select entry_id,par,x,y,image_id,placement,id,entry,
                    title,title_xml,small_image,small_image_x,
                    small_image_y,small_image_format,large_image,large_image_x,
                    large_image_y,large_image_size,large_image_format
                 from inner_images
                  left join entries
                       on inner_images.image_id=entries.id
                 where entry_id=$id
                 order by par,y,x");
    }

}

function storeInnerImage($inner) {
    sql("delete from inner_images
         where entry_id={$inner->getEntryId()} and par={$inner->getPar()}
               and x={$inner->getX()} and y={$inner->getY()}",
        __FUNCTION__,'delete');
    if ($inner->getImageId() == 0)
        return;
    $vars = array('entry_id' => $inner->getEntryId(),
                  'par' => $inner->getPar(),
                  'x' => $inner->getX(),
                  'y' => $inner->getY(),
                  'image_id' => $inner->getImageId(),
                  'placement' => $inner->getPlacement());
    sql(sqlInsert('inner_images', $vars),
        __FUNCTION__, 'insert');
}

function getInnerImageByParagraph($entry_id, $par, $x = 0, $y = 0) {
    $result = sql("select entry_id,par,x,y,image_id,placement
                   from inner_images
                   where entry_id=$entry_id and par=$par and x=$x and y=$y");
    return new InnerImage(mysql_num_rows($result) > 0
                          ? mysql_fetch_assoc($result)
                          : array('entry_id' => $entry_id,
                                  'par' => $par,
                                  'x' => $x,
                                  'y' => $y,
                                  'placement' => IPL_BOTTOMLEFT));
}

function deleteObsoleteInnerImages() {
        global $innerImageTimeout;

        sql("delete
             from entries
             where entry=".ENT_IMAGE." and not exists (
                 select *
                 from inner_images
                 where image_id = entries.id
             ) and created + interval $innerImageTimeout day < now()",
             __FUNCTION__);
}
?>
