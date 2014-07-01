<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/errors.php');
require_once('lib/bug.php');
require_once('lib/utils.php');
require_once('lib/post.php');
require_once('lib/session.php');
require_once('lib/logging.php');

httpRequestString('okdir');
httpRequestString('faildir');
httpRequestString('login');
httpRequestString('password');
httpRequestInteger('my_computer');

dbOpen();
session();
loginHints($login, $my_computer);
$err = login($login, $password,
             $my_computer ? $longSessionTimeout : $shortSessionTimeout);
if ($err == EG_OK)
    header(
        'Location: /actions/checkcookies?'.
        makeQuery(array(
            'svalue'  => $sessionid,
            'okdir'   => $okdir,
            'faildir' => $faildir
        ))
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
