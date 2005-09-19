<?php
# @(#) $Id$

require_once('lib/charsets.php');
require_once('lib/ident.php');

$Args=array();

function postInteger($name)
{
global $Args;

$value=isset($_REQUEST[$name]) ? $_REQUEST[$name] : 0;
settype($value,'integer');
$Args[$name]=$value;
$GLOBALS[$name]=$value;
}

function postIntegerArray($name)
{
global $Args;

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

function postIdent($name,$table='entries')
{
global $Args;

$value=isset($_REQUEST[$name]) ? idByIdent(addslashes($value),$table) : 0;
$Args[$name]=$value;
$GLOBALS[$name]=$value;
}

function postString($name,$convert=true)
{
global $Args;

if(isset($_REQUEST["$name_i"]))
  $value=tmpTextRestore($_REQUEST["$name_i"]);
else
  {
  $value=isset($_REQUEST[$name]) ? $_REQUEST[$name] : '';
  if($convert)
    $value=convertInput($value);
  }
$Args[$name]=$value;
$GLOBALS[$name]=$value;
}

function commandLineArgs()
{
global $Args;

$Args=array_slice($_SERVER['argv'],1);
}
?>
