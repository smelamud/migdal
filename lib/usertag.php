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
var $rebe;

function UserTag($row)
{
$gender='mine';
$this->DataObject($row);
}

function getReplyLink()
{
return $this->email!='' && !$this->hide_email
       ? '<a href="mailto:'.$this->email.'" title="Написать письмо">' : '';
}

function getLogin()
{
return $this->login;
}

function getUserName()
{
return $this->getLogin();
}

function getLoginLink()
{
$link=$this->getReplyLink();
return $link!='' ? $link.$this->login.'</a>' : $this->login;
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

function getEmailLink()
{
$link=$this->getReplyLink();
return $link!='' ? $link.$this->email.'</a>' : '';
}

function isHideEmail()
{
return $this->hide_email;
}

function isRebe()
{
return $this->rebe;
}

}
?>
