<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');

dbOpen();
SetCookie('sessionid');
header("Location: $redir");
dbClose();
?>
