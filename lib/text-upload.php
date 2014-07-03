<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errors.php');
require_once('lib/text.php');
require_once('lib/text-any.php');

function uploadLargeBody(Entry $posting, $deleteExisting) {
    global $tmpDir, $maxLargeText;

    if($deleteExisting)
        $posting->setLargeBody('');
    if (!isset($_FILES['large_body_file']))
        return EG_OK;
    $file = $_FILES['large_body_file'];
    if ($file['tmp_name'] == '' || !is_uploaded_file($file['tmp_name'])
        || filesize($file['tmp_name']) != $file['size'])
        return EG_OK;
    if ($file['size'] > $maxLargeText)
        return EUL_LARGE;
    $format = 'text/';
    if (substr($file['type'], 0, strlen($format)) != $format)
        return EUL_UNKNOWN_FORMAT;

    $tmpname = tempnam($tmpDir, 'mig-');
    if (!move_uploaded_file($file['tmp_name'], $tmpname))
        return EG_OK;
    $fd = fopen($tmpname, 'r');
    $posting->setHasLargeBody(1);
    $posting->setLargeBodyFilename($file['name']);
    $text = fread($fd, $maxLargeText);
    $posting->setLargeBody(convertUploadedText($text));
    $posting->setLargeBodyXML(anyToXML(
        $posting->getLargeBody(), $posting->getLargeBodyFormat(), MTEXT_LONG));
    fclose($fd);
    unlink($tmpname);

    return EG_OK;
}
?>
