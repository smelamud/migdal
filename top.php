<?php
# @(#) $Id$

require_once('lib/menu.php');
require_once('lib/session.php');
require_once('lib/errors.php');
require_once('lib/login.php');

function displayMenu($current)
{
$menu=new MenuIterator($current);
while($item=$menu->next())
     {
     $s='['.$item->getName().']';
     echo $item->isCurrent() ? "<b>$s</b>"
                             : '<a href="'.$item->getLink()."\">$s</a>";
     }
}

function displayError()
{
global $err;

if($err!=EL_INVALID)
  return;
?>
<tr><td colspan=5><a name='error'>
 <font color='red'>Неверный ник или пароль</font>
</td></tr>
<?php
}

function displayLogin($flags)
{
global $userId,$REQUEST_URI;

echo "<form method=post action='actions/".
                     ($userId<0 ? 'login.php' : 'logout.php')."'>
      <input type=hidden name='redir' value='".makeRedirURL()."'>
      <table>";
displayError();
if($userId<0)
  {
  echo '<tr>
         <td>Ник</td>
	 <td><input type=text name=\'login\' size=6 maxlength=30></td>
	 <td>Пароль</td>
	 <td><input type=password name=\'password\' size=6></td>
	 <td><input type=submit value=\'Войти\'></td>
	</tr>';
  if($flags!='no_new')
    echo '<tr><td colspan=5>
           <a href=\'/useredit.php?redir='.urlencode($REQUEST_URI)
	                                  .'\'>Зарегистрироваться</a>
	  </td></tr>';
  }
else
  echo '<tr><td colspan=5><input type=submit value=\'Выйти\'></td></tr>';
echo '</table>
      </form>';
}

function displayTop($current,$flags='')
{
global $sessionid;

displayMenu($current);
session($sessionid);
displayLogin($flags);
}
?>
