<?php
# @(#) $Id$

# This is a template of user-viewable page

require_once('lib/errorreporting.php');
require_once('lib/database.php');

require_once('top.php');
?>
<html>
<head>
 <title>Клуб Еврейского Студента - Пользователь зарегистрирован</title>
</head>
<body bgcolor=white>
 <?php
 dbOpen();
 displayTop('users');
 dbClose();
 ?>
 <center><font color=green><h1>Пользователь успешно зарегистрирован.<br>
                               Добро пожаловать!</h1></font></center>
</body>
</html>
