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
 <title>���� ���������� �������� - ����</title>
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
 perror(ET_NO_EDIT,'� ��� ��� ����� ������ ������ ���');
 perror(ET_STORE_SQL,'������ ���� ������ ��� ���������� ������','magenta');
 perror(ET_NAME_ABSENT,'�������� �� ���� �������');
 ?>
 <tr><td><table>
  <?php echo elementEdit('��������',$topic->getName(),'name',30,70); ?>
 </table></td></tr>
 <?php perror(ET_DESCRIPTION_ABSENT,'�������� �� ���� ������'); ?>
 <tr><td>��������</td></tr>
 <tr><td>
  <textarea name='description' rows=10 cols=50 wrap='virtual'><?php
   echo $topic->getDescription();
  ?></textarea>
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
</body>
</html>
<?php
dbClose();
?>
