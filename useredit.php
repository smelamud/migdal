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
 <title>Клуб Еврейского Студента - Информация о пользователе</title>
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
    <center><h1>Введите информацию о себе</h1></center>
    <form method=post action='actions/usermod.php'>
    <input type=hidden name='edittag' value=1>
    <input type=hidden name='redir' value='<?php echo $redir ?>'>
    <input type=hidden name='editid' value='<?php echo $editid ?>'>
    <?php
    }
  ?>
  <table>
   <?php
   perrorLine(EUM_UPDATE_OK,'Новая информация принята и записана','green');
   perrorLine(EUM_NO_EDIT,'У вас нет права менять информацию для этого
                           пользователя');
   perrorLine(EUM_STORE_SQL,'Ошибка базы данных при обновлении записи',
                            'magenta');
   perrorLine(EUM_ONLINE_SQL,'Ошибка базы данных при записи last_update',
                             'magenta');
   perrorLine(EUM_LOGIN_ABSENT,'Ник не был введен');
   perrorLine(EUM_LOGIN_EXISTS,'Пользователь с таким ником уже существует');
   echo condEdit('Ник',$user->isEditable(),$user->getLogin(),'login',20,30);
   perrorLine(EUM_PASSWORD_LEN,'Пароль должен быть не менее 5 символов');
   perrorLine(EUM_PASSWORD_DIFF,'Опечатка при вводе пароля - введите еще раз');
   if($user->isEditable())
     {
     ?>
     <tr>
      <td>Пароль </td><td><input type=password name='password' size=20></td>
     </tr>
     <tr>
      <td>Пароль (еще раз) </td>
      <td><input type=password name='dup_password' size=20></td>
     </tr>
     <?php
     }
   echo condEdit('Имя',$user->isEditable(),$user->getName(),'name',30,30);
   echo condEdit('Еврейское имя',$user->isEditable(),$user->getJewishName(),
                 'jewish_name',30,30);
   echo condEdit('Фамилия',$user->isEditable(),$user->getSurname(),'surname',
                 30,30);
   perrorLine(EUM_GENDER,'Неизвестный пол');
   ?>
   <tr>
   <?php
   if(!$user->isEditable())
     {
     ?>
     <td><b>Пол:</b></td>
     <td><?php echo $user->isMan() ? 'Мужской' : 'Женский' ?></td>
     <?php
     }
   else
     {
     ?>
     <td>Пол</td>
     <td>
      <input type='radio' name='gender' value='mine' <?php
       echo ($user->isMan() ? ' checked ' : '')
      ?> >Мужской</input>&nbsp;
      <input type='radio' name='gender' value='femine' <?php
       echo ($user->isWoman() ? ' checked ' : '')
      ?> >Женский</input>
     </td>
     <?php
     }
   ?>
   </tr>
   <?php
   perrorLine(EUM_BIRTHDAY,'Совершенно идиотская дата рождения');
   ?>
   <tr>
    <?php
    if($user->isEditable())
      {
      ?>
      <td>Дата рождения</td>
      <td>
       <input type=text name='birth_day' size=2 maxlength=2
              value=<?php echo $user->getDayOfBirth() ?>>
       <select name='birth_month'>
        <?php
        foreach(array(1 => 'январь','февраль','март','апрель','май','июнь',
	              'июль','август','сентябрь','октябрь','ноябрь','декабрь')
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
      <td><b>Дата рождения:</b></td>
      <td><?php echo $user->getBirthday() ?></td>
      <?php
      }
    ?>
   </tr>
   <?php
   echo condCheckBoxLine($user->isEditable(),'migdal_student',
	                 $user->isMigdalStudent(),'Занимаюсь в "Мигдале"',
		         'Занимается в "Мигдале"');
   if($user->isEditable())
     {
     ?>
     <tr><td colspan=2>Коротко о себе</td></tr>
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
       <tr><td colspan=2><b>Коротко о себе:</b></td></tr>
       <tr><td colspan=2><?php echo $user->getInfo() ?></td></tr>
       <?php
       }
   echo condEditValue('E-mail',$user->isEditable(),$user->getEmailLink(),
                      $user->getEmail(),'email',30,70);
   if($user->isEditable())
     {
     echo condCheckBoxLine($user->isEditable(),'hide_email',
			   $user->isHideEmail(),
			   'Не показывать E-mail на сайте');
     echo condCheckBoxLine($user->isEditable(),'email_enabled',
			   $user->isEmailDisabled()==0,
			   'Разрешаю посылать почту на мой адрес');
     }
   if($user->isEmailDisabled()==2)
     {
     ?>
     <tr><td colspan=2><font color=red>
      Посылка почты на этот адрес временно приостановлена, поскольку адрес не
      работает
     </font></td></tr>
     <?php
     }
   echo condEditStatus('ICQ',$user->isEditable(),$user->getICQStatusImage(),
                       $user->getICQ(),'icq',15,15);
   if(!$user->isEditable())
     {
     ?>
     <tr>
      <td><b>Последний раз<br>заходил сюда:</b></td>
      <td><?php echo $user->getLastOnline() ?></td>
     </tr>
     <?php
     }
   echo condCheckBoxLine($userAdminUsers,'has_personal',
		         $user->isHasPersonal(),'Имеет персональную страницу');
   echo condCheckBoxLine($userAdminUsers,'admin_users',
			 $user->isAdminUsers(),'Администратор пользователей');
   echo condCheckBoxLine($userAdminUsers,'admin_topics',
			 $user->isAdminTopics(),'Администратор тем');
   echo condCheckBoxLine($userAdminUsers,'hidden',
			 $user->isHidden(),
			 'Не показывать в списке пользователей');
   echo condCheckBoxLine($userAdminUsers,'no_login',
		         $user->isNoLogin(),'Запретить вход');
   ?>
  </table>
  <?php
  if($user->isEditable())
    {
    ?>
    <input type=submit value='<?php echo $editid ? 'Обновить' 
                                                 : 'Зарегистрировать' ?>'>
    <input type=reset value='Очистить'>
    </form>
    <?php
    }
  ?>
</body>
</html>
<?php
dbClose();
?>
