<?php
# @(#) $Id$

function displayBacktrace($redir)
{
if(isset($redir) && $redir!='')
  echo "<a href='$redir'>&lt;&lt; Вернуться</a>";
}
?>
