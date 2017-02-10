<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/no-cache.php');
require_once('lib/charsets.php');
require_once('lib/style.php');
require_once('lib/redirs.php');
require_once('lib/post.php');
require_once('lib/logs.php');
require_once('lib/head.php');

function initializeHTML() {
    global $bodyClass;

    $bodyClass = '';
    initialize();
    ob_start();
}

function finalizeHead() {
    displayStylesheets();
    displayJSs();
    displayOGImages();
}

function finalizeHTML() {
    global $bodyClass;

    finalizeHead();
    finalize();

    $bodyBuffer = ob_get_clean();?>
    <!DOCTYPE html>
    <html>
    <head>
    <?php echo convertOutput(getHead()); ?>
    </head>
    <body<?php echo $bodyClass != '' ? " class='$bodyClass'" : '' ?>>
    <?php echo convertOutput($bodyBuffer); ?>
    </body>
    </html><?php
}

function initializeXML() {
    initialize();
    ob_start('convertOutput');
}

function finalizeXML() {
    finalize();
    ob_end_flush();
}

function initialize() {
    noCacheHeaders();
    header('Content-Style-Type: text/css'); 
    dbOpen();
    session();
    httpRequestInteger('err');
    set_error_handler('error_handler');
}

function finalize() {
    dbClose(); // dbClose() должен находиться здесь, чтобы профайлер не учитывал
               // время скачивания страницы клиентом
}

function error_handler($errno, $errstr, $errfile, $errline) {
    if (($errno & error_reporting()) == 0)
        return;
    logEvent('bug', "$errstr file($errfile) line($errline)");
    echo "<b>Fatal error</b>: $errstr in <b>$errfile</b> on line <b>$errline</b>";
    finalize();
    die();
}
?>
