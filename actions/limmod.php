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

function deleteImage($id)
{
$result=mysql_query("delete
                     from images
	             where id=$id");
journal('delete
         from images
	 where id='.journalVar('images',$id));
return $result;
}

function setLargeImageSet($posting,$image_set)
{
$result=mysql_query("select stotext_id
                     from messages
		     where id=".$posting->getMessageId());
if(!$result)
  return $result;
$id=mysql_result($result,0,0);
$result=mysql_query("update stotexts
                     set large_imageset=$image_set
		     where id=$id");
journal('update stotexts
         set large_imageset='.journalVar('images',$image_set).'
	 where id='.journalVar('stotexts',$id));
return $result;
}

function setImageId($oldId,$newId)
{
$result=mysql_query("update images
                     set id=$newId
		     where id=$oldId");
journal('update images
         set id='.journalVar('images',$newId).'
	 where id='.journalVar('images',$oldId));
return $result;
}

function storeImage($posting)
{
global $editid,$loaded,$has_large,$small_x,$small_y,$title;

if(!$posting->isWritable())
  return ELIM_NO_EDIT;
if($loaded)
  {
  $image=getImageContentById($editid);
  if(!deleteImage($editid))
    return ELIM_DELETE_SQL;
  if($image->isEmpty())
    return ELIM_IMAGE_ABSENT;
  $image=uploadMemoryImage($image->getLarge(),$image->getFilename(),
                           $image->getFormat(),$has_large,$small_x,$small_y,
			   $err,htmlspecialchars($title,ENT_QUOTES),
			   $posting->getLargeImageSet());
  if(!setImageId($image->getId(),$editid))
    return ELIM_SETID_SQL;
  }
else
  $image=uploadImage('image',$has_large,$small_x,$small_y,$err,
                     htmlspecialchars($title,ENT_QUOTES),
		     $posting->getLargeImageSet());
if(!$image)
  return $err;
if($posting->getLargeImageSet()==0)
  if(!setLargeImageSet($posting,$image->getImageSet()))
    return ELIM_SET_SQL;
setModbitsByMessageId($posting->getMessageId(),MOD_EDIT);
return ELIM_OK;
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
if($err==ELIM_OK)
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
