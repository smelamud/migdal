<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/entries.php');
require_once('lib/selectiterator.php');
require_once('lib/bug.php');
require_once('lib/tmptexts.php');
require_once('lib/text.php');
require_once('lib/image-types.php');
require_once('lib/sql.php');

class Image
      extends Entry
{

function Image($row)
{
$this->entry=ENT_IMAGE;
parent::Entry($row);
}

/*function setup($vars)
{
if(!isset($vars['edittag']) || !$vars['edittag'])
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
}*/

}

class ImagesIterator
      extends SelectIterator
{

function ImagesIterator($postid)
{
parent::SelectIterator('Entry',
		       "select id,ident,entry,up,track,catalog,parent_id,
		               user_id,group_id,perms,disabled,title,title_xml,
			       sent,created,modified,accessed,small_image,
			       small_image_x,small_image_y,large_image,
			       large_image_x,large_image_y,large_image_size,
			       large_image_format,large_image_filename
			from entries
			where up=$postid");
}

}

# remake
function getImageById($id)
{
$result=sql("select id,image_set,filename,small_x,small_y,has_large,title
	     from images
	     where id=$id",
	    'getImageById');
return new Image(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                           : array());
}

# remake
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

# remake
function getImageNameBySet($image_set)
{
$result=sql("select id,image_set,filename,title
	     from images
	     where image_set=$image_set",
	    'getImageNameBySet');
return new Image(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                           : array());
}

function getImageFilename($id,$ext,$fileId=0,$size='large')
{
if($size!='' && $size!='large')
  $sizeC="-$size";
else
  $sizeC='';
if($fileId!=0)
  $fileC="-$fileId";
else
  $fileC='';
return "migdal$sizeC-$id$fileC.$ext";
}

function getImagePath($id,$ext,$fileId=0,$size='large')
{
global $imageDir;

$fname=getImageFilename($id,$ext,$fileId,$size);
return "$imageDir/$fname";
}

function getImageURL($id,$ext,$fileId=0,$size='large')
{
global $imageURL;

$fname=getImageFilename($id,$ext,$fileId,$size);
if($imageURL[0]!='/')
  $imageURL="/$imageURL";
return "$imageURL/$fname";
}

# remake
function imageSetExists($image_set)
{
$result=sql("select id
	     from images
	     where image_set=$image_set",
	    'imageSetExists');
return mysql_num_rows($result)>0;
}

function imageExists($id,$format,$fileId=0,$size='large')
{
return file_exists(getImagePath($id,getMimeExtension($format),$fileId,$size));
}

# remake
function imageLoad($mime,$content)
{
global $tmpDir,$maxImageSize;

if((ImageTypes() & getImageTypeCode($mime))==0)
  return false;
$tmpFile=tempnam($tmpDir,'mig-load-');
$fd=fopen($tmpFile,'w');
fwrite($fd,$content,$maxImageSize);
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

# remake
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

function setMaxImage($max_id)
{
sql("update image_files
     set max_id=$max_id",
    __FUNCTION__);
journal('update image_files
         set max_id='.journalVar('images',$max_id));
}

function getNextImageId()
{
sql('lock tables image_files write',
    __FUNCTION__,'lock');
$result=sql('select max_id
             from image_files',
	    __FUNCTION__,'select');
$id=mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
sql('update image_files
     set max_id=max_id+1',
    __FUNCTION__,'update');
sql('unlock tables',
    __FUNCTION__,'unlock');
// FIXME journal() !
return $id;
}
?>
