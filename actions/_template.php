<?php
# @(#) $Id$

# This is a template of action page

require_once('lib/errorreporting.php');
require_once('lib/database.php');

dbOpen();
header("Location: $redir");
dbClose();
?>
