<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');

require_once('parts/top.php');
require_once('parts/messages.php');
?>
<html>
<head>
 <title>Клуб Еврейского Студента</title>
</head>
<body bgcolor=white>
  <?php
  dbOpen();
  displayTop('index');
  displayMessages();
  dbClose();
  ?>
</body>
</html>
