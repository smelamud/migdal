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
$result=mysql_query("select stotext_id
                     from messages
		     where id=".$posting->getMessageId());
if(!$result)
  return $result;
return mysql_query("update stotexts
                    set large_imageset=$image_set
		    where id=".mysql_result($result,0,0));
}

function setImageId($oldId,$newId)
{
return mysql_query("update images
                    set id=$newId
		    where id=$oldId");
}

function storeImage($posting)
{
global $editid,$loaded,$has_large,$small_x,$small_y,$title;

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
			   $err,$title,$posting->getLargeImageSet());
  if(!setImageId($image->getId(),$editid))
    return ELIM_SETID_SQL;
  }
else
  $image=uploadImage('image',$has_large,$small_x,$small_y,$err,
                     $title,$posting->getLargeImageSet());
if(!$image)
  return $err;
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
$title=addslashes($title);

dbOpen();
session($sessionid);
$posting=getPostingById($postid);
$err=storeImage($posting);
$titleId=tmpTextSave($title);
if($err==ELIM_OK)
  header('Location: /limedit.php?'.makeQuery($HTTP_POST_VARS,
                                             array('err','title','edittag'),
					     array('titleid' => $titleId)));
else
  header('Location: /limedit.php?'.makeQuery($HTTP_POST_VARS,
                                             array('title'),
					     array('err'     => $err,
					           'titleid' => $titleId)));
dbClose();
?>
