<?php
# @(#) $Id$

require_once('lib/global.php');
require_once('lib/database.php');
require_once('lib/menu.php');
?>
<html>
<head>
 <title>Клуб Еврейского Студента</title>
</head>
<body bgcolor=white>
  <?php
  dbOpen();
  $menu=new MenuIterator();
  while($item=$menu->next())
       echo '<a href="'.$item->getLink().'">['.$item->getName().']</a>';
  dbClose();
  ?>
</body>
</html>
