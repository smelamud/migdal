<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/grps.php');

require_once('parts/top.php');
require_once('parts/grps.php');

$title=getGrpOneTitle($grp);
$ident=getGrpIdent($grp);
$requestURI=urlencode($REQUEST_URI);

if(!isset($ident))
  {
  header('Location: '.remakeURI($REQUEST_URI,
                                array(),
				array('grp' => GRP_FORUMS)));
  exit;
  }
?>
<html>
<head>
 <title>Клуб Еврейского Студента - <?php echo $title ?></title>
</head>
<body bgcolor=white>
 <?php
 dbOpen();
 displayTop($ident);
 ?>
 <p>
 <a href='<?php echo $redir ?>'>&lt;&lt; Вернуться</a>
 <?php
 dbClose();
 ?>
</body>
</html>
