<?php
# @(#) $Id$

require_once('lib/time.php');

function biff($time)
{
global $userBiffTime;

return (ourtime()-$time)/3600<=$userBiffTime;
}

?>
