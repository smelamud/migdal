<?php
# @(#) $Id$

# This is a template of user-viewable page

require_once('lib/errorreporting.php');
require_once('lib/database.php');

require_once('top.php');
?>
<html>
<head>
 <title>���� ���������� �������� - ������������ ���������������</title>
</head>
<body bgcolor=white>
 <?php
 dbOpen();
 displayTop('users');
 dbClose();
 ?>
 <center><font color=green><h1>������������ ������� ���������������.<br>
                               ����� ����������!</h1></font></center>
</body>
</html>
