<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/uri.php');
require_once('lib/post.php');
require_once('lib/random.php');

SetCookie('sessionid',0,0,'/');
header('Location: '.remakeURI($okdir,array(),array('reload' => random(0,999))));
?>
