<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/topics.php');
require_once('lib/grps.php');

require_once('parts/top.php');
require_once('parts/grps.php');

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
  <center><h1>Темы</h1></center>
  <?php
  if($userAdminTopics)
    {
    ?>
    <p>
    <a href='topicedit.php?redir=<?php echo $requestURI ?>'>Добавить</a>
    &nbsp;&nbsp;
    <a href='<?php
     echo remakeURI($REQUEST_URI,
                    array(),
		    array('ignoregrp' => $ignoregrp ? 0 : 1));
    ?>'>Показать <?php echo $ignoregrp ? 'активные' : 'все' ?></a>
    <?php
    }
  $list=new TopicListIterator($ignoregrp ? GRP_ANY : $grp);
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
