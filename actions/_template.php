<?php
# @(#) $Id$

# This is a template of action page

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/sql.php');

dbOpen();
session();
header("Location: $okdir");
dbClose();
?>
