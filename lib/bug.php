<?php
# @(#) $Id$

function bug($s) {
    trigger_error($s, E_USER_ERROR);
}

function sqlbug($s) {
    bug("$s: ".mysql_error());
}
?>
