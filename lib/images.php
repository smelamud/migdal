<?php
# @(#) $Id$

require_once('lib/dataobject.php');

class Image
      extends DataObject
{
var $id;
var $image_set;
var $filename;
var $small;
var $large;
var $format;

function Image($row)
{
$this->DataObject($row);
}

function getId()
{
return $this->id;
}

function getFilename()
{
return $this->filename;
}

}

function getImageNameBySet($image_set)
{
$result=mysql_query("select id,image_set,filename
                     from images
		     where image_set=$image_set");
return new Image(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                           : array());
}

function imageSetExists($image_set)
{
$result=mysql_query("select id
		     from images
		     where image_set=$image_set");
return mysql_num_rows($result)>0;
}
?>
