<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/selectiterator.php');

class User
      extends DataObject
{
var $id;
var $login;
var $password;
var $name;
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

function getFullName()
{
return $this->name.($this->jewish_name!='' ? ' ('.$this->jewish_name.')' : '').
       ' '.$this->surname;
}

function getAge()
{
$bt=explode('-',$this->birthday);
$t=getdate();
$jbt=GregorianToJD($bt[1],$bt[2],$bt[0]);
$jt=GregorianToJD($t['mon'],$t['mday'],$t['year']);
return (int)(($jt-$jbt)/365.25);
}

function getMigdalStudent()
{
return $this->migdal_student ? 'Да' : 'Нет';
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
