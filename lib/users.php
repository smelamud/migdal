<?php
# @(#) $Id$

require_once('lib/dataobject.php');

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

}
?>
