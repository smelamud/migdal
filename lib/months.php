<?php
# @(#) $Id$

$months=array(1 => 'января','февраля','марта','апреля','мая','июня',
              'июля','августа','сентября','октября','ноября','декабря');

function getRussianMonth($month)
{
global $months;

return $months[$month];
}
?>
