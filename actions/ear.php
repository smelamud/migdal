<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/logs.php');
require_once('lib/post.php');

postInteger('postid');

dbOpen();
session();
logEvent('ear',"post($postid)");
header("Location: $okdir");
dbClose();
?>
