<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/grps.php');
require_once('lib/messages.php');
require_once('lib/topics.php');

require_once('parts/top.php');
require_once('parts/grps.php');

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

function elementOption($label,$value,$peervalue)
{
return "<option label='$label' value=$value".
       ($value==$peervalue ? ' selected ' : '').
       '>';
}

function perror($code,$message,$color='red')
{
global $err;

if($err==$code)
  echo "<tr><td><a name='error'>
         <font color='$color'>$message</font>
	</td></tr>";
}

$title=getGrpOneTitle($grp);
$ident=getGrpIdent($grp);
$requestURI=urlencode($REQUEST_URI);

if(!isset($ident))
  {
  header('Location: '.remakeURI($REQUEST_URI,
                                array(),
				array('grp' => GRP_FORUMS)));
  exit;
  }
?>
<html>
<head>
 <title>Клуб Еврейского Студента - <?php echo $title ?></title>
</head>
<body bgcolor=white>
 <?php
 dbOpen();
 displayTop($ident);
 ?>
 <p>
 <a href='<?php echo $redir ?>'>&lt;&lt; Вернуться</a>
 <p>
 <?php
 $message=getMessageById($editid,$grp);
 $message->setup($HTTP_GET_VARS);
 ?>
 <form method=post action='actions/messagemod.php'>
 <input type=hidden name='edittag' value=0>
 <input type=hidden name='redir' value='<?php echo $redir ?>'>
 <input type=hidden name='editid' value='<?php echo $editid ?>'>
 <input type=hidden name='up' value='<?php echo $up ?>'>
 <table>
 <tr><td><table>
  <tr>
   <td>Тема</td>
   <td>
    <select name='topic_id'>
     <?php
     echo elementOption('-- Не выбрана --',0,$topic_id);
     $list=new TopicNamesIterator($grp);
     while($item=$list->next())
	  echo elementOption($item->getName(),$item->getId(),$topic_id);
     ?>
    </select>
   </td>
  </tr>
  <?php
  echo elementEdit('Заголовок',$message->getSubject(),'subject',30,250);
  ?>
 </table></td></tr>
 <tr><td>Текст</td></tr>
 <tr><td>
  <textarea name='body' rows=10 cols=50 wrap='virtual'><?php
   echo $message->getBody();
  ?></textarea>
 </td></tr>
 <?php
 echo elementCheckBox('hidden',$message->isHidden(),'Не показывать');
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
