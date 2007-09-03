<?php
# @(#) $Id$

$Callbacks=array('image' => 'nullCallback');

function nullCallback($data)
{
}

function callback($name,$data)
{
global $Callbacks;

$callback=$Callbacks[$name];
return $callback($data);
}

function setCallback($name,$value)
{
global $Callbacks;

$Callbacks[$name]=$value;
}

function beginCallback()
{
ob_start();
}

function endCallback()
{
$s=ob_get_contents();
ob_end_clean();
return $s;
}
?>
