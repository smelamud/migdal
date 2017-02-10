<?php
# @(#) $Id$

require_once('lib/time.php');

function biff($time) {
    return (ourtime() - $time) / 3600 <= 24 /*hours*/;
}

?>
