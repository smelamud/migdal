<?php
# @(#) $Id$

require_once('lib/global.php');
require_once('lib/database.php');
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
  dbClose();
  ?>
 </h1></center>
</body>
</html>
