<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/selectiterator.php');
require_once('lib/tmptexts.php');
require_once('lib/text.php');
require_once('lib/image-types.php');

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
  if($this->image_set==0)
    {
    $this->image_set=$this->id;
    $result=mysql_query('update images
			 set image_set='.$this->id.
		       ' where id='.$this->id)
		 or die('������ SQL ��� ��������� ������ ��� �����������');
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

function getSmallX()
{
return $this->small_x;
}

function getSmallY()
{
return $this->small_y;
}

function getContent()
{
return $this->has_large ? $this->large : $this->small;
}

function isEmpty()
{
return ($this->has_large ? $this->large : $this->small)=='';
}

function hasLarge()
{
return $this->has_large;
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

function setTitle($title)
{
$this->title=$title;
}

}

class ImageSetIterator
      extends SelectIterator
{

function ImageSetIterator($image_set)
{
$this->SelectIterator('Image',
                      "select id,filename,small_x,small_y
		       from images
		       where image_set=$image_set");
}

}

function getImageById($id)
{
$result=mysql_query("select id,image_set,filename,small_x,small_y,has_large,
                            title
                     from images
		     where id=$id")
	     or die('������ SQL ��� ������� �����������');
return new Image(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                           : array());
}

function getImageContentById($id)
{
$result=mysql_query("select id,filename,if(has_large,'',small) as small,
                            has_large,large,format
                     from images
		     where id=$id")
	     or die('������ SQL ��� ������� �����������');
return new Image(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                           : array());
}

function getImageNameBySet($image_set)
{
$result=mysql_query("select id,image_set,filename,title
                     from images
		     where image_set=$image_set")
	     or die('������ SQL ��� ������� ������ �����������');
return new Image(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                           : array());
}

function getImageTagById($id,$align='',$aSize='small')
{
global $uiName,$thumbnailType;

$size=mysql_fetch_row(
      mysql_query("select ${aSize}_x,${aSize}_y,has_large,format
                   from images
 	           where id=$id"));
$al=$align!='' ? "align=$align" : '';
$ext=getImageExtension($size[2] ? $thumbnailType : $size[3]);
return '<img border=0 width='.$size[0].
                    ' height='.$size[1].
		    " $al src='lib/image.php/$uiName-$id.$ext?id=$id&size=$aSize'>";
}

function getImageEnlargeLinkById($id)
{
global $uiName;

$result=mysql_query("select format
                     from images
 	             where id=$id");
$ext=getImageExtension(mysql_result($result,0,0));
return "<a href='lib/image.php/$uiName-$id.$ext?id=$id&size=large' target=_blank".
       " title='���������'>";
}

function imageSetExists($image_set)
{
$result=mysql_query("select id
		     from images
		     where image_set=$image_set")
	     or die('������ SQL ��� ������� ������ �����������');
return mysql_num_rows($result)>0;
}

function imageExists($id)
{
$result=mysql_query("select id
		     from images
		     where id=$id")
	     or die('������ SQL ��� ������� �����������');
return mysql_num_rows($result)>0;
}
?>
