<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/postings.php');
require_once('lib/images.php');
require_once('lib/image-upload.php');
require_once('lib/errors.php');
require_once('lib/modbits.php');
require_once('lib/sql.php');

function modifyImage($image,$original)
{
if($original->getId()!=0 && !$original->isWritable())
  return ELIM_NO_EDIT;
if($image->up==0)
  return ELIM_NO_POSTING;
$correct=validateHierarchy($image->parent_id,$image->up,ENT_IMAGE,$image->id);
if($correct!=EG_OK)
  return $correct;
$posting=getPostingById($image->up);
if(!$posting->isAppendable())
  return ELIM_POSTING_APPEND;
if(!$image->hasSmallImage())
  return ELIM_IMAGE_ABSENT;
$image->track='';
storeImage($image);
commitImages($image,$original);
updateTracks('entries',$image->id);
updateCatalogs($image->id);
setPremoderates($posting,$posting);
return EG_OK;
}

function insertImage($inner,$image,$append)
{
echo "({$inner->image_id})({$image->id})";
if($inner->image_id==0 || !$append && $image->id!=0)
  $inner->image_id=$image->id;
if($inner->entry_id==0)
  return ELIM_NO_POSTING;
$perms=getPermsById($inner->entry_id);
if(!$perms->isWritable())
  return ELIM_POSTING_WRITE;
storeInnerImage($inner);
return EG_OK;
}

postString('okdir');
postString('faildir');

postInteger('edittag');
postInteger('postid');
postInteger('editid');
postInteger('par');
postInteger('x');
postInteger('y');
postInteger('placement');
postInteger('insert');
postInteger('append');
postInteger('small_image');
postInteger('small_image_x');
postInteger('small_image_y');
postInteger('has_large_image');
postInteger('large_image');
postInteger('large_image_x');
postInteger('large_image_y');
postInteger('large_image_size');
postString('large_image_format');
postString('large_image_filename');
postInteger('del_image');
postString('title');

dbOpen();
session();
$image=getImageById($append ? 0 : $editid);
$original=$image;
$image->setup($Args);
$err=uploadImage('image_file',$image,$has_large_image,
                 $small_image_x,$small_image_y,$del_image,true);
if($err==EG_OK)
  $err=modifyImage($image,$original);
if($insert)
  {
  if($err==ELIM_IMAGE_ABSENT)
    $err=EG_OK;
  if($err==EG_OK)
    {
    $inner=getInnerImageByParagraph($postid,$par,$x,$y);
    $inner->setup($Args);
    $err=insertImage($inner,$image,$append);
    }
  }
if($err==EG_OK)
  {
  if(!$insert)
    $okdir=remakeMakeURI($okdir,
			 $Args,
			 array('err',
			       'title',
			       'title_i',
			       'edittag',
			       'small_image',
			       'small_image_x',
			       'small_image_y',
			       'large_image',
			       'large_image_x',
			       'large_image_y',
			       'large_image_size',
			       'large_image_format',
			       'large_image_format_i',
			       'large_image_filename',
			       'large_image_filename_i',
			       'has_large_image',
			       'del_image',
			       'okdir',
			       'faildir'));
  header("Location: $okdir");
  }
else
  {
  $largeImageFormatId=tmpTextSave($image->large_image_format);
  $largeImageFilenameId=tmpTextSave($image->large_image_filename);
  $titleId=tmpTextSave($title);
  header('Location: '.remakeMakeURI($faildir,
				    $Args,
				    array('title',
				          'insert',
					  'append',
				          'okdir',
					  'faildir'),
				    array('err'     => $err,
					  'small_image'   => $image->small_image,
					  'small_image_x' => $image->small_image_x,
					  'small_image_y' => $image->small_image_y,
					  'large_image'   => $image->large_image,
					  'large_image_x' => $image->large_image_x,
					  'large_image_y' => $image->large_image_y,
					  'large_image_size' => $image->large_image_size,
					  'large_image_format_i' => $largeImageFormatId,
					  'large_image_filename_i' => $largeImageFilenameId,
					  'title_i' => $titleId)).'#error');
  }
dbClose();
?>
