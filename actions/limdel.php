<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/postings.php');
require_once('lib/images.php');

function deleteImage($posting)
{
global $editid;

if(!$posting->isEditable())
  return ELID_NO_EDIT;
$result=mysql_query("delete
                     from images
	             where id=$editid");
if(!$result)
  return ELID_DELETE_SQL;
return ELID_OK;
}

postInteger('postid');
postInteger('editid');

dbOpen();
session($sessionid);
$posting=getPostingById($postid);
$err=deleteImage($posting);
if($err==ELID_OK)
  header('Location: '.remakeURI($redir,array('err'),array('editid' => 0)));
else
  header('Location: '.remakeURI($redir,array(),array('err' => $err)));
dbClose();
?>
