<?php
# @(#) $Id$

require_once('lib/grps.php');

function displayForum($forum)
{
global $REQUEST_URI,$userModerator;

$requestURI=urlencode($REQUEST_URI);

if($message->isRebe())
  {
  $startFont='<font color=red>';
  $endFont='</font>';
  }
else
  {
  $startFont='';
  $endFont='';
  }
?>
<table width=100%>
<tr>
 <td width=40%>
 <?php
 echo $startFont;
 echo $forum->getLoginLink();
 if($forum->isSenderVisible())
   echo "(<a href='useredit.php?editid=".$forum->getSenderId().
			      "&redir=$requestURI'>подробнее</a>)";
 echo $endFont;
 ?><br><?php
 echo $forum->sentView();
 ?><br><?php
 if($message->isEditable())
   {
   echo '<a href="messageedit.php?editid='.$forum->getId().
				'&grp='.GRP_FORUMS.
				"&redir=$requestURI\">[Изменить]</a>";
   if($userModerator)
     echo '<a href="actions/moderate.php?editid='.$forum->getId().
				       '&hide='.($forum->isDisabled() ? 0 : 1).
				       "&redir=$requestURI\">[".
	   ($forum->isDisabled() ? 'Разрешить' : 'Запретить').' показ]</a>';
   }
 ?>
 </td>
 <td width=60%>
 <?php echo $startFont.$forum->getBody().$endFont ?>
 </td>
</tr>
<tr>
<td>
<?php
if($message->isEditable())
  {
  echo '<a href="messageedit.php?editid='.$message->getId().
                               '&grp='.$message->getGrp().
			       "&redir=$requestURI\">[Изменить]</a>";
  if($userModerator)
    echo '<a href="actions/moderate.php?editid='.$message->getId().
                                      '&hide='.($message->isDisabled() ? 0 : 1).
				      "&redir=$requestURI\">[".
	  ($message->isDisabled() ? 'Разрешить' : 'Запретить').' показ]</a>';
  }
?>
</td>
</tr>
</table>
<?php
}
?>
