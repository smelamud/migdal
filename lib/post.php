<?php
# @(#) $Id$

require_once('lib/charsets.php');

function postInteger($name)
{
global $HTTP_POST_VARS;

settype($GLOBALS[$name],'integer');
settype($HTTP_POST_VARS[$name],'integer');
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
