<?php
# @(#) $Id$

require_once('lib/charsets.php');
require_once('lib/ident.php');

function postInteger($name)
{
global $HTTP_POST_VARS;

settype($GLOBALS[$name],'integer');
settype($HTTP_POST_VARS[$name],'integer');
}

function postIntegerArray($name)
{
global $HTTP_POST_VARS;

if(!is_array($GLOBALS[$name]))
  $GLOBALS[$name]=array();
else
  foreach($GLOBALS[$name] as $var)
	 settype($var,'integer');
if(!is_array($HTTP_POST_VARS[$name]))
  $HTTP_POST_VARS[$name]=array();
else
  foreach($HTTP_POST_VARS[$name] as $var);
	 settype($var,'integer');
}

function postIdent($name,$table)
{
global $HTTP_POST_VARS;

$GLOBALS[$name]=idByIdent($table,addslashes($GLOBALS[$name]));
$HTTP_POST_VARS[$name]=idByIdent($table,addslashes($HTTP_POST_VARS[$name]));
}

function postString($name)
{
global $HTTP_POST_VARS;

if(isset($GLOBALS[$name]))
  $GLOBALS[$name]=convertInput($GLOBALS[$name]);
if(isset($HTTP_POST_VARS[$name]))
  $HTTP_POST_VARS[$name]=convertInput($HTTP_POST_VARS[$name]);
}
?>
