<?php
# @(#) $Id$

require_once('lib/charsets.php');

function convertMailOutput($s)
{
$s=preg_replace('/\s+/',' ',$s);
$s=str_replace('&sp;',' ',$s);
$s=preg_replace('/\s*<\s*[pP]\s*>(.*?)<\s*\/\s*[pP]\s*>\s*/e',
                "wordwrap(\"\\1\").'<br>'",$s);
$s=str_replace('&nbsp;',' ',$s);
$s=preg_replace('/\s*<\s*[bB][rR][\s\/]*>\s*/',"\n",$s);
$s=convertOutput($s,true,true);
return preg_replace('/^--$/m','---',$s);
}
?>
