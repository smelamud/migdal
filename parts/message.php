<?php
# @(#) $Id$

require_once('lib/images.php');

function displayMessage($message,$grp,$topic)
{
global $REQUEST_URI,$userModerator;

$requestURI=urlencode($REQUEST_URI);
?>
<table width=100%>
<tr><td>&nbsp;</td></tr>
<tr>
 <?php
 if($topic==0)
   echo "<td>
          <a href='messages.php?redir=$requestURI&grp=$grp&topic_id="
                  .$message->getTopicId()."'>"
	  .$message->getTopicName().
	 '</a>
	 </td>';
 else
   echo '<td>&nbsp;</td>';
 ?>
 <td align=right><?php echo $message->getSentView() ?></td>
</tr>
<tr>
 <td><b><?php echo $message->getSubject() ?></b></td>
 <td align=right>
  <?php
  echo $message->getLoginLink();
  if($message->isSenderVisible())
    echo "(<a href='useredit.php?editid=".$message->getSenderId().
			       "&redir=$requestURI'>подробнее</a>)";
  ?>
 </td>
</tr>
<tr><td colspan=2>
 <?php
 if($message->getImageSet()!=0)
   {
   $id=$message->getImageId();
   echo "<table width=100%><tr>
          <td><a href='lib/image.php?id=$id&size=large'>"
	      .getImageTagById($id).
	 '</a></td>
	  <td>'.$message->getBody().'</td>
	 </tr></table>';
   }
 else
   echo $message->getBody();
 ?>
</td></tr>
<?php
if($message->isEditable())
  {
  echo '<tr><td>';
  echo '<a href="messageedit.php?editid='.$message->getId().
                               '&grp='.$message->getGrp().
			       "&redir=$requestURI\">[Изменить]</a>";
  if($userModerator)
    echo '<a href="actions/moderate.php?editid='.$message->getId().
                                      '&hide='.($message->isDisabled() ? 0 : 1).
				      "&redir=$requestURI\">[".
	  ($message->isDisabled() ? 'Разрешить' : 'Запретить').' показ]</a>';
  echo '</td></tr>';
  }
?>
</table>
<?php
}
?>
