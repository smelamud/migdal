<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');

dbOpen();
session($sessionid);
header('Location: '.remakeURI($okdir,array(),array('st' => $style,
                                                   'bt' => $biff,
						   'rk' => $koi)));
dbClose();
?>
