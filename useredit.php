<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/users.php');
require_once('lib/errors.php');

require_once('top.php');

function condEdit($title,$edit,$value,$name,$size,$length)
{
return !$edit ?
       $value!='' ? "<td><b>$title:</b></td><td>$value</td>" : '' :
       "<td>$title </td><td><input type=text name='$name' value='$value'
                            size=$size maxlength=$length></td>";
}

function condEditStatus($title,$edit,$status,$value,$name,$size,$length)
{
return !$edit ?
       $value!='' ? "<td><b>$title:</b></td><td>$status&nbsp;$value</td>" 
                  : '' :
       "<td>$title </td><td>$status&nbsp;<input type=text name='$name'
                     value='$value' size=$size maxlength=$length></td>";
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

function perror($code,$message,$color='red')
{
global $user,$err;

if($user->isEditable() && $err==$code)
  echo "<tr><td colspan=4><a name='error'>
         <font color='$color'>$message</font>
	</td></tr>";
}
?>
<html>
<head>
 <title>Клуб Еврейского Студента - Информация о пользователе</title>
</head>
<body bgcolor=white>
  <?php
  dbOpen();
  displayTop('users',$editid ? '' : 'no_new');
  ?>
  <p>
  <a href='<?php echo $redir ?>'>&lt;&lt; Вернуться</a>
  <p>
  <?php
  $user=getUserById($editid);
  $user->setup($HTTP_GET_VARS);
  if($user->isEditable())
    {
    ?>
    <center><h1>Введите информацию о себе</h1></center>
    <form method=post action='actions/usermod.php'>
    <input type=hidden name='edittag' value=0>
    <input type=hidden name='redir' value='<?php echo $redir ?>'>
    <input type=hidden name='editid' value='<?php echo $editid ?>'>
    <?php
    }
  ?>
  <table>
   <?php
   perror(EUM_UPDATE_OK,'Новая информация принята и записана','green');
   perror(EUM_NO_EDIT,'У вас нет права менять информацию для этого пользователя');
   perror(EUM_STORE_SQL,'Ошибка базы данных при обновлении записи','magenta');
   perror(EUM_ONLINE_SQL,'Ошибка базы данных при записи last_update','magenta');
   ?>
   <tr>
    <?php
    perror(EUM_LOGIN_ABSENT,'Ник не был введен');
    perror(EUM_LOGIN_EXISTS,'Пользователь с таким ником уже существует');
    echo condEdit('Ник',$user->isEditable(),$user->getLogin(),'login',20,30);
    ?>
   </tr>
   <?php
   if($user->isEditable())
     {
     perror(EUM_PASSWORD_LEN,'Пароль должен быть не менее 5 символов');
     perror(EUM_PASSWORD_DIFF,'Опечатка при вводе пароля - введите еще раз');
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
     ?>
   <tr>
    <?php
    echo condEdit('Имя',$user->isEditable(),$user->getName(),'name',30,30);
    echo condEdit('Еврейское имя',$user->isEditable(),$user->getJewishName(),
                  'jewish_name',30,30);
    ?>
   </tr>
   <tr>
    <?php
    echo condEdit('Фамилия',$user->isEditable(),$user->getSurname(),'surname',
                  30,30);
    ?>
   </tr>
   <?php
   perror(EUM_BIRTHDAY,'Совершенно идиотская дата рождения');
   ?>
   <tr>
    <?php
    if($user->isEditable())
      {
      ?>
      <td>Дата рождения</td>
      <td colspan=3>
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
   <tr>
    <td colspan=2>
    <?php
     echo condCheckBox($user->isEditable(),'migdal_student',
                       $user->isMigdalStudent(),'Занимаюсь в "Мигдале"',
		       'Занимается в "Мигдале"');
    ?>
    </td>
   </tr>
   <?php
   if($user->isEditable())
     {
     ?>
     <tr><td colspan=4>Коротко о себе</td></tr>
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
       <tr><td colspan=2><b>Коротко о себе:</b></td></tr>
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
	echo condCheckBox($user->isEditable(),'hide_email',
	                  $user->isHideEmail(),
			  'Не показывать E-mail на сайте');
       ?>
       </td>
      </tr>
      <tr>
       <td colspan=2>
       <?php
	echo condCheckBox($user->isEditable(),'email_enabled',
	                  $user->isEmailDisabled()==0,
			  'Разрешаю посылать почту на мой адрес');
       ?>
       </td>
      </tr>
      <?php
      }
    if($user->isEmailDisabled()==2)
      {
      ?>
      <tr><td colspan=4><font color=red>
       Посылка почты на этот адрес временно приостановлена, поскольку адрес не
       работает
      </font></td></tr>
      <?php
      }
   ?>
   <tr>
    <?php
    echo condEditStatus('ICQ',$user->isEditable(),$user->getICQStatusImage(),
                        $user->getICQ(),'icq',15,15);
    ?>
   </tr>
   <?php
   if(!$user->isEditable())
     {
     ?>
     <tr>
      <td><b>Последний раз<br>заходил сюда:</b></td>
      <td><?php echo $user->getLastOnline() ?></td>
     </tr>
     <?php
     }
   ?>
   <tr>
    <td colspan=2>
    <?php
     echo condCheckBox($userAdminUsers,'admin_users',
		       $user->isAdminUsers(),'Администратор пользователей');
    ?>
    </td>
   </tr>
   <tr>
    <td colspan=2>
    <?php
     echo condCheckBox($userAdminUsers,'admin_topics',
		       $user->isAdminTopics(),'Администратор тем');
    ?>
    </td>
   </tr>
   <tr>
    <td colspan=2>
    <?php
     echo condCheckBox($userAdminUsers,'hidden',
		       $user->isHidden(),'Не показывать в списке пользователей');
    ?>
    </td>
   </tr>
   <tr>
    <td colspan=2>
    <?php
     echo condCheckBox($userAdminUsers,'no_login',
		       $user->isNoLogin(),'Запретить вход');
    ?>
    </td>
   </tr>
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
  dbClose();
  ?>
</body>
</html>
