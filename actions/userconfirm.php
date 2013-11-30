<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/mail.php');
require_once('lib/sql.php');
require_once('lib/logging.php');

function doConfirmUser($id,$code)
{
global $userAdminUsers,$userMyComputerHint;

if($userAdminUsers && $id!=0)
  {
  if(!userExists($id))
    $id=0;
  }
else
  $id=getUserIdByConfirmCode($code);
if($id<=0)
  return EUC_NO_USER;
loginHints(getUserLoginById($id),$userMyComputerHint);
if(!isUserConfirmed($id))
  {
  confirmUser($id);
  postMailToAdmins(USR_ADMIN_USERS,'confirmed',array($id));
  }
else
  return EUC_ALREADY_CONFIRMED;
return EG_OK;
}

httpRequestInteger('id');
httpRequestString('code');

dbOpen();
session();
$err=doConfirmUser($id,$code);
if($err==EG_OK)
  header('Location: /register/signin/');
elseif($err==EUC_ALREADY_CONFIRMED)
  header('Location: /register/already-confirmed/');
else
  header("Location: /register/error/?err=$err");
dbClose();
?>
