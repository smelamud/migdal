<?php
# @(#) $Id$

function noCacheHeaders()
{
header('Expires: '.gmdate('D, d M Y H:i:s',time()-60));
# Note "\G\M\T" in format string. If format of this field is incorrect, PHP
# may output 01 Jan 1970 00:00:00 instead.
header('Last-Modified: '.gmdate('D, d M Y H:i:s \G\M\T'));
header('Cache-Control: no-cache, no-store, must-revalidate, pre-check=0, post-check=0, max-age=0, s-max-age=0'); 
header('Pragma: no-cache'); 
}
?>
