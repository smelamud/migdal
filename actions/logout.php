<?php
# @(#) $Id$

require_once('lib/errorreporting.php');

SetCookie('sessionid',0,0,'/');
header("Location: $redir");
?>
