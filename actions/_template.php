<?php
# @(#) $Id$

# This is a template of action page

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');

dbOpen();
session($sessionid);
header("Location: $redir");
dbClose();
?>
