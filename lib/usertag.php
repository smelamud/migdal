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
var $user_hidden;
var $user_guest;
var $guest_login='';

function UserTag($row)
{
$this->gender='mine';
parent::DataObject($row);
}

function getLogin()
{
return $this->login;
}

function getUserName()
{
return $this->getLogin();
}

function getUserFolder()
{
return c_ascii($this->getLogin()) ? $this->getLogin() : $this->getUserId();
}

function isMan()
{
return $this->gender=='mine' || $this->gender=='';
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

function isHideEmail()
{
return $this->hide_email;
}

function isEmailVisible()
{
return $this->email!='' && !$this->hide_email;
}

function isUserHidden()
{
return $this->user_hidden ? 1 : 0;
}

function isUserAdminHidden()
{
return $this->user_hidden>1 ? 1 : 0;
}

function isUserVisible()
{
global $userAdminUsers;

return !$this->isUserHidden()
       || ($userAdminUsers && !$this->isUserAdminHidden());
}

function isUserGuest()
{
return $this->user_guest ? 1 : 0;
}

function getGuestLogin()
{
return $this->guest_login;
}

}
?>
