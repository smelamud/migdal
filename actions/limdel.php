<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/postings.php');
require_once('lib/images.php');
require_once('lib/modbits.php');

function deleteImage($posting)
{
global $editid;

if(!$posting->isWritable())
  return ELID_NO_EDIT;
$result=mysql_query("delete
                     from images
	             where id=$editid");
if(!$result)
  return ELID_DELETE_SQL;
journal('delete
         from images
	 where id='.journalVar('images',$editid));
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
