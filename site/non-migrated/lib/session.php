<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/bug.php');
require_once('lib/tmptexts.php');
require_once('lib/uri.php');
require_once('lib/utils.php');
require_once('lib/sessions.php');
require_once('lib/users.php');
require_once('lib/sql.php');
require_once('lib/ctypes.php');
require_once('lib/settings.php');
require_once('lib/html-cache.php');

function session($aUserId = -1) {
    global $sessionid;

    if (isset($sessionid) && $aUserId < 0)
        return;

    userRights($aUserId);
    userSettings();
    contentVersions();
}
?>
