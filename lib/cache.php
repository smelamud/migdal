<?php
# @(#) $Id$

$Cache=array();

function getCachedValue($part,$table,$name)
{
global $Cache;

if(hasCachedValue($part,$table,$name))
  return $Cache[$part][$table][$name];
}

function hasCachedValue($part,$table,$name)
{
global $Cache;

return isset($Cache[$part]) && isset($Cache[$part][$table]) &&
       isset($Cache[$part][$table][$name]);
}

function setCachedValue($part,$table,$name,$value)
{
global $Cache;

if(!isset($Cache[$part]))
  $Cache[$part]=array();
if(!isset($Cache[$part][$table]))
  $Cache[$part][$table]=array();
$Cache[$part][$table][$name]=$value;
}
?>
