<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/users.php');

dbOpen();
header("Location: $redir");
dbClose();
?>
