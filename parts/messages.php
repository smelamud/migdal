<?php
# @(#) $Id$

function displayMessages()
{
global $REQUEST_URI;

$requestURI=urlencode($REQUEST_URI);
?>
<table>
<tr><td>
 <a href='messageedit.php?grp=2&redir=<?php
  echo $requestURI
 ?>'>Добавить новость</a>
</td></tr>
</table>
<?php
}
?>
