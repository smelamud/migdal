<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/dataobject.php');
require_once('lib/selectiterator.php');
require_once('lib/bug.php');
require_once('lib/tmptexts.php');
require_once('lib/text.php');
require_once('lib/image-types.php');
require_once('lib/sql.php');

class Image
      extends DataObject
{
var $id;
var $image_set;
var $filename;
var $small;
var $small_x;
var $small_y;
var $has_large;
var $large;
var $large_x;
var $large_y;
var $format;
var $title;
var $image_size;
var $image_x;
var $image_y;

function Image($row)
{
$this->DataObject($row);
}

function setup($vars)
{
if(!isset($vars['edittag']))
  return;
foreach($this->getCorrespondentVars() as $var)
       $this->$var=htmlspecialchars($vars[$var],ENT_QUOTES);

if(isset($vars['titleid']))
  $this->title=tmpTextRestore($vars['titleid']);
}

function getCorrespondentVars()
{
return array('has_large','small_x','small_y','title');
}

function getWorldVars()
{
return array('image_set','filename','small','small_x','small_y','has_large',
             'large','large_x','large_y','format','title');
}

function getJencodedVars()
{
return array('image_set' => 'images','filename' => '','small' => '',
             'large' => '','title' => '');
}

function store()
{
global $userId;

$normal=$this->getNormal();
if($this->id)
  {
  $result=sql(makeUpdate('images',
                         $normal,
			 array('id' => $this->id)),
	      get_method($this,'store'),'update');
  journal(makeUpdate('images',
                     jencodeVars($normal,$this->getJencodedVars()),
		     array('id' => journalVar('images',$this->id))));
  }
else
  {
  $result=sql(makeInsert('images',
                         $normal),
	      get_method($this,'store'),'insert');
  $this->id=sql_insert_id();
  journal(makeInsert('images',
                     jencodeVars($normal,$this->getJencodedVars())),
	  'images',$this->id);
  if($this->image_set==0)
    {
    $this->image_set=$this->id;
    $result=setSelfImageSet($this->id);
    }
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

function setImageSet($image_set)
{
$this->image_set=$image_set;
}

function getFilename()
{
return $this->filename;
}

function getSmall()
{
return $this->small;
}

function getSmallX()
{
return $this->small_x;
}

function getSmallY()
{
return $this->small_y;
}

function isEmpty()
{
return $this->getLarge()=='';
}

function hasLarge()
{
return $this->has_large;
}

function getLarge()
{
return $this->has_large ? $this->large : $this->small;
}

function getLargeX()
{
return $this->large_x;
}

function getLargeY()
{
return $this->large_y;
}

function getFormat()
{
return $this->format;
}

function getTitle()
{
return $this->title;
}

function getHTMLTitle()
{
return stotextToHTML(TF_MAIL,$this->title);
}

function isTitlePea()
{
global $peaSize,$peaSizeMinus,$peaSizePlus;

return strlen($this->getTitle())<=$peaSize+$peaSizePlus;
}

function getTitlePea()
{
global $peaSize,$peaSizeMinus,$peaSizePlus;

return shorten($this->getTitle(),$peaSize,$peaSizeMinus,$peaSizePlus);
}

function getHTMLTitlePea()
{
return stotextToHTML(TF_MAIL,$this->getTitlePea());
}

function getCleanTitlePea()
{
global $peaSize,$peaSizeMinus,$peaSizePlus;

return shorten(clearStotext(TF_MAIL,$this->getTitle()),
               $peaSize,$peaSizeMinus,$peaSizePlus);
}

function setTitle($title)
{
$this->title=$title;
}

function getImageSize()
{
return $this->image_size;
}

function getImageSizeKB()
{
return (int)($this->image_size/1024);
}

function getImageX()
{
return $this->image_x;
}

function getImageY()
{
return $this->image_y;
}

}

class ImageSetIterator
      extends SelectIterator
{

function ImageSetIterator($image_set)
{
$this->SelectIterator('Image',
                      "select id,filename,small_x,small_y,
	               has_large,title,
		       if(has_large,length(large),length(small)) as image_size,
		       if(has_large,large_x,small_x) as image_x,
		       if(has_large,large_y,small_y) as image_y
		       from images
		       where image_set=$image_set");
}

}

function getImageById($id)
{
$result=sql("select id,image_set,filename,small_x,small_y,has_large,title
	     from images
	     where id=$id",
	    'getImageById');
return new Image(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                           : array());
}

function getImageContentById($id,$size='large')
{
$fields=$size=='small' ? ',small' : ",if(has_large,'',small) as small,large";
$result=sql("select id,filename,has_large,format,image_set$fields
	     from images
	     where id=$id",
	    'getImageContentById');
return new Image(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                           : array());
}

function getImageNameBySet($image_set)
{
$result=sql("select id,image_set,filename,title
	     from images
	     where image_set=$image_set",
	    'getImageNameBySet');
return new Image(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                           : array());
}

function getImageTagById($id,$align='',$aSize='small',$src='lib/image.php',
                         $static=false,$title='')
{
global $uiName,$thumbnailType;

$size=mysql_fetch_row(
      sql("select ${aSize}_x,${aSize}_y,has_large,format
	   from images
	   where id=$id",
	  'getImageTagById'));
$al=$align!='' ? "align=$align" : '';
$ext=getImageExtension($size[2] ? $thumbnailType : $size[3]);
$href=!$static ? "$src/$uiName-$id.$ext?id=$id&size=$aSize"
               : "pics/$uiName-$id".($aSize=='small' ? '-small' : '').".$ext";
$alt=$title!='' ? "alt='$title' title='$title'" : '';
return '<img border=0 width='.$size[0].
                    ' height='.$size[1].
		    " $al $alt src='$href'>";
}

function getImageEnlargeLinkById($id,$static=false)
{
global $uiName;

$result=sql("select format
	     from images
	     where id=$id",
	    'getImageEnlargeLinkById');
$ext=getImageExtension(mysql_result($result,0,0));
$href=!$static ? "lib/image.php/$uiName-$id.$ext?id=$id&size=large"
               : "pics/$uiName-$id.$ext";
return "<a href='$href' target=_blank title='Увеличить'>";
}

function imageSetExists($image_set)
{
$result=sql("select id
	     from images
	     where image_set=$image_set",
	    'imageSetExists');
return mysql_num_rows($result)>0;
}

function imageExists($id)
{
$result=sql("select id
	     from images
	     where id=$id",
	    'imageExists');
return mysql_num_rows($result)>0;
}

function imageLoad($mime,$content)
{
global $tmpDir,$maxImage;

if((ImageTypes() & getImageTypeCode($mime))==0)
  return false;
$tmpFile=tempnam($tmpDir,'mig-load-');
$fd=fopen($tmpFile,'w');
fwrite($fd,$content,$maxImage);
fclose($fd);
$ext=getImageTypeName($mime);
if($ext=='')
  {
  unlink($tmpFile);
  return false;
  }
$imageFrom="ImageCreateFrom$ext";
$handle=$imageFrom($tmpFile);
unlink($tmpFile);
return $handle;
}

function setSelfImageSet($id)
{
$result=sql("update images
	     set image_set=$id
	     where id=$id",
	    'setSelfImageSet');
journal('update images
	 set image_set='.journalVar('images',$id).
       ' where id='.journalVar('images',$id));
return $result;
}
?>
