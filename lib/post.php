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

$GLOBALS[$name]=convertInput($GLOBALS[$name]);
$HTTP_POST_VARS[$name]=convertInput($HTTP_POST_VARS[$name]);
}
?>
