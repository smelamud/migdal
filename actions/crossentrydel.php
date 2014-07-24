<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/cross-entries.php');
require_once('lib/sql.php');

function delCrossEntry($id) {
    global $userModerator;

    if (!$userModerator)
        return ECED_NO_DELETE;
    deleteCrossEntry($id);
    return EG_OK;
}

httpRequestString('okdir');
httpRequestString('faildir');

httpRequestInteger('id');

dbOpen();
session();
$err = delCrossEntry($id);
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
