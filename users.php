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
  ?>
  <p>
  <a href="useredit.php">Зарегистрироваться</a><br>
  <table>
   <tr valign=top>
    <th>Ник</th><th>Имя</th><th>Возраст</th><th>Мигдалевец</th><th>E-mail</th>
    <th>ICQ</th><th>Последний раз<br>был здесь</th>
   </tr>
   <?php
   $list=new UserListIterator();
   while($item=$list->next())
        {
	echo '<tr>';
	echo '<td align=center><a href="useredit.php?editid='.$item->getId().
	     '">'.$item->getLogin().'</a></td>';
	echo '<td align=center>'.$item->getFullName().'</td>';
	echo '<td align=center>'.$item->getAge().'</td>';
	echo '<td align=center>'.$item->getMigdalStudent().'</td>';
	echo '<td align=center>'.$item->getEmailLink().'</td>';
	echo '<td align=center>'.$item->getICQ().'</td>';
	echo '<td align=center>'.$item->getLastOnline().'</td>';
	echo '</tr>';
	}
   ?>
  </table>
  <?php
  dbClose();
  ?>
</body>
</html>
