<?php
# @(#) $Id$

require_once('conf/migdal.conf');

function echoCommand($cmd) {
    global $siteDomain, $cgiExec;

    if ($cgiExec)
        readfile("http://$siteDomain/cgi-bin/getexec.pl?cmd=".urlencode($cmd));
    else
        echo `$cmd`;
}

function getCommand($cmd) {
    global $siteDomain, $cgiExec;

    if ($cgiExec)
        return file_get_contents("http://$siteDomain/cgi-bin/getexec.pl?cmd="
                     .urlencode($cmd));
    else
        return `$cmd`;
}
?>
