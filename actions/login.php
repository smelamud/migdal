<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/errors.php');
require_once('lib/utils.php');
require_once('lib/post.php');
require_once('lib/random.php');
require_once('lib/logs.php');

function startSession()
{
global $login,$password;

$result=mysql_query('select id from users where login="'
                     .addslashes($login).'" and password="'
		     .addslashes(md5($password)).'" and no_login=0')
        or die('Ошибка SQL при выборке логина и пароля');
if(mysql_num_rows($result)==0)
  return EL_INVALID;
$id=mysql_result($result,0,0);
logEvent('login',"user($id)");
$sid=rnd();
mysql_query("insert into sessions(user_id,real_user_id,sid)
             values($id,$id,$sid)")
     or die('Ошибка SQL при создании сессии');
SetCookie('sessionid',$sid,time()+7200,'/');
return EL_OK;
}

postString('login');
postString('password');

dbOpen();
$err=startSession();
if($err==EL_OK)
  header("Location: $okdir");
else
  header('Location: '.remakeURI($faildir,
                                array(),
		  	        array('err' => $err)).'#error');
dbClose();
?>
