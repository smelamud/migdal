<?php
# @(#) $Id$

define('SORT_NONE',0);
define('SORT_SENT',1);
define('SORT_NAME',2);
define('SORT_ACTIVITY',3);
define('SORT_READ',4);
define('SORT_INDEX0',5);
define('SORT_INDEX1',6);
define('SORT_RINDEX1',7);
define('SORT_RATING',8);
define('SORT_ORDER',255);
define('SORT_PRIORITY',256);

define('SORT_PSENT',SORT_PRIORITY|SORT_SENT);
define('SORT_PNAME',SORT_PRIORITY|SORT_NAME);
define('SORT_PACTIVITY',SORT_PRIORITY|SORT_ACTIVITY);

function getOrderBy($sort,$names,$priority='priority')
{
$order=$sort & SORT_ORDER;
return $sort==SORT_NONE ? '' : 'order by '.
              (($sort & SORT_PRIORITY)!=0 ? "$priority," : '').$names[$order];
}
?>
