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
require_once('lib/mailings.php');
require_once('lib/sql.php');

function modifyUser($user)
{
global $editid,$disableRegister,$userAdminUsers;

if(!$editid)
  $editid=0;
if($editid==0 && $disableRegister && !$userAdminUsers)
  return EUM_DISABLED;
if(!$user->isEditable())
  return EUM_NO_EDIT;
if($user->login=='')
  return EUM_LOGIN_ABSENT;
if((!$editid || $user->password!='') && strlen($user->password)<5)
  return EUM_PASSWORD_LEN;
if($user->password!=$user->dup_password)
  return EUM_PASSWORD_DIFF;
if(userLoginExists($user->login,$editid))
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
if(!$userAdminUsers)
  $user->rights=$user->rights & USR_USER | $original->rights & ~USR_USER;
storeUser($user);
if($editid==0)
  if(!$userAdminUsers)
    {
    preconfirmUser($user->getId());
    sendMail(MAIL_REGISTER,$user->getId(),$user->getId());
    sendMailAdmin(MAIL_REGISTERING,USR_ADMIN_USERS,$user->getId());
    }
return EG_OK;
}

postString('okdir');
postString('faildir');
postInteger('editid');
postInteger('edittag');
postString('login');
postString('password');
postString('dup_password');
postString('name');
postString('jewish_name');
postString('surname');
postString('gender');
postIntegerArray('rights');
postString('info');
postString('email');
postInteger('hide_email');
postString('icq');
postInteger('hidden');
postInteger('no_login');
postInteger('has_personal');
postInteger('birth_year');
postInteger('birth_month');
postInteger('birth_day');
postInteger('email_enabled');

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
			array('password','dup_password','info','okdir',
			      'faildir'),
			array('info_i' => $infoId,'err' => $err)).'#error');
  }
dbClose();
?>
