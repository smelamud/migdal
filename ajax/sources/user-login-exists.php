<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/json.php');
require_once('lib/users.php');

httpRequestString('login');

dbOpen();
session();
header('Content-Type: application/json');
jsonOutput(array('login' => $login,
                 'exists' => userLoginExists($login)));
dbClose();
?>
