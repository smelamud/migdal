<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/users.php');
require_once('lib/utils.php');

dbOpen();
header('Location: /useredit.php?'.makeQuery($HTTP_POST_VARS,array('password')));
dbClose();
?>
