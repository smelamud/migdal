<?php
# @(#) $Id$

require_once('lib/utils.php');

function makeRedirURL()
{
global $SCRIPT_NAME,$HTTP_GET_VARS;

$query=makeQuery($HTTP_GET_VARS,array('err'));
return $query!='' ? "$SCRIPT_NAME?$query" : $SCRIPT_NAME;
}
?>
