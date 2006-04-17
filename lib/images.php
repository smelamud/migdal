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

function setup($vars)
{
if(!isset($vars['edittag']) || !$vars['edittag'])
  return;
$this->up=$vars['postid'];
$this->body_format=TF_PLAIN;
$this->title=$vars['title'];
$this->title_xml=wikiToXML($this->title,$this->body_format,MTEXT_LINE);
}

}

class ImagesIterator
      extends SelectIterator
{

function ImagesIterator($postid)
{
// ���������� ��� ����������� entries, � ������� ���� ��������, �� �����������
// ���� ENT_IMAGE
parent::SelectIterator('Entry',
		       "select id,ident,entry,up,track,catalog,parent_id,
		               user_id,group_id,perms,disabled,title,title_xml,
			       sent,created,modified,accessed,small_image,
			       small_image_x,small_image_y,large_image,
			       large_image_x,large_image_y,large_image_size,
			       large_image_format,large_image_filename,
			       exists(select *
			              from inner_images
				      where image_id=entries.id) as inserted
			from entries
			where up=$postid and small_image<>0");
}

}

function storeImage(&$image)
{
global $userId;

$jencoded=array('title' => '','title_xml' => '','small_image' => 'images',
                'large_image' => 'images','large_image_filename' => '',
		'user_id' => 'users', 'group_id' => 'users','up' => 'entries',
		'parent_id' => 'entries');
$vars=array('ident' => $image->ident,
            'up' => $image->up,
	    'track' => $image->track,
	    'catalog' => $image->catalog,
	    'parent_id' => $image->parent_id,
	    'user_id' => $image->user_id,
	    'group_id' => $image->group_id,
	    'perms' => $image->perms,
	    'title' => $image->title,
	    'title_xml' => $image->title_xml,
	    'small_image' => $image->small_image,
	    'small_image_x' => $image->small_image_x,
	    'small_image_y' => $image->small_image_y,
	    'large_image' => $image->large_image,
	    'large_image_x' => $image->large_image_x,
	    'large_image_y' => $image->large_image_y,
	    'large_image_size' => $image->large_image_size,
	    'large_image_format' => $image->large_image_format,
	    'large_image_filename' => $image->large_image_filename,
	    'modified' => sqlNow());
if($image->id)
  {
  $result=sql(makeUpdate('entries',
                         $vars,
			 array('id' => $image->id)),
	      __FUNCTION__,'update');
  journal(makeUpdate('entries',
                     jencodeVars($vars,$jencoded),
		     array('id' => journalVar('entries',$image->id))));
  }
else
  {
  $vars['entry']=$image->entry;
  $vars['sent']=sqlNow();
  $vars['created']=sqlNow();
  $result=sql(makeInsert('entries',
                         $vars),
	      __FUNCTION__,'insert');
  $image->id=sql_insert_id();
  journal(makeInsert('entries',
                     jencodeVars($vars,$jencoded)),
	  'entries',$this->id);
  }
return $result;
}

function getImageById($id)
{
$result=sql("select id,ident,entry,up,track,catalog,parent_id,user_id,group_id,
                    perms,disabled,title,title_xml,sent,created,modified,
		    accessed,small_image,small_image_x,small_image_y,
		    large_image,large_image_x,large_image_y,large_image_size,
		    large_image_format,large_image_filename
	     from entries
	     where id=$id",
	    __FUNCTION__);
return new Image(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                           : array());
}

function deleteImage($id,$small_image,$large_image,$large_image_format)
{
sql("delete
     from inner_images
     where image_id=$id",
    __FUNCTION__,'inner');
journal('delete
	 from inner_images
	 where image_id='.journalVar('entries',$id),
	__FUNCTION__,'inner');
sql("delete
     from entries
     where id=$id",
    __FUNCTION__,'entry');
journal('delete
         from entries
	 where id='.journalVar('entries',$id));
deleteImageFiles($id,$small_image,$large_image,$large_image_format);
}

function deleteImageFiles($id,$small_image,$large_image,$large_image_format)
{
global $thumbnailType;

// FIXME Journal!
$smallExt=getImageExtension($thumbnailType);
$largeExt=getImageExtension($large_image_format);
if($large_image!=0)
  {
  @unlink(getImagePath($id,$smallExt,$small_image,'small'));
  @unlink(getImagePath($id,$largeExt,$large_image,'large'));
  @unlink(getImagePath($id,$smallExt,0,'small'));
  @unlink(getImagePath($id,$largeExt,0,'large'));
  }
else
  {
  @unlink(getImagePath($id,$largeExt,$small_image,'small'));
  @unlink(getImagePath($id,$largeExt,$small_image,'large'));
  @unlink(getImagePath($id,$largeExt,0,'small'));
  @unlink(getImagePath($id,$largeExt,0,'large'));
  }
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
