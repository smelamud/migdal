<?php
# @(#) $Id$

require_once('lib/menu.php');

function displayTop($current)
{
$menu=new MenuIterator($current);
while($item=$menu->next())
     {
     $s='['.$item->getName().']';
     echo $item->isCurrent() ? "<b>$s</b>"
                             : '<a href="'.$item->getLink()."\">$s</a>";
     }
}
?>
