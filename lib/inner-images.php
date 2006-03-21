<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/sql.php');

define('IPL_LEFT',1);
define('IPL_HCENTER',2);
define('IPL_RIGHT',3);
define('IPL_HORIZONTAL',3);
define('IPL_TOP',4);
define('IPL_VCENTER',8);
define('IPL_VERTICAL',12);

define('IPL_TOPLEFT',IPL_TOP|IPL_LEFT);
define('IPL_TOPCENTER',IPL_TOP|IPL_HCENTER);
define('IPL_TOPRIGHT',IPL_TOP|IPL_RIGHT);
define('IPL_CENTERLEFT',IPL_VCENTER|IPL_LEFT);
define('IPL_CENTER',IPL_VCENTER|IPL_HCENTER);
define('IPL_CENTERRIGHT',IPL_VCENTER|IPL_RIGHT);

class InnerImage
      extends DataObject
{
var $entry_id;
var $par;
var $x;
var $y;
var $image_id;
var $placement;

function InnerImage($row)
{
$this->placement=IPL_CENTER;
$this->DataObject($row);
}

function getEntryId()
{
return $this->entry_id;
}

function getPar()
{
return $this->par;
}

function getX()
{
return $this->x;
}

function getY()
{
return $this->y;
}

function getImageId()
{
return $this->image_id;
}

function getPlacement()
{
return $this->placement;
}

}

class InnerImagesIterator
      extends SelectIterator
{

function InnerImagesIterator($id)
{
parent::SelectIterator('InnerImage',
                       "select entry_id,par,x,y,image_id,placement
		        from inner_images
			where entry_id=$id
			order by par,y,x");
}

}
?>
