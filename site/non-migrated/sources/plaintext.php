<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/postings.php');

httpRequestIdent('id');
httpRequestString('charset');

dbOpen();
session();
$posting = getPostingById($id, GRP_ALL, -1, SELECT_GENERAL | SELECT_LARGE_BODY);
$data = $posting->getLargeBody();
$size = strlen($data);
header('Content-Type: text/plain');
header("Content-Disposition: attachment; filename=migdal-$id.txt; size=$size");
switch ($charset) {
    case 'win':
        $data = convert_cyr_string($data, 'k', 'w');
        break;
    case 'utf8':
        $data = iconv('KOI8-R', 'UTF-8', $data);
        break;
}
echo $data;
dbClose();
?>
