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
var $small_x;
var $small_y;
var $large;
var $large_x;
var $large_y;
var $format;

function Image($row)
{
$this->DataObject($row);
}

function getWorldVars()
{
return array('filename','small','small_x','small_y','large','large_x','large_y',
             'format');
}

function store()
{
global $userId;

$normal=$this->getWorldVarValues();
if($this->id)
  $result=mysql_query(makeUpdate('images',$normal,array('id' => $this->id)));
else
  {
  $result=mysql_query(makeInsert('images',$normal));
  $this->id=mysql_insert_id();
  $this->image_set=$this->id;
  $result=mysql_query('update images
                       set image_set='.$this->id.
		     ' where id='.$this->id);
  }
return $result;
}

function getId()
{
return $this->id;
}

function getImageSet()
{
return $this->image_set;
}

function getFilename()
{
return $this->filename;
}

}

function getImageExtension($mime_type)
{
switch($mime_type)
  {
  case 'image/pjpeg':
  case 'image/jpeg':
       return 'jpg';
  case 'image/gif':
       return 'gif';
  case 'image/x-png':
  case 'image/png':
       return 'png';
  default:
       return '';
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
