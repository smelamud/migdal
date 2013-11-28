<?php
# @(#) $Id$

function IPToInteger($addr) {
    $octets = explode('.', $addr);
    $ip = 0;
    foreach ($octets as $oct)
        $ip = $ip * 256 + $oct;
    return $ip;
}
?>
