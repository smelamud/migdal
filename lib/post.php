<?php
# @(#) $Id$

require_once('lib/charsets.php');
require_once('lib/ident.php');
require_once('lib/users.php');

$Args=array();

function postValue($name,$value)
{
global $Args;

if(isset($Args[$name]))
  return;
$Args[$name]=$value;
$GLOBALS[$name]=$value;
}

function postProcessInteger($value)
{
return (int)$value;
}

function postInteger($name)
{
postValue($name,
          postProcessInteger(isset($_REQUEST[$name]) ? $_REQUEST[$name] : 0));
}

function postProcessIntegerArray($value)
{
$result=array();
if(!is_array($value))
  $result[]=(int)$value;
else
  foreach($value as $var)
	 $result[]=(int)$var;
return $result;
}

function postIntegerArray($name)
{
postValue($name,
          postProcessIntegerArray(isset($_REQUEST[$name])
	                          ? $_REQUEST[$name]
				  : array()));
}

function postProcessIdent($value,$table='entries')
{
return idByIdent($value,$table);
}

function postIdent($name,$table='entries')
{
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
if(isset($_REQUEST["$name_i"]))
  $value=tmpTextRestore($_REQUEST["$name_i"]);
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
return isId($value) ? $value : getUserIdByLogin($value);
}

function postUser($name)
{
postValue($name,
          postProcessUser(isset($_REQUEST[$name]) ? $_REQUEST[$name] : 0));
}

function commandLineArgs()
{
global $Args;

$Args=array_slice($_SERVER['argv'],1);
}
?>
