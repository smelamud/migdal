<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/users.php');
require_once('lib/utils.php');

dbOpen();
$user=getUserById($editid);
$user->setupHTTP($HTTP_POST_VARS);
if($user->isEditable())
  {
  $user->store();
  $user->online();
  }
header('Location: /useredit.php?'.makeQuery($HTTP_POST_VARS,array('password')));
dbClose();
?>
