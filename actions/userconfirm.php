<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');

function confirmUser($code,&$userLogin)
{
$result=mysql_query('select login
                     from users
		     where confirm_code=\''.addslashes($code).'\'
		           and hidden<2');
if(!$result)
  return EUC_SQL_SELECT;
if(mysql_num_rows($result)<=0)
  return EUC_NO_USER;
$userLogin=mysql_result($result,0,0);
$result=mysql_query("update users
                     set no_login=0,hidden=0,confirm_deadline=null
		     where login='$userLogin'");
if(!$result)
  return EUC_SQL_CONFIRM;
return EUC_OK;
}

dbOpen();
session($sessionid);
$err=confirmUser($code,$userLogin);
if($err!=EUC_OK)
  header("Location: /userfin.php?err=$code");
else
  header('Location: /userfin.php?login='.urlencode($userLogin));
dbClose();
?>
