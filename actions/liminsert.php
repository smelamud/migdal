<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/postings.php');
require_once('lib/images.php');
require_once('lib/stotext-images.php');
require_once('lib/errors.php');

function modifyMessageImage($posting,$image)
{
if(!$posting->isEditable())
  return ELII_NO_EDIT;
if($posting->getMessageId()==0)
  return ELII_MESSAGE_ABSENT;
if($image->getImageId()!=0 && !imageExists($image->getImageId()))
  return ELII_IMAGE_ABSENT;
if(!$image->store())
  return ELII_STORE_SQL;
return ELII_OK;
}

postInteger('postid');
postInteger('par');

dbOpen();
session($sessionid);
$posting=getPostingById($postid);
$image=getStotextImageByParagraph($posting->getStotextId(),$par);
$image->setup($HTTP_POST_VARS);
$err=modifyMessageImage($posting,$image);
if($err==ELII_OK)
  header('Location: '.remakeURI($redir,array('err')));
else
  header('Location: /liminsert.php?'.makeQuery($HTTP_POST_VARS,
                                               array(),
					       array('err' => $err)));
dbClose();
?>
