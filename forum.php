<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/messages.php');
require_once('lib/grps.php');

require_once('parts/top.php');
require_once('parts/message.php');
require_once('parts/grps.php');
require_once('parts/forums.php');

$requestURI=urlencode($REQUEST_URI);

settype($msgid,'integer');

dbOpen();
session();
?>
<html>
<head>
 <title>Клуб Еврейского Студента - Обсуждение</title>
</head>
<body bgcolor=white>
 <?php displayTop('forums'); ?>
 <p>
 <?php
 $message=getFullMessageById($msgid,GRP_FORUMS);
 displayMessage($message,GRP_FORUMS,0,false);
 ?>
<center><h1>Обсуждение</h1></center>
<?php
displayForums($msgid,$userForumPortion,0);
?>
</body>
</html>
<?php
dbClose();
?>
