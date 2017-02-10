<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/utils.php');
require_once('lib/random.php');
require_once('lib/entries.php');

function modifyEntry($editid, $hide) {
    global $userModerator;

    if (!$userModerator)
        return EMH_NO_MODERATE;
    if (!entryExists(ENT_NULL, $editid))
        return EMH_NO_ENTRY;
    setDisabledByEntryId($editid, $hide);
    return EG_OK;
}

httpRequestString('okdir');
httpRequestString('faildir');

httpRequestInteger('editid');
httpRequestInteger('hide');

dbOpen();
session();
$err = modifyEntry($editid, $hide ? 1 : 0);
if ($err == EG_OK)
    header(
        'Location: '.
        remakeURI(
            $okdir,
            array(),
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