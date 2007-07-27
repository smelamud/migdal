<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/uri.php');
require_once('lib/errors.php');
require_once('lib/tmptexts.php');
require_once('lib/complains.php');
require_once('lib/captcha.php');

function modifyComplain($complain,$original)
{
global $captcha,$userId;

if($original->getId()==0)
  {
  $root=getRootComplain();
  if(!$root->isAppendable())
    return EC_NO_SEND;
  }
else
  if(!$original->isWritable())
    return EC_NO_EDIT;
if($complain->body=='')
  return EC_BODY_ABSENT;
if($complain->subject=='')
  return EC_SUBJECT_ABSENT;
if($complain->id<=0 && $userId<=0)
  {
  if($captcha=='')
    return EC_CAPTCHA_ABSENT;
  if(!validateCaptcha($captcha))
    return EC_CAPTCHA;
  }
storeComplain($complain);
return EG_OK;
}

postString('okdir');
postString('faildir');

postInteger('edittag');
postInteger('editid');
postString('captcha');
postString('url');
postString('subject');
postString('body');

dbOpen();
session();
$complain=getComplainById($editid,$url);
$original=$complain;
$complain->setup($Args);
$err=modifyComplain($complain,$original);
if($err==EG_OK)
  header("Location: $okdir");
else
  {
  $urlId=tmpTextSave($url);
  $subjectId=tmpTextSave($subject);
  $bodyId=tmpTextSave($body);
  header('Location: '.
          remakeMakeURI($faildir,
			$Args,
			array('okdir',
			      'faildir',
			      'subject',
			      'body',
			      'url'),
			array('subject_i'    => $subjectId,
			      'body_i'       => $bodyId,
			      'url_i'        => $urlId,
			      'err'          => $err)).'#error');
  }
dbClose();
?>
