<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');

require_once('parts/top.php')
?>
<html>
<head>
 <title>Клуб Еврейского Студента</title>
</head>
<body bgcolor=white>
  <?php
  dbOpen();
  displayTop('index');
  dbClose();
  ?>
</body>
</html>
