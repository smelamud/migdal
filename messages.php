<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/topics.php');
require_once('lib/grps.php');
require_once('lib/utils.php');
require_once('lib/session.php');

require_once('parts/top.php');
require_once('parts/grps.php');
require_once('parts/messages.php');

$title=getGrpTitle($grp);
$ident=getGrpIdent($grp);
$requestURI=urlencode($REQUEST_URI);

reloadParameter(!isset($ident),'grp',GRP_FORUMS);
settype($topic_id,'integer');
settype($offset,'integer');
settype($limit,'integer');
$limit=$limit==0 ? 10 : $limit;

dbOpen();
session();
?>
<html>
<head>
 <title>Клуб Еврейского Студента - <?php echo $title ?></title>
</head>
<body bgcolor=white>
  <?php
  displayTop($ident);
  $topic=getTopicNameById($topic_id);
  ?>
  <center><h1><?php echo $title ?> по теме &quot;<?php
   echo $topic->getName();
  ?>&quot;</h1></center>
  <table width=100%>
   <tr><td><?php displayMessages($grp,$topic_id,$limit,$offset); ?></td></tr>
  </table>
</body>
</html>
<?php
dbClose();
?>
