<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');

httpRequestString('okdir');
httpRequestString('faildir');
httpRequestString('svalue');

dbOpen();
if (getSessionCookie() == $svalue)
    header(
        'Location: '.
        remakeURI(
            $okdir,
            array('err')
        )
    );
else
    header(
        'Location: '.
        remakeURI(
            $faildir,
            array(),
            array('err' => EL_NO_COOKIES)
        ).'#error'
    );
dbClose();
?>
