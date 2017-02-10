<?php
# @(#) $Id$

const UNIX_SDN_OFFSET = 2440588;
const UNIX_DAY_SECONDS = 24 * 60 * 60;

function SDNToUNIX($sdn) {
    return ($sdn >= UNIX_SDN_OFFSET)
           ? (($sdn - UNIX_SDN_OFFSET) * UNIX_DAY_SECONDS)
           : false;
}

function UNIXToSDN($time = 0) {
    if ($time == 0)
        $time = time();
    return ((int)(($time + gmmktime() - mktime()) / UNIX_DAY_SECONDS))
           + UNIX_SDN_OFFSET;
}

?>
