<?php
# @(#) $Id$

define('SORT_NONE',0);
define('SORT_SENT',1);
define('SORT_NAME',2);
define('SORT_ORDER',255);
define('SORT_PRIORITY',256);

define('SORT_PSENT',SORT_PRIORITY|SORT_SENT);
define('SORT_PNAME',SORT_PRIORITY|SORT_NAME);

function getOrderBy($sort,$names,$priority='priority')
{
$order=$sort & SORT_ORDER;
return $sort==SORT_NONE ? '' : 'order by '.
              (($sort & SORT_PRIORITY)!=0 ? "$priority," : '').$names[$order];
}
?>
