<?php
# @(#) $Id$

require_once('lib/session.php');

require_once('parts/menu.php');
require_once('parts/login.php');

function displayTop($current,$flags='')
{
global $sessionid,$err;

displayMenu($current);
session($sessionid);
displayLogin($flags,$err);
}
?>
