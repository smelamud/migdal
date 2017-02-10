<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/postings.php');
require_once('lib/utils.php');
require_once('lib/sql.php');

function shadowPosting($postid) {
    global $userModerator;

    if (!$userModerator)
        return ESP_NO_SHADOW;
    if (!postingExists($postid))
        return ESP_POSTING_ABSENT;
    createPostingShadow($postid);
    return EG_OK;
}

httpRequestString('okdir');
httpRequestString('faildir');

httpRequestInteger('postid');

dbOpen();
session();
$err = shadowPosting($postid);
if ($err == EG_OK)
    header(
        'Location: '.
        remakeURI(
            $okdir,
            array('err'),
            array('reload' => random(0,999))
        )
    );
else
    header(
        'Location: '.
        remakeURI(
            $faildir,
            array(),
            array('err' => $err)
        ).'#error'
    );
dbClose();
?>
