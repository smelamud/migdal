<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/postings.php');
require_once('lib/image-upload.php');
require_once('lib/errors.php');

function deleteImage($id)
{
return mysql_query("delete
                    from images
	            where id=$id");
}

function setLargeImageSet($posting,$image_set)
{
return mysql_query("update messages
                    set large_imageset=$image_set
		    where id=".$posting->getMessageId());
}

function setImageId($oldId,$newId)
{
return mysql_query("update images
                    set id=$newId
		    where id=$oldId");
}

function storeImage($posting)
{
global $editid,$loaded,$has_large,$small_x,$small_y;

if(!$posting->isEditable())
  return ELIM_NO_EDIT;
if($loaded)
  {
  $image=getImageContentById($editid);
  if(!deleteImage($editid))
    return ELIM_DELETE_SQL;
  if($image->isEmpty())
    return ELIM_IMAGE_ABSENT;
  $image=uploadMemoryImage($image->getContent(),$image->getFilename(),
                           $image->getFormat(),$has_large,$small_x,$small_y,
			   $err,$posting->getLargeImageSet());
  if(!setImageId($image->getId(),$editid))
    return ELIM_SETID_SQL;
  }
else
  $image=uploadImage('image',$has_large,$small_x,$small_y,$err,
                     $posting->getLargeImageSet());
if(!$image)
  return $err;
$editid=$image->getId();
if($posting->getLargeImageSet()==0)
  if(!setLargeImageSet($posting,$image->getImageSet()))
    return ELIM_SET_SQL;
return ELIM_OK;
}

settype($postid,'integer');
settype($editid,'integer');
settype($has_large,'integer');
settype($small_x,'integer');
settype($small_y,'integer');

dbOpen();
session($sessionid);
$posting=getPostingById($postid);
$err=storeImage($posting);
if($err==ELIM_OK)
  header('Location: /limedit.php?'.makeQuery($HTTP_POST_VARS,
                                             array('err'),
					     array('postid' => $postid,
					           'editid' => $editid)));
else
  header('Location: /limedit.php?'.makeQuery($HTTP_POST_VARS,
                                             array(),
					     array('err' => $err)));
dbClose();
?>
