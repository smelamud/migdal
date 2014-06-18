<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/postings.php');
require_once('lib/images.php');
require_once('lib/modbits.php');
require_once('lib/sql.php');
require_once('lib/errors.php');

function deleteImageAction($image)
{
if($image->getId()==0 || !$image->isWritable())
  return ELID_NO_EDIT;
$posting=getPostingById($image->up);
if(!$posting->isAppendable())
  return ELID_POSTING_APPEND;
deleteImage($image->getId());
setPremoderates($posting,$posting);
return EG_OK;
}

httpRequestString('okdir');
httpRequestString('faildir');

httpRequestInteger('editid');

dbOpen();
session();
$image=getImageById($editid);
$err=deleteImageAction($image);
if($err==EG_OK)
  header('Location: '.remakeURI($okdir,
                                array('err'),
				array('editid' => 0)));
else
  header('Location: '.remakeURI($faildir,
                                array(),
				array('err' => $err)).'#error');
dbClose();
?>
