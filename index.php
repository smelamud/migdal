<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/grps.php');
require_once('lib/session.php');

require_once('parts/top.php');
require_once('parts/messages.php');

settype($offset,'integer');

dbOpen();
session();
?>
<html>
<head>
 <title>Клуб Еврейского Студента</title>
</head>
<body bgcolor=white>
  <?php displayTop('index'); ?>
  <table width=100%>
   <tr><td><?php displayMessages(GRP_ANY,0,$userMsgPortion,$offset) ?></td></tr>
  </table>
</body>
</html>
<?php
dbClose();
?>
