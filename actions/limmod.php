<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/postings.php');
require_once('lib/image-upload.php');
require_once('lib/errors.php');
require_once('lib/modbits.php');
require_once('lib/sql.php');

function deleteImage($id)
{
$result=sql("delete
	     from images
	     where id=$id",
	    'deleteImage');
journal('delete
         from images
	 where id='.journalVar('images',$id));
return $result;
}

function setLargeImageSet($posting,$image_set)
{
$result=sql("select stotext_id
	     from messages
	     where id=".$posting->getMessageId(),
	    'setLargeImageSet','get_stotext');
$id=mysql_result($result,0,0);
$result=sql("update stotexts
	     set large_imageset=$image_set
	     where id=$id",
	    'setLargeImageSet','set_imageset');
journal('update stotexts
         set large_imageset='.journalVar('images',$image_set).'
	 where id='.journalVar('stotexts',$id));
return $result;
}

function setImageId($oldId,$newId)
{
$result=sql("update images
	     set id=$newId
	     where id=$oldId",
	    'setImageId');
journal('update images
         set id='.journalVar('images',$newId).'
	 where id='.journalVar('images',$oldId));
return $result;
}

function storeImage($posting)
{
global $editid,$loaded,$has_large,$small_x,$small_y,$title,$userModerator;

if(!$posting->isWritable())
  return ELIM_NO_EDIT;
if($loaded)
  {
  $image=getImageContentById($editid);
  deleteImage($editid);
  if($image->isEmpty())
    return ELIM_IMAGE_ABSENT;
  $image=uploadMemoryImage($image->getLarge(),$image->getFilename(),
                           $image->getFormat(),$has_large,$small_x,$small_y,
			   $err,htmlspecialchars($title,ENT_QUOTES),
			   $posting->getLargeImageSet());
  setImageId($image->getId(),$editid);
  }
else
  $image=uploadImage('image',$has_large,$small_x,$small_y,$err,
                     htmlspecialchars($title,ENT_QUOTES),
		     $posting->getLargeImageSet());
if(!$image)
  return $err;
if($posting->getLargeImageSet()==0)
  setLargeImageSet($posting,$image->getImageSet());
if(!$userModerator)
  setModbitsByMessageId($posting->getMessageId(),MOD_EDIT);
return EG_OK;
}

postInteger('postid');
postInteger('editid');
postInteger('has_large');
postInteger('small_x');
postInteger('small_y');
postString('title');

dbOpen();
session();
$posting=getPostingById($postid);
$err=storeImage($posting);
$titleId=tmpTextSave($title);
if($err==EG_OK)
  header('Location: '.remakeMakeURI($okdir,
				    $HTTP_POST_VARS,
				    array('err',
				          'title',
					  'titleid',
					  'edittag',
					  'small_x',
					  'small_y',
					  'has_large',
					  'okdir',
				          'faildir'),
				    array('titleid' => $titleId)));
else
  header('Location: '.remakeMakeURI($faildir,
				    $HTTP_POST_VARS,
				    array('title',
				          'okdir',
					  'faildir'),
				    array('err'     => $err,
					  'titleid' => $titleId)).'#error');
dbClose();
?>
