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

$normal=$this->getNormal();
if($this->id)
  $result=mysql_query(makeUpdate('images',$normal,array('id' => $this->id)));
else
  {
  $result=mysql_query(makeInsert('images',$normal));
  $this->id=mysql_insert_id();
  $this->image_set=$this->id;
  $result=mysql_query('update images
                       set image_set='.$this->id.
		     ' where id='.$this->id)
               or die('Ошибка SQL при установке набора для изображения');
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

function getImageNameBySet($image_set)
{
$result=mysql_query("select id,image_set,filename
                     from images
		     where image_set=$image_set")
	     or die('Ошибка SQL при выборке набора изображений');
return new Image(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                           : array());
}

function getImageTagById($id,$align='')
{
$size=mysql_fetch_row(
      mysql_query("select small_x,small_y
                   from images
 	           where id=$id"));
$al=$align!='' ? "align=$align" : '';
return '<img border=0 width='.$size[0].
                    ' height='.$size[1].
		    " $al src='lib/image.php?id=$id&size=small'>";
}

function imageSetExists($image_set)
{
$result=mysql_query("select id
		     from images
		     where image_set=$image_set")
	     or die('Ошибка SQL при выборке набора изображений');
return mysql_num_rows($result)>0;
}
?>
