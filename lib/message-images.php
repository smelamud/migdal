<?php
# @(#) $Id$

require_once('lib/dataobject.php');

define('IPL_LEFT',0);
define('IPL_HCENTER',1);
define('IPL_RIGHT',2);
define('IPL_TOP',0);
define('IPL_VCENTER',4);
define('IPL_BOTTOM',8);

define('IPL_TOPLEFT',IPL_TOP|IPL_LEFT);
define('IPL_TOPCENTER',IPL_TOP|IPL_HCENTER);
define('IPL_TOPRIGHT',IPL_TOP|IPL_RIGHT);
define('IPL_CENTERLEFT',IPL_VCENTER|IPL_LEFT);
define('IPL_CENTER',IPL_VCENTER|IPL_HCENTER);
define('IPL_CENTERRIGHT',IPL_VCENTER|IPL_RIGHT);
define('IPL_BOTTOMLEFT',IPL_BOTTOM|IPL_LEFT);
define('IPL_BOTTOMCENTER',IPL_BOTTOM|IPL_HCENTER);
define('IPL_BOTTOMRIGHT',IPL_BOTTOM|IPL_RIGHT);

class MessageImage
      extends DataObject
{
var $message_id;
var $par;
var $image_id;
var $placement;

function MessageImage($row)
{
$this->placement=IPL_CENTER;
$this->DataObject($row);
}

function getMessageId()
{
return $this->message_id;
}

function getPar()
{
return $this->par;
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

function getMessageImageByParagraph($msgid,$par)
{
$result=mysql_query("select message_id,par,image_id,placement
                     from message_images
                     where message_id=$msgid and par=$par");
return new MessageImage(mysql_num_rows($result)>0
                        ? mysql_fetch_assoc($result)
                        : array('message_id' => $msgid,
			        'par'        => $par));
}
?>
