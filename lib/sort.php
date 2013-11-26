<?php
# @(#) $Id$

const SORT_NONE = 0;
const SORT_SENT = 1;
const SORT_NAME = 2;
const SORT_ACTIVITY = 3;
const SORT_CTR = 4;
const SORT_INDEX0 = 5;
const SORT_INDEX1 = 6;
const SORT_RINDEX1 = 7;
const SORT_RATING = 8;
const SORT_JEWISH_NAME = 9;
const SORT_SURNAME = 10;
const SORT_LOGIN = 11;
const SORT_URL_DOMAIN = 12;
const SORT_RINDEX0 = 13;
const SORT_TOPIC_INDEX0_INDEX0 = 14;
const SORT_RSENT = 15;
const SORT_RINDEX2_RINDEX0 = 16;
const SORT_SUBJECT = 17;
const SORT_NAME_RUSSIAN = 18;
const SORT_LOCATION = 19;
const SORT_GHETTO_NAME = 20;
const SORT_SENDER_NAME = 21;
const SORT_ORDER = 255;
const SORT_PRIORITY = 256;

define('SORT_PSENT', SORT_PRIORITY|SORT_SENT);
define('SORT_PNAME', SORT_PRIORITY|SORT_NAME);
define('SORT_PACTIVITY', SORT_PRIORITY|SORT_ACTIVITY);

function getOrderBy($sort, $names, $priority='entries.priority') {
    $order = @$names[$sort & SORT_ORDER];
    $priorityOrder = ($sort & SORT_PRIORITY) != 0 ? "$priority," : '';
    return $order == '' ? '' : "order by $priorityOrder$order";
}
?>
