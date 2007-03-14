<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/logs.php');
require_once('lib/post.php');
require_once('lib/postings.php');
require_once('lib/counters.php');

postString('okdir');

postInteger('id');

dbOpen();
session();
incCounter($id,CMODE_EAR_CLICKS);
header("Location: $okdir");
dbClose();
?>
