<?php
# @(#) $Id$

require_once('lib/global.php');
require_once('lib/database.php');
require_once('lib/users.php');

require_once('top.php');
?>
<html>
<head>
 <title>���� ���������� �������� - ������������</title>
</head>
<body bgcolor=white>
  <?php
  dbOpen();
  displayTop('users');
  ?>
  <p>
  <a href="useredit.php">������������������</a><br>
  <table>
   <tr valign=top>
    <th>���</th><th>���</th><th>�������</th><th>����������</th><th>E-mail</th>
    <th>ICQ</th><th>��������� ���<br>��� �����</th>
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
