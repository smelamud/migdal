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

function deleteImage($posting)
{
global $editid,$userModerator;

if(!$posting->isWritable())
  return ELID_NO_EDIT;
sql("delete
     from images
     where id=$editid",
    'deleteImage');
journal('delete
         from images
	 where id='.journalVar('images',$editid));
if(!$userModerator)
  setModbitsByMessageId($posting->getMessageId(),MOD_EDIT);
return ELID_OK;
}

postInteger('postid');
postInteger('editid');

dbOpen();
session();
$posting=getPostingById($postid);
$err=deleteImage($posting);
if($err==ELID_OK)
  header('Location: '.remakeURI($okdir,array('err'),array('editid' => 0)));
else
  header('Location: '.remakeURI($faildir,array(),array('err' => $err)).'#error');
dbClose();
?>
