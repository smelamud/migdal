<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');

function initializeMail() {
    ob_start();
}

function finalizeMail() {
    $Output = ob_get_contents();
    ob_end_clean();
    return convertOutput($Output);
}
?>
