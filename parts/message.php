<?php
# @(#) $Id$

function displayMessage($message)
{
?>
<table width=100%>
<tr><td>&nbsp;</td></tr>
<tr><td align=right><?php echo $message->getSentView() ?></td></tr>
<tr><td><b><?php echo $message->getSubject() ?></b></td></tr>
<tr><td><?php echo $message->getBody() ?></td></tr>
</table>
<?php
}
?>
