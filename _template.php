<?php
# @(#) $Id$

# This is a template of user-viewable page

require_once('lib/errorreporting.php');
require_once('lib/database.php');

require_once('top.php');
?>
<html>
<head>
 <title>Клуб Еврейского Студента - &lt;шаблон&gt;</title>
</head>
<body bgcolor=white>
 <?php
 dbOpen();
 displayTop('template');
 dbClose();
 ?>
</body>
</html>
