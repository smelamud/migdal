<?php
# @(#) $Id$

require_once('lib/errors.php');
require_once('lib/utils.php');

function displayLoginError($err)
{
if($err!=EL_INVALID)
  return;
?>
<tr><td colspan=5><a name='error'>
 <font color='red'>Неверный ник или пароль</font>
</td></tr>
<?php
}

function displayLogin($flags,$err)
{
global $userId,$REQUEST_URI;

?>
<form method=post action='actions/<?php
 echo $userId<0 ? 'login.php' : 'logout.php';
?>'>
<input type=hidden name='redir' value='<?php
 echo remakeURI($REQUEST_URI,array('err'));
?>'>
<table>
<?php
displayLoginError($err);
if($userId<0)
  {
  ?>
  <tr>
   <td>Ник</td>
   <td><input type=text name='login' size=6 maxlength=30></td>
   <td>Пароль</td>
   <td><input type=password name='password' size=6></td>
   <td><input type=submit value='Войти'></td>
  </tr>
  <?php
  if($flags!='no_new')
    {
    ?>
    <tr><td colspan=5>
     <a href='/useredit.php?redir='<?php
      echo urlencode($REQUEST_URI);
     ?>'>Зарегистрироваться</a>
    </td></tr>
    <?php
    }
  }
else
  {
  ?>
  <tr><td colspan=5><input type=submit value='Выйти'></td></tr>
  <?php
  }
?>
</table>
</form>
<?php
}
?>
