<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');

require_once('top.php')
?>
<html>
<head>
 <title>���� ���������� ��������</title>
</head>
<body bgcolor=white>
  <?php
  dbOpen();
  displayTop('index');
  dbClose();
  ?>
</body>
</html>
