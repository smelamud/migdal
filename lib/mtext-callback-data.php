<?php
# @(#) $Id$

class ImageCallbackData
{
var $id;
var $align;
var $image;
var $par;

function __construct()
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

function __construct()
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

class IncutCallbackData
{
var $align='right';
var $width='50%';

function __construct()
{
}

function getAlign()
{
return $this->align;
}

function getWidth()
{
return $this->width;
}

}
?>
