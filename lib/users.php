<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/selectiterator.php');
require_once('lib/months.php');
require_once('lib/utils.php');

class User
      extends DataObject
{
var $id;
var $login;
var $password;
var $dup_password;
var $name;
var $jewish_name;
var $surname;
var $info;
var $birthday;
var $migdal_student;
var $last_online;
var $email;
var $icq;
var $email_disabled;
var $admin_users;

function User($row)
{
$this->DataObject($row);
}

function setupHTTP($vars)
{
if(!isset($vars['login']))
  return;
foreach(array('login','password','dup_password','name','jewish_name','surname',
              'migdal_student','info','email','icq','admin_users') as $var)
       $this->$var=htmlspecialchars($vars[$var]);
$this->birthday='19'.$vars['birth_year'].'-'.$vars['birth_month']
                                        .'-'.$vars['birth_day'];
$this->email_disabled=$vars['email_enabled'] ? 0 : 1;
}

function isEditable()
{
global $userId,$userAdminUsers;

return $this->id==0 || $this->id==$userId || $userAdminUsers;
}

function store()
{
global $userAdminUsers;

$normal=array('login'          => $this->login,
	      'name'           => $this->name,
	      'jewish_name'    => $this->jewish_name,
	      'surname'        => $this->surname,
	      'info'           => $this->info,
	      'birthday'       => $this->birthday,
	      'migdal_student' => $this->migdal_student,
	      'email'          => $this->email,
	      'icq'            => $this->icq,
	      'email_disabled' => $this->email_disabled);
if(!$this->id || $this->password!='')
  $normal=array_merge($normal,
        array('password'       => md5($this->password)));
if($userAdminUsers)
  $normal=array_merge($normal,
        array('admin_users'    => $this->admin_users));
$result=mysql_query($this->id 
                    ? makeUpdate('users',$normal,array('id' => $this->id))
                    : makeInsert('users',$normal));
if(!$this->id)
  $this->id=mysql_insert_id();
return $result;
}

function online()
{
if(!$this->id)
  return false;
return mysql_query('update users set last_online=now() where id='.$this->id);
}

function getId()
{
return $this->id;
}

function getLogin()
{
return $this->login;
}

function getName()
{
return $this->name;
}

function getJewishName()
{
return $this->jewish_name;
}

function getSurname()
{
return $this->surname;
}

function getFullName()
{
return $this->name.($this->jewish_name!='' ? ' ('.$this->jewish_name.')' : '').
       ' '.$this->surname;
}

function getInfo()
{
return $this->info;
}

function getAge()
{
$bt=explode('-',$this->birthday);
$t=getdate();
$jbt=GregorianToJD($bt[1],$bt[2],$bt[0]);
$jt=GregorianToJD($t['mon'],$t['mday'],$t['year']);
return (int)(($jt-$jbt)/365.25);
}

function getBirthday()
{
$bt=explode('-',$this->birthday);
return $bt[2].' '.getRussianMonth((int)$bt[1]).' '.$bt[0];
}

function getDayOfBirth()
{
$bt=explode('-',$this->birthday);
return $bt[2] ? $bt[2] : 1;
}

function getMonthOfBirth()
{
$bt=explode('-',$this->birthday);
return $bt[1] ? $bt[1] : 1;
}

function isMonthOfBirth($month)
{
return $this->getMonthOfBirth()==$month;
}

function getYearOfBirth()
{
$bt=explode('-',$this->birthday);
$c=substr($bt[0],2);
return $c ? $c : '00';
}

function isMigdalStudent()
{
return $this->migdal_student;
}

function getMigdalStudent()
{
return $this->migdal_student ? 'Да' : 'Нет';
}

function getEmail()
{
return $this->email;
}

function getEmailLink()
{
return $this->email!=''
       ? '<a href="mailto:'.$this->email.'">'.$this->email.'</a>'
       : '';
}

function getICQ()
{
return $this->icq;
}

function getICQStatusImage()
{
return $this->icq ? '<img src="http://wwp.icq.com/scripts/online.dll?icq='.
                                    $this->icq.'&img=5 width=18 height=18">'
		  : '';
}

function isEmailDisabled()
{
return $this->email_disabled;
}

function getLastOnline()
{
$t=strtotime($this->last_online);
return date('j/m/Y в H:i:s',$t);
}

function isAdminUsers()
{
return $this->admin_users;
}

}

class UserListIterator
      extends SelectIterator
{

function UserListIterator()
{
$this->SelectIterator('User',
                      'select id,login,name,jewish_name,surname,birthday,
		              migdal_student,email,icq,last_online
		       from users
		       order by login');
}

}

function getUserById($id)
{
$result=mysql_query("select *
                     from users
		     where id=$id");
return new User(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                          : array());
}
?>
