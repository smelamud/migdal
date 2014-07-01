<?php
# @(#) $Id$

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
?>
