<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/users.php');
require_once('lib/complains.php');

function assignComplain($id,$login)
{
global $userJudge;

if(!$userJudge)
  return EC_NO_ASSIGN;
if(!complainExists($id))
  return EC_NO_COMPLAIN;
$peerId=getUserIdByLogin(addslashes($login));
if($peerId==0 && $login!='')
  return EC_LOGIN_ASSIGN;
$result=mysql_query("update complains
                     set recipient_id=$peerId
		     where id=$id");
if(!$result)
  return EC_SQL_ASSIGN;
return EC_OK;
}

postInteger('id');

dbOpen();
session($sessionid);
$err=assignComplain($id,$login);
header('Location: '.($err==EC_OK ? remakeURI($redir,array('err'))
                                 : remakeURI($redir,
				             array(),
					     array('err' => $err))));
dbClose();
?>
