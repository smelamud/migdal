<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/users.php');
require_once('lib/utils.php');
require_once('lib/errors.php');
require_once('lib/tmptexts.php');
require_once('lib/mailings.php');

function modifyUser($user)
{
global $editid;

if(!$editid)
  $editid=0;
if(!$user->isEditable())
  return EUM_NO_EDIT;
if($user->login=='')
  return EUM_LOGIN_ABSENT;
if((!$editid || $user->password!='') && strlen($user->password)<5)
  return EUM_PASSWORD_LEN;
if($user->password!=$user->dup_password)
  return EUM_PASSWORD_DIFF;
$result=mysql_query('select id from users where login="'.
		    addslashes($user->login)."\" and id<>$editid")
 	     or die('Ошибка SQL при выборке пользователя');
if(mysql_num_rows($result)>0)
  return EUM_LOGIN_EXISTS;
if($user->name=='')
  return EUM_NAME_ABSENT;
if($user->surname=='')
  return EUM_SURNAME_ABSENT;
if($user->getGender()!='mine' && $user->getGender()!='femine')
  return EUM_GENDER;
if(!checkdate($user->getMonthOfBirth(),$user->getDayOfBirth(),
              '19'.$user->getYearOfBirth()))
  return EUM_BIRTHDAY;
if($user->email=='')
  return EUM_EMAIL_ABSENT;
if(!preg_match('/^[A-Za-z-]+(\.[A-Za-z-]+)*@[A-Za-z-]+(\.[A-Za-z-]+)*$/',
               $user->email))
  return EUM_NOT_EMAIL;
if(!$user->store())
  return EUM_STORE_SQL;
if($editid==0)
  {
  if(!$user->preconfirm())
    return EUM_PRECONFIRM_SQL;
  sendMail('register',$user->getId(),$user->getId());
  }
return $editid ? EUM_UPDATE_OK : EUM_INSERT_OK;
}

settype($editid,'integer');

dbOpen();
session($sessionid);
$user=getUserById($editid);
$user->setup($HTTP_POST_VARS);
$err=modifyUser($user);
if($err==EUM_INSERT_OK)
  header('Location: /userok.php?'.
          makeQuery(array('login' => $login,'redir' => $redir)));
else
  if($err==EUM_UPDATE_OK)
    header("Location: $redir");
  else
    {
    $infoId=tmpTextSave($info);
    header('Location: '.
	    remakeMakeURI($caller,
	                  $HTTP_POST_VARS,
		          array('password','dup_password','info'),
		          array('infoid' => $infoId,'err' => $err)).'#error');
    }
dbClose();
?>
