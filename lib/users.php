<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/selectiterator.php');
require_once('lib/months.php');

class User
      extends DataObject
{
var $id;
var $login;
var $password;
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

function User($row)
{
$this->DataObject($row);
}

function setupHTTP($vars)
{
if(!isset($vars['login']))
  return;
foreach(array('login','name','jewish_name','surname','migdal_student','info',
              'email','icq') as $var)
       $this->$var=quotemeta($vars[$var]);
$this->birthday='19'.$vars['birth_year'].'-'.$vars['birth_month']
                                        .'-'.$vars['birth_day'];
$this->email_disabled=$vars['email_enabled'] ? 0 : 1;
}

function isEditable()
{
return $this->id==0;
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
return $bt[2];
}

function getMonthOfBirth()
{
$bt=explode('-',$this->birthday);
return $bt[1];
}

function isMonthOfBirth($month)
{
return $this->getMonthOfBirth()==$month;
}

function getYearOfBirth()
{
$bt=explode('-',$this->birthday);
return substr($bt[0],2);
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

function isEmailDisabled()
{
return $this->email_disabled;
}

function getLastOnline()
{
$t=strtotime($this->last_online);
return date('j/m/Y в H:i:s',$t);
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
