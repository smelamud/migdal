<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/logs.php');

dbOpen();
session($sessionid);
logEvent('link',"[$msgid] $okdir");
header("Location: $okdir");
dbClose();
?>
