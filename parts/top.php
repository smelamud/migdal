<?php
# @(#) $Id$

require_once('lib/session.php');

require_once('parts/menu.php');
require_once('parts/login.php');
require_once('parts/backtrace.php');

function displayTop($current,$flags='')
{
global $sessionid,$redir,$err;

displayMenu($current);?><br><?php
session($sessionid);
displayLogin($flags,$err);?><br><?php
displayBacktrace($redir);?><br><?php
}
?>
