<?php
# @(#) $Id$

define('SORT_NONE',0);
define('SORT_SENT',1);
define('SORT_NAME',2);
define('SORT_ACTIVITY',3);
define('SORT_CTR',4);
define('SORT_INDEX0',5);
define('SORT_INDEX1',6);
define('SORT_RINDEX1',7);
define('SORT_RATING',8);
define('SORT_JEWISH_NAME',9);
define('SORT_SURNAME',10);
define('SORT_LOGIN',11);
define('SORT_URL_DOMAIN',12);
define('SORT_RINDEX0',13);
define('SORT_TOPIC_INDEX0_INDEX0',14);
define('SORT_RSENT',15);
define('SORT_ORDER',255);
define('SORT_PRIORITY',256);

define('SORT_PSENT',SORT_PRIORITY|SORT_SENT);
define('SORT_PNAME',SORT_PRIORITY|SORT_NAME);
define('SORT_PACTIVITY',SORT_PRIORITY|SORT_ACTIVITY);

function getOrderBy($sort,$names,$priority='priority')
{
$order=@$names[$sort & SORT_ORDER];
return $order=='' ? '' : 'order by '.
                       (($sort & SORT_PRIORITY)!=0 ? "$priority," : '').$order;
}
?>
