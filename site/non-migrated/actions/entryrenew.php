<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/random.php');
require_once('lib/entries.php');
require_once('lib/forums.php');
require_once('lib/sql.php');

function renewEntryAction($id) {
    global $userModerator;

    if (!$userModerator)
        return EMR_NO_RENEW;
    if (!entryExists(ENT_NULL, $id))
        return EMR_NO_ENTRY;
    $entryType = getTypeByEntryId($id);
    switch ($entryType) {
        case ENT_FORUM:
            renewForum($id);
            break;
        default:
            renewEntry($id);
    }
    return EG_OK;
}

httpRequestString('okdir');
httpRequestString('faildir');

httpRequestInteger('id');

dbOpen();
session();
$err = renewEntryAction($id);
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
