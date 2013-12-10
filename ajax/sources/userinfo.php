<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/json.php');
require_once('lib/users.php');
require_once('lib/fuzzy-calendar.php');

httpGetInteger('userid');

dbOpen();
session();

$user = getUserById($userid);

header('Content-Type: application/json');
jsonOutput(array('login' => $user->getLogin(),
                 'fullName' => $user->getFullName(),
                 'rank' => $user->getInfoOrRankHTML(),
                 'femine' => $user->isWoman(),
                 'lastOnline' => formatFuzzyTimeElapsed(
                                     $user->getLastOnline())));
dbClose();
?>
