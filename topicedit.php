<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/topics.php');

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

?>
<html>
<head>
 <title>���� ���������� �������� - ����</title>
</head>
<body bgcolor=white>
 <?php
 dbOpen();
 displayTop('topics');
 ?>
 <p>
 <a href='<?php echo $redir ?>'>&lt;&lt; ���������</a>
 <p>
 <?php
 $topic=getTopicById($editid);
 $topic->setup($HTTP_GET_VARS);
 ?>
 <form method=post action='actions/topicmod.php'>
 <input type=hidden name='redir' value='<?php echo $redir ?>'>
 <input type=hidden name='editid' value='<?php echo $editid ?>'>
 <table>
 <tr></td><table>
  <?php echo elementEdit('��������',$topic->getName(),'name',30,70); ?>
 </table></td></tr>
 <tr><td>��������</td></tr>
 <tr><td>
  <textarea name='description' rows=10 cols=50 wrap='virtual'>
   <?php echo $topic->getDescription() ?>
  </textarea>
 </td></tr>
 <?php
 echo elementCheckBox('hidden',$topic->isHidden(),'�� ���������� � ������');
 echo elementCheckBox('news',$topic->isNews(),'���� ��� ��������');
 echo elementCheckBox('forums',$topic->isForums(),'���� ��� ������');
 echo elementCheckBox('gallery',$topic->isGallery(),'���� ��� �������');
 ?>
 </table>
 <input type=submit value='<?php echo $editid ? '��������' : '��������' ?>'>
 <input type=reset value='��������'>
 </form>
 <?php
 dbClose();
 ?>
</body>
</html>
