<?php
# @(#) $Id$

require_once('lib/head.php');

function createJSList() {
    global $jsList;

    if (!isset($jsList))
        $jsList = array();
}

function declareBasicJS($src) {
    global $jsList;

    createJSList();
    if (!in_array($src, $jsList))
        array_unshift($jsList, $src);
}

function declareJS($src) {
    global $jsList;

    createJSList();
    if (!in_array($src, $jsList))
        $jsList[] = $src;
}

function displayJSs() {
    global $jsList;

    beginHead();
    if (isset($jsList))
        foreach ($jsList as $src)
            echo "<script src='$src' type='text/javascript'></script>\n";
    endHead();
}
?>
