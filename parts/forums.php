<?php
# @(#) $Id$

require_once('lib/messages.php');
require_once('lib/errors.php');
require_once('lib/grps.php');

require_once('parts/forum.php');
require_once('parts/utils.php');

function displayForums($up,$limit=10,$offset=0)
{
global $REQUEST_URI,$userId;

$requestURI=urlencode($REQUEST_URI);

if($userId>0)
  echo "<a href='messageedit.php?grp=".GRP_FORUMS.
                "&up=$up&redir=$requestURI'>Добавить сообщение</a>";
$list=new ForumListIterator($up,$limit,$offset);
echo navigator($list);
echo batcher($limit);
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
     displayForum($item);
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
