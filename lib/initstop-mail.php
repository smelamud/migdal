<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/mail.php');
require_once('lib/post.php');

function initializeMail()
{
global $args;

commandLineArgs();

parse_str($args[0]);
dbOpen();
session($args[1]);
ob_start();
}

function finalizeMail()
{
dbClose();
$Output=ob_get_contents();
ob_end_clean();
echo convertMailOutput($Output);
}
?>
