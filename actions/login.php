<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/errors.php');

function startSession()
{
global $login,$password;

$result=mysql_query('select id from users where login="'
                     .AddSlashes($login).'" and password="'
		     .AddSlashes(md5($password)).'"');
if(!$result)
  die('Ошибка SQL при выборке логина и пароля');
if(mysql_num_rows($result)==0)
  return EL_INVALID;
$id=mysql_result($result,0,0);
srand(time());
$sid=rand();
mysql_query("insert into sessions(user_id,sid) values($id,$sid)")
     or die('Ошибка SQL при создании сессии');
SetCookie('sessionid',$sid,time()+7200);
return EL_OK;
}

dbOpen();
$err=startSession();
header("Location: $redir".($err!=EL_OK ? "?err=$err#error" : ''));
dbClose();
?>
