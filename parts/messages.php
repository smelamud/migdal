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
  {
  ?>
  <a href='messageedit.php?grp=<?php echo $grp ?>&redir=<?php
   echo $requestURI
  ?>'>Добавить <?php echo $title ?></a>
  <?php
  }
$list=new MessageListIterator($grp,$topic,$limit,$offset);
echo navigator($list);
?>
<table width=100%><tr><td align=right><table><form>
<tr valign=center>
<td>Показывать по&nbsp;</td>
<td><select>
<?php
$shows=array(10,15,20,25,30,35,40);
$shows[]=$limit;
sort($shows);
$shows=array_unique($shows);
foreach($shows as $val)
       echo "<option label=$val value=$val ".
             ($val==$limit ? 'selected' : '').
	    ' >';
?>
</select></td>
<td>сообщений&nbsp;</td>
<td><input type=submit value='Изменить'></td>
</form></table></td></tr></table>
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
