<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/logs.php');
require_once('lib/post.php');

postInteger('msgid');

dbOpen();
session();
logEvent('link',"msg($msgid) $okdir");
header("Location: $okdir");
dbClose();
?>
