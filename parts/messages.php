<?php
# @(#) $Id$

require_once('lib/messages.php');
require_once('lib/errors.php');

require_once('parts/grps.php');
require_once('parts/message.php');
require_once('parts/utils.php');

function displayMessages($grp,$topic=0,$limit=10,$offset=0)
{
global $REQUEST_URI,$userId;

$requestURI=urlencode($REQUEST_URI);
$title=getGrpItemTitle($grp);

if(isset($title) && $userId>0)
  echo "<a href='messageedit.php?topic_id=$topic&grp=$grp&redir=$requestURI".
       "'>Добавить $title</a>";
$list=new MessageListIterator($grp,$topic,$limit,$offset);
echo batcher($limit);
echo navigator($list);
?>
<table width=100%>
<?php
perror(EMH_NO_MESSAGE,'Такого сообщения не существует');
perror(EMH_NO_MODERATE,'Вы не модератор');
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
echo navigator($list);
}
?>
