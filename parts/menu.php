<?php
# @(#) $Id$

require_once('lib/menu.php');

function displayMenu($current)
{
$menu=new MenuIterator($current);
while($item=$menu->next())
     {
     $s='['.$item->getName().']';
     echo '<a href="'.$item->getLink().'">'.
            ($item->isCurrent() ? "<b>$s</b>" : $s).
	  '</a>';
     }
}
?>
