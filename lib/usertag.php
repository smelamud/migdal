<?php
# @(#) $Id$

require_once('lib/dataobject.php');

class UserTag
      extends DataObject
{
var $login;
var $gender;
var $email;
var $hide_email;

function UserTag($row)
{
$gender='mine';
$this->DataObject($row);
}

function getLogin()
{
return $this->login;
}

function getLoginLink()
{
return $this->email!='' && !$this->hide_email
       ? '<a href="mailto:'.$this->email.'">'.$this->login.'</a>'
       : $this->login;
}

function isMan()
{
return $this->gender=='mine';
}

function isWoman()
{
return $this->gender=='femine';
}

function getGender()
{
return $this->gender;
}

function getGenderIndex()
{
return $this->isMan() ? 1 : 2;
}

function getEmail()
{
return $this->email;
}

function getEmailLink()
{
return $this->email!='' && !$this->hide_email
       ? '<a href="mailto:'.$this->email.'">'.$this->email.'</a>'
       : '';
}

function isHideEmail()
{
return $this->hide_email;
}

}
?>
