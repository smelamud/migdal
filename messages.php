<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/topics.php');
require_once('lib/grps.php');

require_once('parts/top.php');
require_once('parts/grps.php');
require_once('parts/messages.php');

$title=getGrpTitle($grp);
$ident=getGrpIdent($grp);
$requestURI=urlencode($REQUEST_URI);

if(!isset($ident))
  {
  header('Location: '.remakeURI($REQUEST_URI,
                                array(),
				array('grp' => GRP_FORUMS)));
  exit;
  }

settype($topic_id,'integer');
?>
<html>
<head>
 <title>Клуб Еврейского Студента - <?php echo $title ?></title>
</head>
<body bgcolor=white>
  <?php
  dbOpen();
  displayTop($ident);
  $topic=getTopicNameById($topic_id);
  ?>
  <p>
  <a href='<?php echo $redir ?>'>&lt;&lt; Вернуться</a>
  <center><h1><?php echo $title ?> по теме &quot;<?php
   echo $topic->getName();
  ?>&quot;</h1></center>
  <table width=100%>
   <tr><td><?php displayMessages($grp,$topic_id); ?></td></tr>
  </table>
  <?php
  dbClose();
  ?>
</body>
</html>
