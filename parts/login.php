<?php
# @(#) $Id$

require_once('lib/errors.php');
require_once('lib/login.php');

function displayLoginError($err)
{
if($err!=EL_INVALID)
  return;
?>
<tr><td colspan=5><a name='error'>
 <font color='red'>�������� ��� ��� ������</font>
</td></tr>
<?php
}

function displayLogin($flags,$err)
{
global $userId,$REQUEST_URI;

echo "<form method=post action='actions/".
                     ($userId<0 ? 'login.php' : 'logout.php')."'>
      <input type=hidden name='redir' value='".makeRedirURL()."'>
      <table>";
displayLoginError($err);
if($userId<0)
  {
  echo '<tr>
         <td>���</td>
	 <td><input type=text name=\'login\' size=6 maxlength=30></td>
	 <td>������</td>
	 <td><input type=password name=\'password\' size=6></td>
	 <td><input type=submit value=\'�����\'></td>
	</tr>';
  if($flags!='no_new')
    echo '<tr><td colspan=5>
           <a href=\'/useredit.php?redir='.urlencode($REQUEST_URI)
	                                  .'\'>������������������</a>
	  </td></tr>';
  }
else
  echo '<tr><td colspan=5><input type=submit value=\'�����\'></td></tr>';
echo '</table>
      </form>';
}
?>
