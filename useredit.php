<?php
# @(#) $Id$

require_once('lib/global.php');
require_once('lib/database.php');
require_once('lib/users.php');

require_once('top.php');
?>
<html>
<head>
 <title>Клуб Еврейского Студента - Информация о пользователе</title>
</head>
<body bgcolor=white>
  <?php
  dbOpen();
  displayTop('users');
  ?>
  <p>
  <?php
  $user=getUserById($editid);
  if($user->isEditable())
    echo '<form>';
  ?>
  <table>
   <tr>
    <td>Ник:</td>
    <td>
    <?php
    echo !$user->isEditable() ? $user->getLogin() 
                              : '<input type=text name="login" value="'.
	 		         $user->getLogin().'" size=30 maxlength=30>';
    ?>
    </td>
   </tr>
   <?php
   if($user->isEditable())
     {
     ?>
     <tr>
      <td>Пароль:</td><td><input type=password name='password' size=30></td>
     </tr>
     <?php
     }
     ?>
  </table>
  <?php
  if($user->isEditable())
    echo '</form>';
  dbClose();
  ?>
</body>
</html>
