<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/users.php');
require_once('lib/utils.php');
require_once('lib/errors.php');
require_once('lib/bug.php');
require_once('lib/tmptexts.php');
require_once('lib/mail.php');
require_once('lib/sql.php');
require_once('lib/captcha.php');

function modifyUser($user,$original)
{
global $captcha,$disableRegister,$usersMandatorySurname,$userAdminUsers;

if($user->id==0 && $disableRegister && !$userAdminUsers)
  return EUM_DISABLED;
if(!$user->isEditable())
  return EUM_NO_EDIT;
if($user->login=='')
  return EUM_LOGIN_ABSENT;
if(($user->id==0 || $user->password!='') && strlen($user->password)<5)
  return EUM_PASSWORD_LEN;
if($user->password!=$user->dup_password)
  return EUM_PASSWORD_DIFF;
if(userLoginExists($user->login,$user->id))
  return EUM_LOGIN_EXISTS;
if($user->name=='')
  return EUM_NAME_ABSENT;
if($usersMandatorySurname && $user->surname=='')
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
if($user->id==0)
  {
  if($captcha=='')
    return EUM_CAPTCHA_ABSENT;
  if(!validateCaptcha($captcha))
    return EUM_CAPTCHA;
  }
if(!$userAdminUsers)
  $user->rights=$user->rights & USR_USER | $original->rights & ~USR_USER;
storeUser($user);
if($original->id==0)
  if(!$userAdminUsers)
    {
    preconfirmUser($user->getId());
    postMailTo($user->getId(),'register',array($user->getId()));
    postMailToAdmins(USR_ADMIN_USERS,'registering',array($user->getId()));
    }
return EG_OK;
}

httpRequestString('okdir');
httpRequestString('faildir');
httpRequestInteger('editid');
httpRequestInteger('edittag');
httpRequestString('captcha');
httpRequestString('new_login');
httpRequestString('new_password');
httpRequestString('dup_password');
httpRequestString('name');
httpRequestString('jewish_name');
httpRequestString('surname');
httpRequestString('gender');
httpRequestIntegerArray('rights');
httpRequestString('info');
httpRequestString('email');
httpRequestInteger('hide_email');
httpRequestString('icq');
httpRequestInteger('hidden');
httpRequestInteger('no_login');
httpRequestInteger('has_personal');
httpRequestInteger('birth_year');
httpRequestInteger('birth_month');
httpRequestInteger('birth_day');
httpRequestInteger('email_enabled');

dbOpen();
session();
$user=getUserById($editid);
$original=$user;
$user->setup($Args);
$err=modifyUser($user,$original);
if($err==EG_OK)
  {
  $parts=parse_url($okdir);
  $okdir=$parts['path'];
  if(substr($okdir,-1)!='/')
    $okdir.='/';
  $okdir.=$user->getFolder().'/';
  if($parts['query']!='')
    $okdir.='?'.$parts['query'];
  header("Location: $okdir");
  }
else
  {
  $infoId=tmpTextSave($info);
  header('Location: '.
	  remakeMakeURI($faildir,
			$Args,
			array('new_password','dup_password','info','okdir',
			      'faildir'),
			array('info_i' => $infoId,'err' => $err)).'#error');
  }
dbClose();
?>
