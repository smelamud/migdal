<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/topics.php');

require_once('top.php');
?>
<html>
<head>
 <title>Клуб Еврейского Студента - Темы</title>
</head>
<body bgcolor=white>
  <?php
  dbOpen();
  displayTop('topics');
  ?>
  <center><h1>Темы</h1></center>
  <?php
  if($userAdminTopics)
    {
    ?>
    <p>
    <a href='topicedit.php'>Добавить</a>
    <?php
    }
  $list=new TopicListIterator();
  while($item=$list->next())
       {
       echo '<p>';
       if($userAdminTopics)
         echo '<a href=\'topicedit.php?id='.$item->getId().'>Изменить</a><br>';
       echo '<b>'.$item->getName().'</b><br>';
       echo $item->getDescription();
       }
  dbClose();
  ?>
</body>
</html>
