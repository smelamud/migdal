<?php
# @(#) $Id$

require_once('lib/global.php');
require_once('lib/database.php');
require_once('lib/menu.php');
?>
<html>
<head>
 <title>Migdal</title>
</head>
<body>
 <center><h1>
  <?php
  dbOpen();
  include('conf/migdal.conf');
  echo $hello;
  ?>
 </h1></center>
 <p>
  <?php
  $menu=new MenuIterator();
  while($item=$menu->next())
       {
       echo $item->name;
       echo '<br>';
       }
  dbClose();
  ?>
</body>
</html>
