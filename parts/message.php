<?php
# @(#) $Id$

require_once('lib/images.php');

function displayMessage($message)
{
?>
<table width=100%>
<tr><td>&nbsp;</td></tr>
<tr><td align=right><?php echo $message->getSentView() ?></td></tr>
<tr><td><b><?php echo $message->getSubject() ?></b></td></tr>
<tr><td>
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
</table>
<?php
}
?>
