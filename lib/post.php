<?php
# @(#) $Id$

require_once('lib/charsets.php');
require_once('lib/ident.php');
require_once('lib/users.php');

$Args=array();

function postValue($name,$value)
{
global $Args;

$Args[$name]=$value;
$GLOBALS[$name]=$value;
}

function postProcessInteger($value)
{
return (int)$value;
}

function postInteger($name)
{
global $Args;

if(!isset($_REQUEST[$name]) && isset($Args[$name]))
  return;
postValue($name,
          postProcessInteger(isset($_REQUEST[$name]) ? $_REQUEST[$name] : 0));
}

function postProcessIntegerArray($value)
{
$result=array();
if(!is_array($value))
  $result[]=(int)$value;
else
  foreach($value as $key => $var)
	 $result[$key]=(int)$var;
return $result;
}

function postIntegerArray($name)
{
global $Args;

if(!isset($_REQUEST[$name]) && isset($Args[$name]))
  return;
postValue($name,
          postProcessIntegerArray(isset($_REQUEST[$name])
	                          ? $_REQUEST[$name]
				  : array()));
}

function postProcessIntegerArray2D($value)
{
$result=array();
if(!is_array($value))
  $result[]=array((int)$value);
else
  foreach($value as $key => $var)
	 $result[$key]=postProcessIntegerArray($var);
return $result;
}

function postIntegerArray2D($name)
{
global $Args;

if(!isset($_REQUEST[$name]) && isset($Args[$name]))
  return;
postValue($name,
          postProcessIntegerArray2D(isset($_REQUEST[$name])
	                            ? $_REQUEST[$name]
				    : array()));
}

function postProcessIdent($value,$table='entries')
{
return idByIdent($value,$table);
}

function postIdent($name,$table='entries')
{
global $Args;

if(!isset($_REQUEST[$name]) && isset($Args[$name]))
  return;
postValue($name,
          postProcessIdent(isset($_REQUEST[$name]) ? $_REQUEST[$name] : 0,
	                   $table));
}

function postProcessString($value)
{
return $value;
}

function postString($name,$convert=true)
{
global $Args;

if(!isset($_REQUEST[$name]) && !isset($_REQUEST["${name}_i"])
   && isset($Args[$name]))
  return;
if(isset($_REQUEST["${name}_i"]))
  $value=tmpTextRestore($_REQUEST["${name}_i"]);
else
  {
  $value=isset($_REQUEST[$name]) ? $_REQUEST[$name] : '';
  if($convert)
    $value=convertInput($value);
  }
postValue($name,postProcessString($value));
}

function postProcessUser($value)
{
return idByLogin($value);
}

function postUser($name)
{
global $Args;

if(!isset($_REQUEST[$name]) && isset($Args[$name]))
  return;
postValue($name,
          postProcessUser(isset($_REQUEST[$name]) ? $_REQUEST[$name] : 0));
}

function commandLineArgs()
{
global $Args;

$Args=array_slice($_SERVER['argv'],1);
}
?>
