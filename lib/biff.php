<?php
# @(#) $Id$

function biff($time)
{
global $userBiffTime;

return (time()-$time)/3600<=$userBiffTime;
}

?>
