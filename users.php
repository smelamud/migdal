<?php
# @(#) $Id$

require_once('lib/global.php');
require_once('lib/database.php');
require_once('lib/users.php');

require_once('top.php');
?>
<html>
<head>
 <title>Клуб Еврейского Студента - Пользователи</title>
</head>
<body bgcolor=white>
  <?php
  dbOpen();
  displayTop('users');
  dbClose();
  ?>
</body>
</html>
