<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/users.php');
require_once('lib/utils.php');
require_once('lib/errors.php');

function modifyUser($user)
{
global $editid;

if(!$user->isEditable())
  return EUM_NO_EDIT;
if((!$editid || $user->password!='') && strlen($user->password)<5)
  return EUM_PASSWORD_LEN;
if($user->password!=$user->dup_password)
  return EUM_PASSWORD_DIFF;
if($user->login=='')
  return EUM_LOGIN_ABSENT;
if(!$editid)
  {
  $result=mysql_query('select id from users where login="'.
                     AddSlashes($user->login).'"');
  if(mysql_num_rows($result)>0)
    return EUM_LOGIN_EXISTS;
  }
if(!checkdate($user->getMonthOfBirth(),$user->getDayOfBirth(),
              '19'.$user->getYearOfBirth()))
  return EUM_BIRTHDAY;
if(!$user->store())
  return EUM_STORE_SQL;
if(!$user->online())
  return EUM_ONLINE_SQL;
return $editid ? EUM_UPDATE_OK : EUM_INSERT_OK;
}

dbOpen();
session($sessionid);
$user=getUserById($editid);
$user->setupHTTP($HTTP_POST_VARS);
$err=modifyUser($user);
if($err==EUM_INSERT_OK)
  header('Location: /userok.php?login='.urlencode($login)
                             .'&redir='.urlencode($redir));
else
  header('Location: /useredit.php?'.
          makeQuery($HTTP_POST_VARS,array('password','dup_password')).
          "&err=$err#error");
dbClose();
?>
