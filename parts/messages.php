<?php
# @(#) $Id$

require_once('lib/messages.php');
require_once('lib/errors.php');

require_once('parts/grps.php');
require_once('parts/message.php');

function perror($code,$message,$color='red')
{
global $err;

if($err==$code)
  echo "<tr><td><a name='error'>
         <font color='$color'>$message</font>
	</td></tr>";
}

function displayMessages($grp,$topic=0)
{
global $REQUEST_URI,$userId;

$requestURI=urlencode($REQUEST_URI);
$title=getGrpItemTitle($grp);

if(isset($title) && $userId>0)
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
perror(EMH_FAILED,'Такого сообщения не существует или у Вас нет прав на его
                   модерирование');
$list=new MessageListIterator($grp,$topic);
while($item=$list->next())
     {
     ?>
     <tr><td>
     <?php
     displayMessage($item,$grp,$topic);
     ?>
     </td></tr>
     <?php
     }
?>
</table>
<?php
}
?>
