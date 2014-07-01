<?php
# @(#) $Id$

require_once('lib/head.php');

function createOGImageList() {
    global $ogImageList;

    if (!isset($ogImageList))
        $ogImageList = array();
}

function declareOGImage($src) {
    global $ogImageList, $userDomain, $siteDomain;

    createOGImageList();
    if (!in_array($src, $ogImageList))
        $ogImageList[] = "http://$userDomain.$siteDomain$src";
}

function displayOGImages() {
    global $ogImageList;

    beginHead();
    if (isset($ogImageList) && count($ogImageList) > 0) {
        echo "<link rel='image_src' href='{$ogImageList[0]}'>\n";
        foreach ($ogImageList as $src)
            echo "<meta property='og:image' content='$src'>\n";
    }
    endHead();
}
?>
