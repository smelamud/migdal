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

$title=getGrpTitle($grp);
$ident=getGrpIdent($grp);
$requestURI=urlencode($REQUEST_URI);

reloadParameter(!isset($ident),'grp',GRP_FORUMS);

dbOpen();
session();
?>
<html>
<head>
 <title>Клуб Еврейского Студента - <?php echo $title ?></title>
</head>
<body bgcolor=white>
  <?php displayTop($ident); ?>
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
       $topicId=$item->getId();
       echo '<p>';
       echo "<b><a href='messages.php?grp=$grp&redir=$requestURI&".
	                                      "topic_id=$topicId'>".
	      $item->getName().
	    '</a></b>&nbsp;';
       if($userAdminTopics)
         echo "<a href='topicedit.php?editid=$topicId&redir=$requestURI'>".
	       '[Изменить]</a>';
       echo '<br>'.$item->getDescription();
       }
  ?>
</body>
</html>
<?php
dbClose();
?>
