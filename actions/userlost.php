<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/users.php');
require_once('lib/tmptexts.php');
require_once('lib/mailings.php');

function repairPassword($id)
{
global $passwdCommand;

if($id<=0)
  return EPL_NO_LOGIN;
$password=trim(`$passwdCommand`);
$result=mysql_query("update users
                     set password=md5('$password')
		     where id=$id");
if(!$result)
  return EPL_STORE_SQL;
$passwordId=tmpTextSave($password);
sendMail(MAIL_REPAIR_PASSWORD,$id,$passwordId);
sendMailAdmin(MAIL_REPAIRING_PASSWORD,'admin_users',$id);
return EPL_OK;
}

postString('login');

dbOpen();
session();
$id=getUserIdByLogin($login);
$err=repairPassword($id);
if($err==EPL_OK)
  header('Location: '.remakeURI($okdir,
                                array('err'),
				array('userid' => $id)));
else
  header('Location: '.remakeURI($faildir,
                                array(),
				array('err' => $err)).'#error');
dbClose();
?>
