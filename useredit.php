<?php
# @(#) $Id$

require_once('lib/global.php');
require_once('lib/database.php');
require_once('lib/users.php');

require_once('top.php');

function condEdit($edit,$value,$name,$size,$length)
{
return !$edit ? $value : "<input type=text name='$name' value='$value'
                          size=$size maxlength=$length>";
}

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
    echo condEdit($user->isEditable(),$user->getLogin(),'login',30,30);
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
