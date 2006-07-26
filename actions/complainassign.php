<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/users.php');
require_once('lib/complains.php');
require_once('lib/sql.php');

function assignComplainAction($id,$login)
{
global $userJudge;

if(!$userJudge)
  return EC_NO_ASSIGN;
if(!complainExists($id))
  return EC_NO_COMPLAIN;
$peerId=getUserIdByLogin($login);
if($peerId==0 && $login!='')
  return EC_LOGIN_ASSIGN;
assignComplain($id,$peerId);
return EG_OK;
}

postString('okdir');
postString('faildir');

postInteger('id');
postString('login');

dbOpen();
session();
$err=assignComplainAction($id,$login);
header('Location: '.($err==EG_OK ? remakeURI($okdir,
                                             array('err'))
                                 : remakeURI($faildir,
				             array(),
					     array('err' => $err)).'#error'));
dbClose();
?>
