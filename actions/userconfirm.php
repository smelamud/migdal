<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/mailings.php');

function confirmUser($id,$code,&$userLogin)
{
global $userAdminUsers;

if($userAdminUsers && $id!=0)
  $result=mysql_query("select login,id
		       from users
		       where id=$id and hidden<2");
else
  $result=mysql_query('select login,id
		       from users
		       where confirm_code=\''.addslashes($code).'\'
			     and hidden<2');
if(!$result)
  return EUC_SQL_SELECT;
if(mysql_num_rows($result)<=0)
  return EUC_NO_USER;
$userLogin=mysql_result($result,0,0);
$id=mysql_result($result,0,1);
$result=mysql_query("update users
                     set no_login=0,hidden=0,confirm_deadline=null,
		         last_online=now()
		     where login='$userLogin'");
if(!$result)
  return EUC_SQL_CONFIRM;
journal("update users
         set no_login=0,hidden=0,confirm_deadline=null,
	     last_online=now()
	 where login='".jencode($userLogin)."'");
sendMailAdmin(MAIL_CONFIRMED,'admin_users',$id);
return EUC_OK;
}

postInteger('id');

dbOpen();
session();
$err=confirmUser($id,$code,$userLogin);
if($err!=EUC_OK)
  header("Location: /userfin.php?err=$err");
else
  header('Location: /userfin.php?login='.urlencode($userLogin));
dbClose();
?>
