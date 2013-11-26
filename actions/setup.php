<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/settings.php');

postString('okdir');
postString('faildir');

dbOpen();
session();

foreach($allSettings as $name => $info) {
    if (!isset($_REQUEST[$name]))
        continue;
    $func = 'post'.ucfirst($info['type']);
    if (function_exists($func))
        $func($name);
    if (isset($Args[$name]))
        $GLOBALS['user'.ucfirst($name)] = $Args[$name];
}
$settings = getSettingsString(SETL_USER, false);
if ($userId > 0)
    setSettingsByUserId($userId, $settings);
updateSettingsCookie(SETL_USER);

header("Location: $okdir");
dbClose();
?>
