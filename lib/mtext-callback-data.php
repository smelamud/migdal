<?php
# @(#) $Id$

class ImageCallbackData
{
var $id;
var $align;
var $image;
var $par;

function ImageCallbackData()
{
}

function getId()
{
return $this->id;
}

function getAlign()
{
return $this->align;
}

function getImage()
{
return $this->image;
}

function getPar()
{
return $this->par;
}

}

class UserNameCallbackData
{
var $guest;
var $login;

function UserNameCallbackData()
{
}

function isGuest()
{
return $this->guest;
}

function getLogin()
{
return $this->login;
}

}
?>
