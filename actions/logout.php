<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/uri.php');

SetCookie('sessionid',0,0,'/');
header('Location: '.remakeURI($okdir,array(),array('reload' => rand(0,999))));
?>
