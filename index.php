<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/grps.php');

require_once('parts/top.php');
require_once('parts/messages.php');

settype($offset,'integer');
settype($limit,'integer');
$limit=$limit==0 ? 10 : $limit;
?>
<html>
<head>
 <title>Клуб Еврейского Студента</title>
</head>
<body bgcolor=white>
  <?php
  dbOpen();
  displayTop('index');
  ?>
  <table width=100%>
   <tr><td><?php displayMessages(GRP_ANY,0,$limit,$offset) ?></td></tr>
  </table>
  <?php
  dbClose();
  ?>
</body>
</html>
