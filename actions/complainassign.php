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
$result=sql("update complains
	     set recipient_id=$peerId
	     where id=$id",
	    'assignComplain');
journal('update complains
         set recipient_id='.journalVar('users',$peerId).'
	 where id='.journalVar('complains',$id));
return EG_OK;
}

postInteger('id');
postString('login');

dbOpen();
session();
$err=assignComplain($id,$login);
header('Location: '.($err==EG_OK ? remakeURI($okdir,array('err'))
                                 : remakeURI($faildir,
				             array(),
					     array('err' => $err))));
dbClose();
?>
