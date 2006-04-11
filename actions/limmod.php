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
$perms=getPermsById($image->up);
if(!$perms->isAppendable())
  return ELIM_POSTING_APPEND;
if(!$image->hasSmallImage())
  return ELIM_IMAGE_ABSENT;
$image->track='';
storeImage($image);
commitImages($image,$original);
updateTracks('entries',$image->id);
updateCatalogs($image->id);
$posting=getPostingById($image->up);
setPremoderates($posting,$posting);
return EG_OK;
}

postString('okdir');
postString('faildir');

postInteger('edittag');
postInteger('postid');
postInteger('editid');
postInteger('del_image');
postInteger('has_large_image');
postInteger('small_image_x');
postInteger('small_image_y');
postString('title');

dbOpen();
session();
$image=getImageById($editid);
$original=$image;
$image->setup($Args);
$err=uploadImage('image_file',$image,$has_large_image,
                 $small_image_x,$small_image_y,$del_image,true);
if($err==EG_OK)
  $err=modifyImage($image,$original);
$titleId=tmpTextSave($title);
if($err==EG_OK)
  header('Location: '.remakeMakeURI($okdir,
				    $Args,
				    array('err',
				          'title',
					  'title_i',
					  'edittag',
					  'small_image_x',
					  'small_image_y',
					  'has_large_image',
					  'del_image',
					  'okdir',
				          'faildir')));
else
  header('Location: '.remakeMakeURI($faildir,
				    $Args,
				    array('title',
				          'okdir',
					  'faildir'),
				    array('err'     => $err,
					  'title_i' => $titleId)).'#error');
dbClose();
?>
