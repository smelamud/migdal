<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/topics.php');
require_once('lib/errors.php');

require_once('top.php');

function elementEdit($title,$value,$name,$size,$length)
{
return "<tr>
         <td>$title </td>
         <td><input type=text name='$name' value='$value' size=$size
	            maxlength=$length></td>
	</tr>";
}

function elementCheckBox($name,$value,$title)
{
return "<tr><td>
         <input type=checkbox name=$name value=1 ".($value ? 'checked' : '')
                                                 ."> $title
	</td></tr>";
}

function perror($code,$message,$color='red')
{
global $err;

if($err==$code)
  echo "<tr><td><a name='error'>
         <font color='$color'>$message</font>
	</td></tr>";
}
?>
<html>
<head>
 <title>Клуб Еврейского Студента - Тема</title>
</head>
<body bgcolor=white>
 <?php
 dbOpen();
 displayTop('topics');
 ?>
 <p>
 <a href='<?php echo $redir ?>'>&lt;&lt; Вернуться</a>
 <p>
 <?php
 $topic=getTopicById($editid);
 $topic->setup($HTTP_GET_VARS);
 ?>
 <form method=post action='actions/topicmod.php'>
 <input type=hidden name='edittag' value=0>
 <input type=hidden name='redir' value='<?php echo $redir ?>'>
 <input type=hidden name='editid' value='<?php echo $editid ?>'>
 <table>
 <?php
 perror(ET_NO_EDIT,'У вас нет права менять список тем');
 perror(ET_STORE_SQL,'Ошибка базы данных при обновлении записи','magenta');
 perror(ET_NAME_ABSENT,'Название не было введено');
 ?>
 <tr></td><table>
  <?php echo elementEdit('Название',$topic->getName(),'name',30,70); ?>
 </table></td></tr>
 <?php perror(ET_DESCRIPTION_ABSENT,'Описание не было задано'); ?>
 <tr><td>Описание</td></tr>
 <tr><td>
  <textarea name='description' rows=10 cols=50 wrap='virtual'><?php
   echo $topic->getDescription()
  ?></textarea>
 </td></tr>
 <?php
 echo elementCheckBox('hidden',$topic->isHidden(),'Не показывать в списке');
 echo elementCheckBox('news',$topic->isNews(),'Тема для новостей');
 echo elementCheckBox('forums',$topic->isForums(),'Тема для форума');
 echo elementCheckBox('gallery',$topic->isGallery(),'Тема для галереи');
 ?>
 </table>
 <input type=submit value='<?php echo $editid ? 'Изменить' : 'Добавить' ?>'>
 <input type=reset value='Очистить'>
 </form>
 <?php
 dbClose();
 ?>
</body>
</html>
