<?php
# @(#) $Id$

require_once('lib/charsets.php');
require_once('lib/ident.php');
require_once('lib/users.php');

$Args=array();

function postIntegerValue($name,$value)
{
global $Args;

if(isset($Args[$name]))
  return;
settype($value,'integer');
$Args[$name]=$value;
$GLOBALS[$name]=$value;
}

function postInteger($name)
{
postIntegerValue($name,isset($_REQUEST[$name]) ? $_REQUEST[$name] : 0);
}

function postIntegerArray($name)
{
global $Args;

if(isset($Args[$name]))
  return;
$Args[$name]=array();
$GLOBALS[$name]=array();
if(!isset($_REQUEST[$name]))
  return;
if(!is_array($_REQUEST[$name]))
  {
  $value=$_REQUEST[$name];
  settype($value,'integer');
  $Args[$name][]=$value;
  $GLOBALS[$name][]=$value;
  }
else
  {
  foreach($_REQUEST[$name] as $var)
         {
	 $value=$var;
	 settype($value,'integer');
	 $Args[$name][]=$value;
	 $GLOBALS[$name][]=$value;
	 }
  }
}

function postIdentValue($name,$value,$table='entries')
{
global $Args;

if(isset($Args[$name]))
  return;
$value=idByIdent($value,$table);
$Args[$name]=$value;
$GLOBALS[$name]=$value;
}

function postIdent($name,$table='entries')
{
postIdentValue($name,isset($_REQUEST[$name]) ? $_REQUEST[$name] : 0,$table);
}

function postStringValue($name,$value)
{
global $Args;

if(isset($Args[$name]))
  return;
$Args[$name]=$value;
$GLOBALS[$name]=$value;
}

function postString($name,$convert=true)
{
if(isset($_REQUEST["$name_i"]))
  $value=tmpTextRestore($_REQUEST["$name_i"]);
else
  {
  $value=isset($_REQUEST[$name]) ? $_REQUEST[$name] : '';
  if($convert)
    $value=convertInput($value);
  }
postStringValue($name,$value);
}

function postUserValue($name,$value)
{
global $Args;

if(isset($Args[$name]))
  return;
$value=isId($value) ? $value : getUserIdByLogin($value);
$Args[$name]=$value;
$GLOBALS[$name]=$value;
}

function postUser($name)
{
postUserValue($name,isset($_REQUEST[$name]) ? $_REQUEST[$name] : 0);
}

function commandLineArgs()
{
global $Args;

$Args=array_slice($_SERVER['argv'],1);
}
?>
