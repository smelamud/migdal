<?php
# @(#) $Id$

# This is a template of user-viewable page

require_once('lib/errorreporting.php');
require_once('lib/database.php');

require_once('parts/top.php');
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
 <center><font color=green>
  <h1>Пользователь <?php echo $login?> успешно зарегистрирован.<br>
      Добро пожаловать!</h1>
 </font></center>
 <p>
 <a href='<?php echo $redir ?>'>&lt;&lt; Вернуться</a>
</body>
</html>
