<?php
# @(#) $Id$

function getURLDomain($url)
{
$parts=parse_url($url);
$host=strtolower($parts['host']);
if(substr($host,0,4)=='www.')
  $host=substr($host,4);
return $host;
}
?>
