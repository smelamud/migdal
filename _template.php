<?php
# @(#) $Id$

# This is a template of user-viewable page

require_once('lib/errorreporting.php');
require_once('lib/database.php');

require_once('parts/top.php');

dbOpen();
session($sessionid);
?>
<html>
<head>
 <title>Клуб Еврейского Студента - &lt;шаблон&gt;</title>
</head>
<body bgcolor=white>
 <?php displayTop('template'); ?>
</body>
</html>
<?php
dbClose();
?>
