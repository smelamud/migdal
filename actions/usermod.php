<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
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
if(!preg_match('/^[A-Za-z0-9-_]+(\.[A-Za-z0-9-_]+)*@[A-Za-z0-9-]+(\.[A-Za-z0-9-]+)*$/',
               $user->email))
  return EUM_NOT_EMAIL;
if(!$user->store())
  return EUM_STORE_SQL;
if($editid==0)
  {
  if(!$user->preconfirm())
    return EUM_PRECONFIRM_SQL;
  sendMail('register',$user->getId(),$user->getId());
  sendMailAdmin('registering','admin_users',$user->getId());
  }
return EUM_OK;
}

postInteger('editid');
postString('login');
postString('password');
postString('dup_password');
postString('info');
postString('name');
postString('jewish_name');
postString('surname');

dbOpen();
session($sessionid);
$user=getUserById($editid);
$user->setup($HTTP_POST_VARS);
$err=modifyUser($user);
if($err==EUM_OK)
  header('Location: '.remakeURI($okdir,array(),array('login' => $login)));
else
  {
  $infoId=tmpTextSave($info);
  header('Location: '.
	  remakeMakeURI($faildir,
			$HTTP_POST_VARS,
			array('password','dup_password','info'),
			array('infoid' => $infoId,'err' => $err)).'#error');
  }
dbClose();
?>
