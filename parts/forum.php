<?php
# @(#) $Id$

require_once('lib/grps.php');

function displayForum($forum)
{
global $REQUEST_URI,$userModerator;

$requestURI=urlencode($REQUEST_URI);

if($forum->isRebe())
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
 <td width=25%>
 <?php
 echo $startFont;
 echo $forum->getLoginLink();
 if($forum->isSenderVisible())
   echo "(<a href='useredit.php?editid=".$forum->getSenderId().
			      "&redir=$requestURI'>подробнее</a>)";
 echo $endFont;
 ?><br><?php
 echo $forum->getSentView();
 ?><br><?php
 if($forum->isEditable())
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
 <td width=75% align=left valign=top>
 <?php echo $startFont.$forum->getBody().$endFont ?>
 </td>
</tr>
</table>
<?php
}
?>
