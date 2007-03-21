<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/mail.php');
require_once('lib/sql.php');

function confirmUser($id,$code,&$userLogin)
{
global $userAdminUsers;

if($userAdminUsers && $id!=0)
  $result=sql("select login,id
	       from users
	       where id=$id and hidden<2",
	      'confirmUser','admin_select');
else
  $result=sql('select login,id
	       from users
	       where confirm_code=\''.addslashes($code).'\'
		     and hidden<2',
	      'confirmUser','regular_select');
if(mysql_num_rows($result)<=0)
  return EUC_NO_USER;
$userLogin=mysql_result($result,0,0);
$id=mysql_result($result,0,1);
sql("update users
     set no_login=0,hidden=0,confirm_deadline=null,
	 last_online=now()
     where login='$userLogin'",
    'confirmUser','confirm');
journal("update users
         set no_login=0,hidden=0,confirm_deadline=null,
	     last_online=now()
	 where login='".jencode($userLogin)."'");
sendMailAdmin(MAIL_CONFIRMED,'admin_users',$id);
return EG_OK;
}

postInteger('id');

dbOpen();
session();
$err=confirmUser($id,$code,$userLogin);
if($err!=EG_OK)
  header("Location: /userfin.php?err=$err");
else
  header('Location: /userfin.php?login='.urlencode($userLogin));
dbClose();
?>
