<?php
# @(#) $Id$

require_once('lib/global.php');
require_once('lib/database.php');
require_once('lib/users.php');

require_once('top.php');

function condEdit($title,$edit,$value,$name,$size,$length)
{
return !$edit ?
       $value!='' ? "<td><b>$title:</b></td><td>$value</td>" : '' :
       "<td>$title </td><td><input type=text name='$name' value='$value'
                            size=$size maxlength=$length></td>";
}

function condEditValue($title,$edit,$value,$valueEdit,$name,$size,$length)
{
return !$edit ?
       $value!='' ? "<td><b>$title:</b></td><td>$value</td>" : '' :
       "<td>$title </td><td><input type=text name='$name' value='$valueEdit'
                            size=$size maxlength=$length></td>";
}

function condCheckBox($edit,$name,$value,$title,$textOn='',$textOff='')
{
return !$edit ? '<i>'.($value ? $textOn : $textOff).'</i>'
              : "<input type=checkbox name=$name value=1 ".
	        ($value ? 'checked' : '')."> $title";
}

?>
<html>
<head>
 <title>���� ���������� �������� - ���������� � ������������</title>
</head>
<body bgcolor=white>
  <?php
  dbOpen();
  displayTop('users');
  ?>
  <p>
  <a href="useredit.php">����� ������������</a><br>
  <?php
  $user=getUserById($editid);
  if($user->isEditable())
    {
    ?>
    <center><h1>������� ���������� � ����</h1></center>
    <form method=post action='lib/usermod.php'>
    <input type=hidden name='redir' value='<?php echo $REQUEST_URI ?>'>
    <?php
    }
  ?>
  <table>
   <tr>
    <?php
    echo condEdit('���',$user->isEditable(),$user->getLogin(),'login',20,30);
    ?>
   </tr>
   <?php
   if($user->isEditable())
     {
     ?>
     <tr>
      <td>������ </td><td><input type=password name='password' size=20></td>
     </tr>
     <?php
     }
     ?>
   <tr>
    <?php
    echo condEdit('���',$user->isEditable(),$user->getName(),'name',30,30);
    echo condEdit('��������� ���',$user->isEditable(),$user->getJewishName(),
                  'jewish_name',30,30);
    ?>
   </tr>
   <tr>
    <?php
    echo condEdit('�������',$user->isEditable(),$user->getSurname(),'surname',
                  30,30);
    ?>
   </tr>
   <tr>
    <?php
    if($user->isEditable())
      {
      ?>
      <td>���� ��������</td>
      <td colspan=3>
       <input type=text name='birth_day' size=2 maxlength=2 value=1>
       <select name='birth_month'>
        <option selected value=1>������</option>
        <option value=2>�������</option>
        <option value=3>����</option>
        <option value=4>������</option>
        <option value=5>���</option>
        <option value=6>����</option>
        <option value=7>����</option>
        <option value=8>������</option>
        <option value=9>��������</option>
        <option value=10>�������</option>
        <option value=11>������</option>
        <option value=12>�������</option>
       </select>
       19<input type=text name='birth_year' size=2 maxlength=2 value='00'>
      </td>
      <?php
      }
    else
      {
      ?>
      <td><b>���� ��������:</b></td>
      <td><?php echo $user->getBirthday() ?></td>
      <?php
      }
    ?>
   </tr>
   <tr>
    <td colspan=2>
    <?php
     echo condCheckBox($user->isEditable(),'migdal_student',
                       $user->isMigdalStudent(),'��������� � "�������"',
		       '���������� � "�������"');
    ?>
    </td>
   </tr>
   <?php
   if($user->isEditable())
     {
     ?>
     <tr><td colspan=4>������� � ����</td></tr>
     <tr><td colspan=4>
      <textarea name='info' rows=10 cols=50 wrap='virtual'><?php
       echo $user->getInfo()
      ?></textarea>
     </td></tr>
     <?php
     }
   else
     if($user->getInfo()!='')
       {
       ?>
       <tr><td colspan=2><b>������� � ����:</b></td></tr>
       <tr><td colspan=2><?php echo $user->getInfo() ?></td></tr>
       <?php
       }
   ?>
   <tr>
    <?php
    echo condEditValue('E-mail',$user->isEditable(),$user->getEmailLink(),
                       $user->getEmail(),'email',30,70);
    ?>
   </tr>
   <?php
    if($user->isEditable())
      {
      ?>
      <tr>
       <td colspan=2>
       <?php
	echo condCheckBox($user->isEditable(),'email_enabled',
	                  $user->isEmailDisabled()==0,
			  '�������� �������� ����� �� ��� �����');
       ?>
       </td>
      </tr>
      <?php
      }
    if($user->isEmailDisabled()==2)
      {
      ?>
      <tr><td colspan=4><font color=red>
       ������� ����� �� ���� ����� �������� ��������������, ��������� ����� ��
       ��������
      </font></td></tr>
      <?php
      }
   ?>
   <tr>
    <?php
    echo condEdit('ICQ',$user->isEditable(),$user->getICQ(),'icq',15,15);
    ?>
   </tr>
   <?php
   if(!$user->isEditable())
     {
     ?>
     <tr>
      <td><b>��������� ���<br>������� ����:</b></td>
      <td><?php echo $user->getLastOnline() ?></td>
     </tr>
     <?php
     }
   ?>
  </table>
  <?php
  if($user->isEditable())
    {
    ?>
    <input type=submit value='���������'>
    <input type=reset value='��������'>
    </form>
    <?php
    }
  dbClose();
  ?>
</body>
</html>
