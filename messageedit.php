<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/grps.php');
require_once('lib/messages.php');
require_once('lib/topics.php');
require_once('lib/utils.php');

require_once('parts/top.php');
require_once('parts/grps.php');
require_once('parts/utils.php');

$title=getGrpOneTitle($grp);
$ident=getGrpIdent($grp);
$requestURI=urlencode($REQUEST_URI);

reloadParameter(!isset($ident),'grp',GRP_FORUMS);
settype($editid,'integer');
settype($up,'integer');

dbOpen();
session();
?>
<html>
<head>
 <title>Клуб Еврейского Студента - <?php echo $title ?></title>
</head>
<body bgcolor=white>
 <?php displayTop($ident); ?>
 <p>
 <?php
 $message=getMessageById($editid,$grp);
 $message->setUpValue($up);
 $message->setup($HTTP_GET_VARS);
 ?>
 <form method=post enctype='multipart/form-data'
       action='actions/messagemod.php'>
 <input type=hidden name='edittag' value=1>
 <input type=hidden name='redir' value='<?php echo $redir ?>'>
 <input type=hidden name='editid' value='<?php echo $editid ?>'>
 <input type=hidden name='grp' value='<?php echo $message->getGrp() ?>'>
 <input type=hidden name='up' value='<?php echo $message->getUpValue() ?>'>
 <input type=hidden name='personal_id' value='<?php
  echo $message->getPersonalId();
 ?>'>
 <table>
 <?php
 perror(EM_NO_SEND,'Посылать сообщения могут только зарегистрированные
                    пользователи');
 perror(EM_NO_EDIT,'У вас нет права редактировать это сообщение');
 perror(EM_STORE_SQL,'Ошибка базы данных при обновлении записи','magenta');
 perror(EM_FORUM_ANSWER,'Отвечать можно только в форуме');
 perror(EM_NO_UP,'Сообщение, на которое вы пытаетесь ответить, еще не написано');
 perror(EM_NO_PERSONAL,'У этого пользователя нет персональной страницы');
 perror(EM_TOPIC_ABSENT,'Тема должна быть выбрана');
 perror(EM_NO_TOPIC,'Такой темы не существует');
 ?>
 <tr><td><table>
  <tr>
   <td>Тема</td>
   <td>
    <select name='topic_id'>
     <?php
     echo elementOption('-- Не выбрана --',0,$message->getTopicId());
     $list=new TopicNamesIterator($grp);
     while($item=$list->next())
	  echo elementOption($item->getName(),$item->getId(),
	                     $message->getTopicId());
     ?>
    </select>
   </td>
  </tr>
  <?php
  if($message->hasSubject())
    {
    perror(EM_SUBJECT_ABSENT,'Заголовок пуст');
    echo elementEdit('Заголовок',$message->getSubject(),'subject',42,250);
    }
  if($message->hasImage())
    {
    perror(EM_IMAGE_ABSENT,'Нужно указать картинку для отображения');
    perror(EM_NO_IMAGE,'Указанной картинки не существует');
    perror(EM_UNKNOWN_IMAGE,'Неизвестный формат картинки');
    perror(EM_IMAGE_LARGE,'Картинка слишком велика (больше 2M)');
    perror(EM_IMAGE_SQL,'Ошибка базы данных при загрузке картинки','magenta');
    if($message->getImageSet()==0)
      {
      ?>
      <tr>
       <td>Картинка</td>
       <td><input type=file name='image' size=35></td>
      </tr>
      <?php
      }
    else
      {
      $image=getImageNameBySet($message->getImageSet());
      ?>
      <tr><td colspan=2><table>
       <tr><td>Картинка</td></tr>
       <tr>
	<td>
	 <input type=radio name='image_set' value=<?php
	  echo $message->getImageSet();
	 ?> checked >
	 &nbsp;<?php echo $image->getFilename() ?>&nbsp;<i>(уже загружена)</i>
	</td>
       </tr>
       <tr><td>
	<input type=radio name='image_set' value=0>&nbsp;Другая&nbsp;
	<input type=file name='image' size=35>
       </td></tr>
      </table></td></tr>
      <?php
      }
    }
  ?>
 </table></td></tr>
 <?php
 perror(EM_BODY_ABSENT,'Напишите хотя бы пару строчек в теле сообщения');
 ?>
 <tr><td>Текст</td></tr>
 <tr><td>
  <textarea name='body' rows=10 cols=50 wrap='virtual'><?php
   echo $message->getBody();
  ?></textarea>
 </td></tr>
 <?php
 echo elementCheckBox('hidden',$message->isHidden(),'Не показывать');
 if($userModerator)
   echo elementCheckBox('disabled',$message->isDisabled(),'Запретить показ');
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
