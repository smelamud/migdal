<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/users.php');
require_once('lib/errors.php');
require_once('lib/session.php');

require_once('parts/top.php');
require_once('parts/utils.php');

function condCheckBoxLine($edit,$name,$value,$title,$textOn='',$textOff='')
{
return condCheckBox($edit,$name,$value,$title,$textOn,$textOff,2);
}

function perrorLine($code,$message,$color='red')
{
global $user;

if($user->isEditable())
  perror($code,$message,$color,2);
}

settype($editid,'integer');

dbOpen();
session();
?>
<html>
<head>
 <title>���� ���������� �������� - ���������� � ������������</title>
</head>
<body bgcolor=white>
  <?php displayTop('users',$editid ? '' : 'no_new'); ?>
  <p>
  <?php
  $user=getUserById($editid);
  $user->setup($HTTP_GET_VARS);
  if($user->isEditable())
    {
    ?>
    <center><h1>������� ���������� � ����</h1></center>
    <form method=post action='actions/usermod.php'>
    <input type=hidden name='edittag' value=1>
    <input type=hidden name='redir' value='<?php echo $redir ?>'>
    <input type=hidden name='editid' value='<?php echo $editid ?>'>
    <?php
    }
  ?>
  <table>
   <?php
   perrorLine(EUM_UPDATE_OK,'����� ���������� ������� � ��������','green');
   perrorLine(EUM_NO_EDIT,'� ��� ��� ����� ������ ���������� ��� �����
                           ������������');
   perrorLine(EUM_STORE_SQL,'������ ���� ������ ��� ���������� ������',
                            'magenta');
   perrorLine(EUM_ONLINE_SQL,'������ ���� ������ ��� ������ last_update',
                             'magenta');
   perrorLine(EUM_LOGIN_ABSENT,'��� �� ��� ������');
   perrorLine(EUM_LOGIN_EXISTS,'������������ � ����� ����� ��� ����������');
   echo condEdit('���',$user->isEditable(),$user->getLogin(),'login',20,30);
   perrorLine(EUM_PASSWORD_LEN,'������ ������ ���� �� ����� 5 ��������');
   perrorLine(EUM_PASSWORD_DIFF,'�������� ��� ����� ������ - ������� ��� ���');
   if($user->isEditable())
     {
     ?>
     <tr>
      <td>������ </td><td><input type=password name='password' size=20></td>
     </tr>
     <tr>
      <td>������ (��� ���) </td>
      <td><input type=password name='dup_password' size=20></td>
     </tr>
     <?php
     }
   echo condEdit('���',$user->isEditable(),$user->getName(),'name',30,30);
   echo condEdit('��������� ���',$user->isEditable(),$user->getJewishName(),
                 'jewish_name',30,30);
   echo condEdit('�������',$user->isEditable(),$user->getSurname(),'surname',
                 30,30);
   perrorLine(EUM_GENDER,'����������� ���');
   ?>
   <tr>
   <?php
   if(!$user->isEditable())
     {
     ?>
     <td><b>���:</b></td>
     <td><?php echo $user->isMan() ? '�������' : '�������' ?></td>
     <?php
     }
   else
     {
     ?>
     <td>���</td>
     <td>
      <input type='radio' name='gender' value='mine' <?php
       echo ($user->isMan() ? ' checked ' : '')
      ?> >�������</input>&nbsp;
      <input type='radio' name='gender' value='femine' <?php
       echo ($user->isWoman() ? ' checked ' : '')
      ?> >�������</input>
     </td>
     <?php
     }
   ?>
   </tr>
   <?php
   perrorLine(EUM_BIRTHDAY,'���������� ��������� ���� ��������');
   ?>
   <tr>
    <?php
    if($user->isEditable())
      {
      ?>
      <td>���� ��������</td>
      <td>
       <input type=text name='birth_day' size=2 maxlength=2
              value=<?php echo $user->getDayOfBirth() ?>>
       <select name='birth_month'>
        <?php
        foreach(array(1 => '������','�������','����','������','���','����',
	              '����','������','��������','�������','������','�������')
		as $key=>$value)
	       echo "<option ".($user->isMonthOfBirth($key) ? 'selected' : '')
	                      ." value=$key>$value</option>";
	?>
       </select>
       19<input type=text name='birth_year' size=2 maxlength=2
                value='<?php echo $user->getYearOfBirth()?>'>
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
   <?php
   echo condCheckBoxLine($user->isEditable(),'migdal_student',
	                 $user->isMigdalStudent(),'��������� � "�������"',
		         '���������� � "�������"');
   if($user->isEditable())
     {
     ?>
     <tr><td colspan=2>������� � ����</td></tr>
     <tr><td colspan=2>
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
   echo condEditValue('E-mail',$user->isEditable(),$user->getEmailLink(),
                      $user->getEmail(),'email',30,70);
   if($user->isEditable())
     {
     echo condCheckBoxLine($user->isEditable(),'hide_email',
			   $user->isHideEmail(),
			   '�� ���������� E-mail �� �����');
     echo condCheckBoxLine($user->isEditable(),'email_enabled',
			   $user->isEmailDisabled()==0,
			   '�������� �������� ����� �� ��� �����');
     }
   if($user->isEmailDisabled()==2)
     {
     ?>
     <tr><td colspan=2><font color=red>
      ������� ����� �� ���� ����� �������� ��������������, ��������� ����� ��
      ��������
     </font></td></tr>
     <?php
     }
   echo condEditStatus('ICQ',$user->isEditable(),$user->getICQStatusImage(),
                       $user->getICQ(),'icq',15,15);
   if(!$user->isEditable())
     {
     ?>
     <tr>
      <td><b>��������� ���<br>������� ����:</b></td>
      <td><?php echo $user->getLastOnline() ?></td>
     </tr>
     <?php
     }
   echo condCheckBoxLine($userAdminUsers,'has_personal',
		         $user->isHasPersonal(),'����� ������������ ��������');
   echo condCheckBoxLine($userAdminUsers,'admin_users',
			 $user->isAdminUsers(),'������������� �������������');
   echo condCheckBoxLine($userAdminUsers,'admin_topics',
			 $user->isAdminTopics(),'������������� ���');
   echo condCheckBoxLine($userAdminUsers,'hidden',
			 $user->isHidden(),
			 '�� ���������� � ������ �������������');
   echo condCheckBoxLine($userAdminUsers,'no_login',
		         $user->isNoLogin(),'��������� ����');
   ?>
  </table>
  <?php
  if($user->isEditable())
    {
    ?>
    <input type=submit value='<?php echo $editid ? '��������' 
                                                 : '����������������' ?>'>
    <input type=reset value='��������'>
    </form>
    <?php
    }
  ?>
</body>
</html>
<?php
dbClose();
?>
