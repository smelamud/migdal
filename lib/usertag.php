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

}
?>
