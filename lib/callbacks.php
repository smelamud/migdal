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
global $callbackBuffer;

$callbackBuffer=ob_get_contents();
if(function_exists('ob_clean'))
  ob_clean();
else
  {
  ob_end_clean();
  ob_start();
  }
}

function endCallback()
{
global $callbackBuffer;

$s=ob_get_contents();
if(function_exists('ob_clean'))
  ob_clean();
else
  {
  ob_end_clean();
  ob_start();
  }
echo $callbackBuffer;
return $s;
}
?>
