<?php
# @(#) $Id$

require_once('conf/migdal.conf');

function echoCommand($cmd)
{
global $siteDomain;

readfile("http://$siteDomain/cgi-bin/getexec.pl?cmd=".urlencode($cmd));
}

function getCommand($cmd)
{
global $siteDomain;

return file_get_contents("http://$siteDomain/cgi-bin/getexec.pl?cmd="
                         .urlencode($cmd));
}
?>
