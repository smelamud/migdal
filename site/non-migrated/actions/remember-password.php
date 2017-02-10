<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/users.php');
require_once('lib/mail.php');
require_once('lib/pwgen.php');

function rememberPassword($id)
{
if($id<=0)
  return EPL_NO_LOGIN;
if(!isUserConfirmed($id))
  return EPL_NOT_CONFIRMED;
$password=generatePassword();
setPasswordByUserId($id,$password);
postMailTo($id,'remember_password',array($id,$password));
postMailToAdmins(USR_ADMIN_USERS,'remembering_password',array($id));
return EG_OK;
}

httpRequestString('okdir');
httpRequestString('faildir');

httpRequestString('login');

dbOpen();
session();
$id=getUserIdByLogin($login);
$err=rememberPassword($id);
if($err==EG_OK)
  header('Location: '.remakeURI($okdir,
                                array('err'),
				array('id' => $id)));
else
  header('Location: '.remakeURI($faildir,
                                array(),
				array('err' => $err,
				      'login' => $login)).'#error');
dbClose();
?>
