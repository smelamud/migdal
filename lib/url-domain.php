<?php
# @(#) $Id$

function getURLDomain($url)
{
$parts=parse_url($url);
return strtolower($parts['host']);
}
?>
