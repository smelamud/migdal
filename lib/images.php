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

function uploadImageUsingMogrify($image,$image_name,$image_size,$image_type,
                                 $thumbnail,&$err)
{
global $maxImage,$thumbnailType,$thumbnailWidth,$thumbnailHeight;
global $userId;

if(!isset($image) || $image=='' || !is_uploaded_file($image)
   || filesize($image)!=$image_size)
  {
  $err=EIU_OK;
  return false;
  }
if($image_size>$maxImage)
  {
  $err=EIU_IMAGE_LARGE;
  return false;
  }
$largeExt=getImageExtension($image_type);
$smallExt=getImageExtension($thumbnailType);
if($largeExt=='')
  {
  $err=EIU_UNKNOWN_IMAGE;
  return false;
  }

srand(time());
$hash=rand();
$largeFile="/tmp/mig-$hash.$largeExt";
$smallFile="/tmp/mig-$hash.$smallExt";

if(!move_uploaded_file($image,$largeFile))
  {
  $err=EIU_OK;
  return false;
  }
$large_size=getImageSize($largeFile);
$fd=fopen($largeFile,'r');
$large=fread($fd,$maxImage);
fclose($fd);

$geometry=$thumbnailWidth.'x'.$thumbnailHeight;
exec("mogrify -format $smallExt -geometry '$geometry>' $largeFile");

$small_size=getImageSize($smallFile);
$fd=fopen($smallFile,'r');
$small=fread($fd,$maxImage);
fclose($fd);

return new Image(array('filename' => $image_name,
		       'small'    => $small,
		       'small_x'  => $small_size[0],
		       'small_y'  => $small_size[1],
		       'large'    => $large,
		       'large_x'  => $large_size[0],
		       'large_y'  => $large_size[1],
		       'format'   => $image_type));
}

function uploadImage($name,$thumbnail,&$err)
{
global $HTTP_POST_FILES;

$img=uploadImageUsingMogrify($HTTP_POST_FILES[$name]['tmp_name'],
			     $HTTP_POST_FILES[$name]['name'],
			     $HTTP_POST_FILES[$name]['size'],
			     $HTTP_POST_FILES[$name]['type'],
			     $thumbnail,$err);
if(!$img)
  return $img;
if(!$img->store())
  {
  $err=EIU_IMAGE_SQL;
  return false;
  }
$err=EIU_OK;
return $img;
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

function getImageTagById($id)
{
$size=mysql_fetch_row(
      mysql_query("select small_x,small_y
                   from images
 	           where id=$id"));
return '<img border=0 width='.$size[0].
                    ' height='.$size[1]." src='lib/image.php?id=$id&size=small'>";
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
