<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/users.php');

require_once('parts/top.php');

dbOpen();
session($sessionid);
?>
<html>
<head>
 <title>���� ���������� �������� - ������������</title>
</head>
<body bgcolor=white>
  <?php displayTop('users'); ?>
  <p>
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
	     '&redir='.urlencode($REQUEST_URI).'">'.$item->getLogin().
	     '</a></td>';
	echo '<td align=center>'.$item->getFullName().'</td>';
	echo '<td align=center>'.$item->getAge().'</td>';
	echo '<td align=center>'.$item->getMigdalStudent().'</td>';
	echo '<td align=center>'.$item->getEmailLink().'</td>';
	echo '<td align=center>'.$item->getICQStatusImage().'&nbsp;'
	                        .$item->getICQ().'</td>';
	echo '<td align=center>'.$item->getLastOnline().'</td>';
	echo '</tr>';
	}
   ?>
  </table>
</body>
</html>
<?php
dbClose();
?>
