<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/json.php');
require_once('lib/users.php');

httpGetInteger('userid');

function formatLastOnline($time) {
    return gmdate("d-m-Y H:i", $time);
}

dbOpen();
session();

$user = getUserById($userid);

header('Content-Type: application/json');
jsonOutput(array('login' => $user->getLogin(),
                 'fullName' => $user->getFullName(),
                 'rank' => $user->getRank(),
                 'femine' => $user->isWoman(),
                 'lastOnline' => formatLastOnline($user->getLastOnline())));
dbClose();
?>
