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
  $requestURI=urlencode($REQUEST_URI);
  ?>
  <center><h1>Темы</h1></center>
  <?php
  if($userAdminTopics)
    {
    ?>
    <p>
    <a href='topicedit.php?redir=<?php echo $requestURI ?>'>Добавить</a>
    <?php
    }
  $list=new TopicListIterator();
  while($item=$list->next())
       {
       echo '<p>';
       echo '<b>'.$item->getName().'</b>&nbsp;';
       if($userAdminTopics)
         echo "<a href='topicedit.php?editid=".$item->getId()
	                            ."&redir=$requestURI'>[Изменить]</a>";
       echo '<br>'.$item->getDescription();
       }
  dbClose();
  ?>
</body>
</html>
