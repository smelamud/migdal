<?php
# @(#) $Id$

require_once('lib/messages.php');

require_once('parts/grps.php');
require_once('parts/message.php');

function displayMessages($grp)
{
global $REQUEST_URI;

$requestURI=urlencode($REQUEST_URI);
$title=getGrpItemTitle($grp);

if(isset($title))
  {
  ?>
  <a href='messageedit.php?grp=<?php echo $grp ?>&redir=<?php
   echo $requestURI
  ?>'>Добавить <?php echo $title ?></a>
  <?php
  }
?>
<table width=100%>
<?php
$list=new MessageListIterator($grp);
while($item=$list->next())
     {
     ?>
     <tr><td>
     <?php
     displayMessage($item);
     ?>
     </td></tr>
     <?php
     }
?>
</table>
<?php
}
?>
