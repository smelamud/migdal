<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/topics.php');
require_once('lib/errors.php');

require_once('parts/top.php');
require_once('parts/utils.php');

settype($editid,'integer');

dbOpen();
session($sessionid);
?>
<html>
<head>
 <title>Клуб Еврейского Студента - Тема</title>
</head>
<body bgcolor=white>
 <?php displayTop('topics'); ?>
 <p>
 <?php
 $topic=getTopicById($editid);
 $topic->setup($HTTP_GET_VARS);
 ?>
 <form method=post action='actions/topicmod.php'>
 <input type=hidden name='edittag' value=1>
 <input type=hidden name='redir' value='<?php echo $redir ?>'>
 <input type=hidden name='editid' value='<?php echo $editid ?>'>
 <table>
 <?php
 perror(ET_NO_EDIT,'У вас нет права менять список тем');
 perror(ET_STORE_SQL,'Ошибка базы данных при обновлении записи','magenta');
 perror(ET_NAME_ABSENT,'Название не было введено');
 ?>
 <tr><td><table>
  <?php echo elementEdit('Название',$topic->getName(),'name',30,70); ?>
 </table></td></tr>
 <?php perror(ET_DESCRIPTION_ABSENT,'Описание не было задано'); ?>
 <tr><td>Описание</td></tr>
 <tr><td>
  <textarea name='description' rows=10 cols=50 wrap='virtual'><?php
   echo $topic->getDescription();
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
</body>
</html>
<?php
dbClose();
?>
